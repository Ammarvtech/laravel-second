<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Contracts\Options\OptionsContract;
use Contracts\Users\UsersContract;
use Contracts\Images\ImagesContract;

class OptionsController extends Controller
{
    public function __construct(OptionsContract $options, UsersContract $users, ImagesContract $images)
    {
        $this->options = $options;
        $this->users = $users;
        $this->images = $images;
    }
    
    public function edit(Request $request)
    {
        $this->users->hasPermission($request->user(), 'config-edit', true); // check permission first
        $row['image'] = "";
        $row['photo'] = "";
        $row = $this->options->getByLabel('site_config')->pluck('value','name')->toArray();
        if(isset($row['landing_image']) && $row['landing_image'] !="")
            $row['image'] = $this->images->get($row['landing_image']);
        if(isset($row['login_image']) && $row['login_image'] !="")
            $row['photo'] = $this->images->get($row['login_image']);
        $row['id']= "site_config";
        $data['title'] = trans('backend.config');
       // print_r($row);
        //die();
        return view('backend.config.edit', [
            "row" => (object) $row, $data
        ]);
    }

     /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->users->hasPermission($request->user(), 'config-edit', true); // check permission first

        $config = $this->options->update($request);

        if (!$config) {
            return redirect()->back()->with(messages('update', 'error'));
        }
        return redirect()->route('Backend::config.index')->with(messages('update'));
    }

    public function slider(Request $request)
    {
        if (empty($request->id) || empty($request->type))
            return response()->json(['type' => 'error']);

        $update = $this->options->updateSlider($request);

        if (!$update)
            return response()->json(['type' => 'error']);

        return response()->json(['type' => 'success']);
    }
}
