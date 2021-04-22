<?php
namespace Contracts\Images;

use ImageLib;
use App\Image;
use Storage;

interface ImagesContract
{
    public function __construct(Image $image);
    public function get($id);
    public function getAll();
    public function getPaginated();
    public function set($image, $type = null);
    public function update($data, $file);
    public function upload($image, $path, $title, $ext);
    public function delete($files);
}
