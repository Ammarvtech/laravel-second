<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Contracts\Movies\MoviesContract;
use Contracts\Genres\GenresContract;
use Contracts\Casts\CastsContract;
use Contracts\Users\UsersContract;
use Contracts\Videos\VideosContract;
use Illuminate\Support\Facades\DB;

class MoviesController extends Controller
{
    public function __construct(VideosContract $videos, MoviesContract $movies, GenresContract $genres, CastsContract $casts, UsersContract $users)
    {
        $this->movies = $movies;
        $this->genres = $genres;
        $this->casts  = $casts;
        $this->users  = $users;
        $this->videos = $videos;
    }

    public function index(Request $request)
    {
        $this->users->hasPermission($request->user(), 'movies-view', true); // check permission first

        $data['title']     = trans('backend.movies');
        $data['movies']    = $this->movies->getPaginated();
        $data['genres']    = $this->genres->getAll();
        $data['sliderIds'] = slider('movie');

        return view('backend.movies.index', $data);
    }

    public function filter(Request $request)
    {
        $this->users->hasPermission($request->user(), 'movies-view', true); // check permission first

        $data['title']     = trans('backend.movies');
        $data['movies']    = $this->movies->backendFilter($request)->data;
        $data['count']     = $this->movies->backendFilter($request)->count;
        $data['genres']    = $this->genres->getAll();
        $data['sliderIds'] = slider('movie');

        return view('backend.movies.index', $data);
    }

    public function create(Request $request)
    {
        $this->users->hasPermission($request->user(), 'movies-add', true); // check permission first

        $data['title']  = trans('backend.new_episode');
        $data['genres'] = $this->genres->getAll();
        $data['casts']  = $this->casts->getAll();
        $data['videos'] = $this->videos->getAll();
        
        return view('backend.movies.create', $data);
    }

    public function store(Request $request)
    {
        $this->users->hasPermission($request->user(), 'movies-add', true); // check permission first

        $this->validate($request, [
            'title'    => 'required|min:1|max:255',
            'ar_title' => 'required|min:1|max:255',
            'genres'   => 'required',
            'casts'    => 'required',
            'length'   => 'required',
            'imdb_url' => 'required|url',
        ]);

        $store = $this->movies->set($request);

        if( ! $store){
            return redirect()->back()->with(messages('store', 'error'));
        }

        return redirect()->route('Backend::movies.index')->with(messages('store'));
    }

    public function edit(Request $request, $id)
    {
        $this->users->hasPermission($request->user(), 'movies-edit', true); // check permission first

        $data['movie']  = $this->movies->get($id);
        $data['genres'] = $this->genres->getAll();
        $data['casts']  = $this->casts->getAll();
        $data['videos'] = $this->videos->getAll();
        return view('backend.movies.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $this->users->hasPermission($request->user(), 'movies-view', true); // check permission first

        $this->validate($request, [
            'title'    => 'required|min:1|max:255',
            'ar_title' => 'required|min:1|max:255',
            'genres'   => 'required',
            'casts'    => 'required',
            'length'   => 'required',
            'imdb_url' => 'required|url',
        ]);

        $update = $this->movies->update($request, $id);

        if( ! $update){
            return redirect()->back()->with(messages('update', 'error'));
        }

        return redirect()->route('Backend::movies.index')->with(messages('update'));
    }

    public function destroy(Request $request, $id)
    {
        $this->users->hasPermission($request->user(), 'movies-delete', true); // check permission first

        $delete = $this->movies->delete($id);

        if( ! $delete){
            return redirect()->back()->with(messages('delete', 'error'));
        }

        return redirect()->route('Backend::movies.index')->with(messages('delete'));
    }

    public function status(Request $request){
        $update = DB::table('movies')->where('id',$request->id)->update([$request->type => $request->status]);
        
        if(!$update)
            return response()->json(['type' => 'error']);
        
        return response()->json(['type' => 'success']);
    }
}
