<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Contracts\Pages\PageContract;
use Contracts\Users\UsersContract;

class PageController extends Controller
{
    public function __construct(PageContract $pages, UsersContract $users)
    {
        $this->pages = $pages;
        $this->users = $users;
    }

    public function index(Request $request)
    {
        $this->users->hasPermission($request->user(), 'pages-view', true); // check permission first

        $data['title']  = trans('backend.pages');
        $data['pages'] = $this->pages->getPaginated();

        return view('backend.pages.index', $data);
    }

    public function filter(Request $request)
    {
        $this->users->hasPermission($request->user(), 'pages-view', true); // check permission first

        $data['title']  = trans('backend.pages');
        $data['pages'] = $this->pages->backendFilter($request)->data;
        $data['count'] = $this->pages->backendFilter($request)->count;

        return view('backend.pages.index', $data);
    }

    public function create(Request $request)
    {
        $this->users->hasPermission($request->user(), 'pages-add', true); // check permission first

        $data['title'] = trans('backend.new_page');

        return view('backend.pages.create', $data);
    }

    public function store(Request $request)
    {
        $this->users->hasPermission($request->user(), 'pages-add', true); // check permission first

        $this->validate($request, [
            'title'    => 'required|min:3|max:255',
            'ar_title' => 'required|min:3|max:255',
            'content'    => 'required|min:3',
            'ar_content' => 'required|min:3',
        ]);

        $store = $this->pages->set($request);

        if(!$store)
            return redirect()->back()->with(messages('store', 'error'));

        return redirect()->route('Backend::pages.index')->with(messages('store'));
    }

    public function edit(Request $request, $id)
    {
        $this->users->hasPermission($request->user(), 'pages-edit', true); // check permission first

        $data['title'] = trans('backend.update');
        $data['page']  = $this->pages->get($id);

        return view('backend.pages.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $this->users->hasPermission($request->user(), 'pages-edit', true); // check permission first

        $this->validate($request, [
            'title'    => 'required|min:3|max:255',
            'ar_title' => 'required|min:3|max:255',
            'content'    => 'required|min:3',
            'ar_content' => 'required|min:3',
        ]);

        $update = $this->pages->update($request, $id);

        if(!$update)
            return redirect()->back()->with(messages('update', 'error'));

        return redirect()->route('Backend::pages.index')->with(messages('update'));
    }

    public function destroy(Request $request, $id)
    {
        $this->users->hasPermission($request->user(), 'pages-delete', true); // check permission first

        $delete = $this->pages->delete($id);

        if(!$delete)
            return redirect()->back()->with(messages('delete', 'error'));

        return redirect()->route('Backend::pages.index')->with(messages('delete'));
    }
}
