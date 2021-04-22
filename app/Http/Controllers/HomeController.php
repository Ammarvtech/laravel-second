<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Contracts\Movies\MoviesContract;
use Contracts\Shows\ShowsContract;
use Illuminate\Support\Facades\Mail;
use App\Mail\sendMail;
use App\CountryException;
use App\Device;
use App\Support;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ShowsContract $shows, MoviesContract $movies)
    {
        $this->movies = $movies;
        $this->shows  = $shows;
    }

    /**
     * Show the application dashboard.
     *
 * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['title'] = trans('frontend.home');

        return view('home', $data);
    }

    public function search(Request $request)
    {
        $this->validate($request, [
            'keyword'    => 'required|min:3|max:255'
        ]);

        $data['title'] = trans('frontend.search');
        $data['movies'] = $this->movies->search($request->keyword);
        $data['shows'] = $this->shows->search($request->keyword);
        return view('frontend.results', $data);

    }

     public function allow_me(){
        $ip= \Request::ip();
        $exp = new CountryException;
        $exp->ip = $ip;
        $exp->save();
        return redirect()->route('login');
    }

    public function contactUs(Request $request){
        $support = new Support;
        $this->validate($request, [
            'name'   => 'required|min:3|max:255',
            'subject'   => 'required|min:3|max:255',
            'message'   => 'required',
            'email'  => 'required|email|max:255'
        ]);
        $support->name = $request->name;
        $support->subject = $request->subject;
        $support->email = $request->email;
        $support->message = $request->message;
        $support->is_viewed = 1;
        $saved = $support->save();

       $sendToMedia = array( 
            "name" => "Team Tasali Media",
            "message" => $request->message.'<br><br>Name: '.$request->name.'<br>Email : '.$request->email.'<br><br> Best Regards: '.$request->name.'<br>',
            "template" => "frontend.dynamic_email_template",
            "subject" => $request->subject
        );
        Mail::to($request->email)->send(new SendMail($sendToMedia));
        /*$sendToUser = array( 
            "name" => $request->name,
            "message" => 'Your Request has been submitted, Tasali Media Support Team will look after your query and contact you very soon.<br><br> Best Regards: Tasali Media Support Team<br>',
            "template" => "frontend.dynamic_email_template",
            "subject" => "Tasali Media Support"
        ); 
        Mail::to($request->email)->send(new SendMail($sendToUser)); */

        return response()->json([
            'success' => 'Your request has been submitted successfully!',
            'type'     => 'success'
        ]);
    }

    /*public function makeThumbnails(){
    $images =  $this->images->getAll();
    foreach ($images as $key => $value) {
        if($value->id !=1){
            $s3path = $value->title.".".$value->ext;
            $filename = explode('/', $value->title);
            $filename = $filename[3] . '_thumb' . "." . $value->ext;
            $image_path =  base_path() . '/storage/app/public/' . $filename;
            $thumb = $value->title . "_thumb" . "." . $value->ext;
            
            $s3_file = Storage::disk('s3')->get($s3path);
            $s3 = Storage::disk('public');
            $s3->put("./".$filename, $s3_file);

            $value->update(['done' => 1]);
            $thumb_file = ImageLib::make($image_path)->resize(199, 274);// if want to save on local directory then user "->save(public_path()."/".uploads/)" after resize(199, 274).

            Storage::disk('s3')->put($thumb, $thumb_file->stream()->detach(),  'public');

            echo "<<<<<<<<<<<<<<<<<<<<<<<<<<<< ". $value->id ." >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>><br>";
            echo $thumb."<br>";
            echo "<<<<<<<<<<<<<<<<<<<<<<<<<<< END >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>><br><br><br>";
        }
    }
    die();
    }*/

     public function removeDevice($id)
    {
        Device::where('id', $id)->delete();
        return redirect()->route('login');
    }
}
