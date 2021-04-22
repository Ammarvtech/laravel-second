<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Contracts\Genres\GenresContract;
use Contracts\Users\UsersContract;
use Illuminate\Support\Facades\DB;


class GenresController extends Controller
{
    public function __construct(GenresContract $genres, UsersContract $users)
    {
        $this->genres = $genres;
        $this->users = $users;
    }

    public function index(Request $request)
    {
        $this->users->hasPermission($request->user(), 'genres-view', true); // check permission first

        $data['title']  = trans('backend.genres');
        $data['genres'] = $this->genres->getPaginated();
        return view('backend.genres.index', $data);
    }

    public function filter(Request $request)
    {
        $this->users->hasPermission($request->user(), 'genres-view', true); // check permission first

        $data['title']  = trans('backend.genres');
        $data['genres'] = $this->genres->backendFilter($request)->data;
        $data['count']  = $this->genres->backendFilter($request)->count;

        return view('backend.genres.index', $data);
    }

    public function create(Request $request)
    {
        $this->users->hasPermission($request->user(), 'genres-add', true); // check permission first

        $data['title'] = trans('backend.new_genre');

        return view('backend.genres.create', $data);
    }

    public function abc(Request $request)
    {
        die('adsfasdf');
        //$this->users->hasPermission($request->user(), 'genres-add', true); // check permission first

        $data['title'] = trans('backend.new_genre');

        return view('backend.genres.create', $data);
    }

    public function store(Request $request)
    {
        $this->users->hasPermission($request->user(), 'genres-add', true); // check permission first

        $this->validate($request, [
            'title'    => 'required|min:3|max:255'
        ]);

        $store = $this->genres->set($request);

        if(!$store)
            return redirect()->back()->with(messages('store', 'error'));

        return redirect()->route('Backend::genres.index')->with(messages('store'));
    }

    public function edit(Request $request, $id)
    {
        $this->users->hasPermission($request->user(), 'genres-edit', true); // check permission first

        $data['genre'] = $this->genres->get($id);

        return view('backend.genres.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $this->users->hasPermission($request->user(), 'genres-edit', true); // check permission first

        $this->validate($request, [
            'title'    => 'required|min:3|max:255',
        ]);

        $update = $this->genres->update($request, $id);

        if(!$update)
            return redirect()->back()->with(messages('update', 'error'));

        return redirect()->route('Backend::genres.index')->with(messages('update'));
    }

    public function destroy(Request $request, $id)
    {
        $this->users->hasPermission($request->user(), 'genres-delete', true); // check permission first

        $delete = $this->genres->delete($id);

        if(!$delete)
            return redirect()->back()->with(messages('delete', 'error'));

        return redirect()->route('Backend::genres.index')->with(messages('delete'));
    }

    public function status(Request $request){
        $update = DB::table('genres')->where('id',$request->id)->update([$request->type => $request->status]);
        
        if(!$update)
            return response()->json(['type' => 'error']);
        
        return response()->json(['type' => 'success']);
    }
}
