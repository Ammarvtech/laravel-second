<?php

namespace Repos\Videos;

use Contracts\Videos\VideosContract;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;
use Pion\Laravel\ChunkUpload\Handler\AbstractHandler;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use App\Jobs\UploadFile;
use App\Jobs\VideosTranscode;
use App\Video;
use Storage;
use Aws\S3\MultipartUploader;
use Aws\Exception\MultipartUploadException;
use AWSContract;

class VideosRepo implements VideosContract
{
    private $pagination = 10;

    public function __construct(Video $video)
    {
        $this->video = $video;
    }

    public function get($id)
    {
        return $this->video->findOrFail($id);
    }
    public function getAll()
    {
        return $this->video->all();
    }
    public function getLatest()
    {
        return $this->video->orderBy('id','desc')->pluck('id')->first();
    }

    public function getByType($type, $id)
    {
        return $this->video->where('parent_type', $type)->where('parent', $id)->first();
    }

    public function getPaginated()
    {
        return $this->video->latest()->paginate($this->pagination);
    }

    public function uploadedToday($from_date, $to_date){
        return $this->video->whereDate('created_at','<=',$from_date)->whereDate('created_at','>=',$to_date)->get();
    }
    
    public function set($file)
	{
        // try {                  
            $title = md5( microtime() ) . rand( 00000, 99999 );
            $ext   = $file->getClientOriginalExtension();
            $path  = date('Y') . '/' . date('m') . '/';

            $originalTitle = explode('.', $file->getClientOriginalName());
            array_pop($originalTitle);
            
            $this->video->title          = $path . $title;
            $this->video->ext            = $ext;
            $this->video->size           = $file->getClientSize();
            $this->video->mime           = $file->getMimeType();
            $this->video->original_title = implode('.', $originalTitle);
            $this->video->parent         = 0;
            
            $this->video->save();            
            dispatch(
                (new UploadFile($file->getPathname(), $file->getRealPath(), $path, $title, $ext)
            )->onQueue('uploadVideos'));
            
            dispatch((new VideosTranscode($path, $title, $ext))->onQueue('transcode'));
            
            return response()->json([
                'type' => 'success'
            ]);
        // } catch(\Exception $e) {
        //     return false;
        // }
    }


    public function update($request, $id)
    {
        $video = $this->get($id);

        if (!$video)
            abort(404);        
        $old_video = Video::where('parent', $request->movie)->where('parent_type', ($request->type == 'trailer' ? 'trailer_' : null) . 'movie')->first();
        if($old_video != null){
            $old_video->parent = 0;
            $old_video->parent_type = NULL;
            $old_video->update();
        }
        $video->original_title = $request->original_title;
        if (!empty($request->movie) && $request->movie != 'none')
        {
            $video->parent = $request->movie;
            $video->parent_type = ($request->type == 'trailer' ? 'trailer_' : null) . 'movie';
        }
        
        if (!empty($request->episode) && $request->episode !== 'none')
        {
            $video->parent = $request->episode;
            $video->parent_type = ($request->type == 'trailer' ? 'trailer_' : null) . 'show';
        }
        
        $save = $video->save();

        if ($save)
        {
            return redirect()->route('Backend::videos.index')->with([
                'msg-type' => 'success',
                'msg'      => trans('backend.success_update')
            ]);
        }

        return redirect()->route('Backend::videos.index')->with([
            'msg-type' => 'danger',
            'msg'      => trans('backend.error_update')
        ]);
    }

    public function delete($id)
    {
        $delete = $this->get($id)->delete();
        
        if ($delete)
        {
            return redirect()->route('Backend::videos.index')->with([
                'msg-type' => 'success',
                'msg'      => trans('backend.success_delete')
            ]);
        }
        
        return redirect()->route('Backend::videos.index')->with([
            'msg-type' => 'danger',
            'msg'      => trans('backend.error_delete')
        ]);
    }
    
    public function sendToS3($file, $uploadId = null, $part)
    {
        $s3 = AWSContract::createClient('s3');

        if (is_null($uploadId))
        {
            $result = $s3->createMultipartUpload(array(
                'Bucket'       => env('AWS_VIDEOS_BUCKET'),
                'Key'          => env('AWS_KEY'),
                'StorageClass' => 'REDUCED_REDUNDANCY',
                'ACL'          => 'public-read',
            ));

            $uploadId = $result['UploadId'];
        } 

        $result = $s3->uploadPart([
            'Bucket'     => env('AWS_VIDEOS_BUCKET'),
            'Key'        => env('AWS_KEY'),
            'UploadId'   => $uploadId,
            'PartNumber' => $part,
            'Body'       => $file->getRealPath(),
        ]);

        return (object) [
            'id'   => $uploadId,
            'etag' => trim($result['ETag'], '"'),
            'part' => $part
        ];
    }

    public function setMulti($files, $id)
    {
		$result = [];

		foreach ($files as $file) {
			$title = md5( microtime() ) . rand( 0000, 9999 );
			$ext   = $file->extension();
            $path  = date( 'Y' ) . '/' . date( 'm' ) . '/';
            
            $store = Storage::disk('s3')->putFileAs('videos/'.$path, $file, $title.'.'.$ext, 'public');
            
            if (!$store)
                continue;

            $data = [
                'title'          => $path . $title,
                'ext'            => $ext,
                'size'           => $file->getClientSize(),
                'mime'           => $file->getMimeType(),
                'original_title' => $file->getClientOriginalName(),
                'parent'         => $id
            ];

            $insertedFile = $this->video->create($data);
            $result[]     = $insertedFile->id;
		}

		return $result;
    }
    
    public function uploadToS3($pathToFile, $realPath, $path, $title, $ext)
    {
        //try {
            $s3 = AWSContract::createClient('s3');
            $s3->putObject(array(
                'Bucket'     => env('AWS_VIDEOS_BUCKET'),
                'Key'        => $title.'.'.$ext,
                'SourceFile' => $realPath,
            ));

            //unlink($pathToFile);
            return true;
        //} catch(\Exception $e) {
             //return false;
        //}
    }
}