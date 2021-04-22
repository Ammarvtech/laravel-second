<?php
namespace Repos\Casts;

use Contracts\Casts\CastsContract;
use Contracts\Images\ImagesContract;
use App\Cast;
use Carbon\Carbon;

class CastsRepo implements CastsContract
{
    private $pagination = 20;

    public function __construct(Cast $cast, ImagesContract $images)
    {
        $this->cast   = $cast;
        $this->images = $images;
    }

    public function get($id)
    {
        return $this->cast->findOrFail($id);
    }

    public function getBySlug($slug)
    {
        return $this->cast->where('slug', $slug)->firstOrFail();
    }

    public function getAll()
    {
        return $this->cast->all();
    }

    public function getPaginated()
    {
        return $this->cast->latest()->paginate($this->pagination);
    }

    public function backendFilter($request)
    {
        $q = $this->cast;

        if (isset($request->keyword) && !empty($request->keyword))
            $q = $q->whereTranslationLike('name', '%' . $request->keyword . '%');

        return (object) [
            'data'  => $q->paginate($this->pagination),
            'count' => $q->count()
        ];
    }

    public function set($data)
    {
        $slug = make_slug($data->name);

        if ($cast = $this->cast->where('slug', 'like', '%'.$slug.'%')->orderBy('slug', 'desc')->first())
            $slug = $cast->slug.'-2';

        $inputs = [
            'slug' => $slug,
            // multi lang inputs
            'en' => [
                'name' => $data->name,
            ],  
            'ar' => [
                'name' => $data->ar_name,
            ],
            'meta_tags' => $data->meta_tags,
            'meta_description' => $data->meta_description
        ];

        if ($data->hasFile('image') && $data->file('image')->isValid())
            $inputs['image_id'] = $this->images->set($data->image);

        return $this->cast->create($inputs);
    }

    public function update($data, $id)
    {
        $cast = $this->get($id);
        $inputs = [
            // multi lang inputs
            'en' => [
                'name' => $data->name,
            ],
            'ar' => [
                'name' => $data->ar_name,
            ],
            'meta_tags' => $data->meta_tags,
            'meta_description' => $data->meta_description
        ];

        if ($data->hasFile('image') && $data->file('image')->isValid())
            $inputs['image_id'] = $this->images->set($data->image);

        return $cast->update($inputs);
    }

    public function delete($id)
    {
        return $this->get($id)->delete();
    }

    public function countAll(){
        return $this->cast->count();
    }

    public function getMovies($slug){
        return $this->get($slug)->movies()->get();
    }

    public function dateFilter($from_date, $to_date){
        $q = $this->cast;
        $q =  $q->whereDate('created_at', '<=', $from_date);
        $q =  $q->whereDate('created_at', '>=', $to_date);
        return (object) [
            'count' => $q->count()
        ];
    }

}
