<?php
/**
 * Created by PhpStorm.
 * User: Backend Dev
 * Date: 4/22/2018
 * Time: 10:24 AM
 */

namespace Repos\Pages;


use Contracts\Pages\PageContract;
use App\Page;
use Contracts\Images\ImagesContract;

class PageRepo implements PageContract
{

    private $pagination = 20;

    public function __construct(Page $page, ImagesContract $images)
    {
        $this->page = $page;
        $this->images = $images;
    }

    public function get($id)
    {
        return $this->page->findOrFail($id);
    }

    public function getBySlug($slug)
    {
        return $this->page->where('slug', $slug)->firstOrFail();
    }

    public function getAll()
    {
        return $this->page->all();
    }

    public function getPaginated()
    {
        return $this->page->latest()->paginate($this->pagination);
    }

    public function backendFilter($request)
    {
        $q = $this->page;

        if (isset($request->keyword) && !empty($request->keyword))
            $q = $q->whereTranslationLike('title', '%' . $request->keyword . '%');

        return (object) [
            'data'  => $q->paginate($this->pagination),
            'count' => $q->count()
        ];
    }

    public function set($data)
    {
        $slug = make_slug($data->title);

        if ($page = $this->page->where('slug', 'like', '%'.$slug.'%')->orderBy('slug', 'desc')->first())
            $slug = $page->slug.'-2';

        $inputs = [
            'slug' => $slug,
            // multi lang inputs
            'en' => [
                'title' => $data->title,
                'content' => $data->content,
            ],  
            'ar' => [
                'title' => $data->ar_title,
                'content' => $data->ar_content,
            ],
            "is_active" =>  (boolean) $data->is_active
        ];

        if ($data->hasFile('image') && $data->file('image')->isValid())
            $inputs['image_id'] = $this->images->set($data->image);

        return $this->page->create($inputs);
    }

    public function update($data, $id)
    {
        $page = $this->get($id);
        $inputs = [
            // multi lang inputs
            'en' => [
                'title' => $data->title,
                'content' => $data->content,
            ],  
            'ar' => [
                'title' => $data->ar_title,
                'content' => $data->ar_content,
            ],
            "is_active" =>  (boolean) $data->is_active
        ];

        
        if ($data->hasFile('image') && $data->file('image')->isValid())
            $inputs['image_id'] = $this->images->set($data->image);

        return $page->update($inputs);
    }

    public function getLinks($is_active = null)
    {
      $query = $this->page;
      if(!is_null($is_active)){
        $query = $query->where('is_active',$is_active)->get()->pluck('title', 'slug');
      }
      return $query;
    }

    public function delete($id)
    {
        return $this->get($id)->delete();
    }
}