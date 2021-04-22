<?php
namespace Repos\Images;

use Contracts\Images\ImagesContract;
use App\Image;
use ImageLib;
use Storage;

class ImagesRepo implements ImagesContract
{
    private $pagination = 20;

    public function __construct(Image $image)
    {
        $this->image = $image;
    }

    public function get($id)
    {
        return $this->image->findOrFail($id);
    }

    public function getAll()
    {
        return $this->image->all();
    }

    public function getPaginated()
    {
        return $this->image->paginate($this->pagination);
    }

    public function set($image, $type = null)
    {
        $title = md5( microtime() ) . rand( 0000, 9999 );
        $ext   = $image->extension();
        $path  = 'images/' . date( 'Y' ) . '/' . date( 'm' ) . '/';

        $upload = $this->upload($image, $path, $title, $ext);

        if (!$upload)
            return false;
		
        $inputs['title']          = $path . $title;
        $inputs['ext']            = $ext;
        $inputs['size']           = $image->getClientSize();
        $inputs['mime']           = $image->getMimeType();
        $inputs['original_title'] = $image->getClientOriginalName();

        $image = $this->image->create($inputs);
        
		return $image->id;
    }

    public function update($data, $file)
    {
        return $file->update($data);
    }

    public function upload($image, $path, $title, $ext)
    {
        // try {
            $df  = $path . $title . '.' . $ext;
            $lg  = $path . $title . '_lg.' . $ext;
            $md  = $path . $title . '_md.' . $ext;
            $sm  = $path . $title . '_sm.' . $ext;
            $sm2 = $path . $title . '_sm2.' . $ext;
            $xs  = $path . $title . '_xs.' . $ext;
            $thumb = $path . $title . '_thumb.' . $ext;

            $df_file  = ImageLib::make( $image );
            $lg_file  = ImageLib::make( $image )->resize( 566, 429 );
            $md_file  = ImageLib::make( $image )->resize( 420, 300 );
            $sm_file  = ImageLib::make( $image )->resize( 278, 210 );
            $sm2_file = ImageLib::make( $image )->resize( 200, 150 );
            $xs_file  = ImageLib::make( $image )->resize( 100, 80 );
            $thumb_file  = ImageLib::make( $image )->resize( 199, 274 );

            Storage::disk('s3')->put($df, $df_file->stream()->detach(),  'public');
            Storage::disk('s3')->put($lg, $lg_file->stream()->detach(),  'public');
            Storage::disk('s3')->put($md, $md_file->stream()->detach(),  'public');
            Storage::disk('s3')->put($sm, $sm_file->stream()->detach(),  'public');
            Storage::disk('s3')->put($sm2, $sm2_file->stream()->detach(), 'public');
            Storage::disk('s3')->put($xs, $xs_file->stream()->detach(),  'public');
            Storage::disk('s3')->put($thumb, $thumb_file->stream()->detach(),  'public');
            
            return true;
        // } catch(\Exception $e) {
        //     return false;
        // }
    }

    public function delete($files)
    {
        return $files->delete();
    }
}