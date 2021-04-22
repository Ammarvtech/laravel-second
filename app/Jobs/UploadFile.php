<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Contracts\Videos\VideosContract;
use App\Jobs\VideosTranscode;
use Storage;

class UploadFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public  $tries = 2;
    private $pathToFile;
    private $realPath;
    private $path;
    private $title;
    private $ext;

    public function __construct($pathToFile, $realPath, $path, $title, $ext)
    {
      $this->pathToFile = $pathToFile;
      $this->realPath   = $realPath;
      $this->path       = $path;
      $this->title      = $title;
      $this->ext        = $ext;
    }
    
    public function handle(VideosContract $videos)
    {
        $upload = $videos->uploadToS3($this->pathToFile, $this->realPath, $this->path, $this->title, $this->ext);

        if (!$upload)
           return false;

        //dispatch((new VideoTranscode($this->path, $this->title, $this->ext))->onQueue('transcode'));
    }
}
