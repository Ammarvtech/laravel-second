<?php
/**
 * Created by PhpStorm.
 * User: Backend Dev
 * Date: 4/22/2018
 * Time: 10:23 AM
 */

namespace Contracts\Pages;


use App\Page;
use Contracts\Images\ImagesContract;

interface PageContract
{
    public function __construct(Page $page, ImagesContract $images);

    public function get($id);

    public function getBySlug($slug);

    public function getAll();

    public function getPaginated();

    public function set($data);

    public function update($data, $id);

    public function getLinks($active=null);
    
    public function delete($id);
}