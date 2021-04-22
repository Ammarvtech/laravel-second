<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\sendMail;
use App\Http\Controllers\Controller;
use Contracts\CreditCards\CreditCardContract;
use Contracts\Options\OptionsContract;
use App\User;
use App\Order;
use App\Device;
use App\Subscription;
use App\CreditCards;
use Contracts\Users\UsersContract;
use Contracts\Shows\ShowsContract;
use Contracts\Movies\MoviesContract;
use Auth;
use Cartalyst\Stripe\Stripe;
use App\Country;

class UserController extends Controller
{
    public function __construct(
        CreditCardContract $cards,
        User $user,
        OptionsContract $config,
        Order $order,
        Subscription $subscription,
        UsersContract $userRepo,
        ShowsContract $shows,
        MoviesContract $movies
    ) {
        $this->user = $user;
        $this->cards = $cards;
        $this->config = $config;
        $this->order = $order;
        $this->subscription = $subscription;
        $this->userRepo = $userRepo;
        $this->shows = $shows;
        $this->movies = $movies;

    }

    public function profile()
    {
        $amount = $this->config->getByName('amount');
        $data['user'] = $this->userRepo->get(\Auth::id());
        $data['cards'] = $this->cards->getUser(\Auth::id());
        $data['subscription'] = $this->subscription->where('user_id', \Auth::id())->first();
        $data['devices'] = Device::where('user_id', \Auth::id())->get();
        $data['amount']  = Country::where('name', '=', Auth::user()->country)->pluck('amount')->first();
        $data['currency'] = Country::where('name', '=', Auth::user()->country)->pluck('curriency_code')->first();

        /*$stripe = \Stripe::setApiKey(env('STRIPE_SECRET'));
        $customer_id = $this->subscription->where('user_id', \Auth::id())->where('is_cancelled', '0')->pluck('customer_id')->first();
        //$customer = $stripe->cards()->all($customer_id); //card_1HXN3CBs2CQ90tyBmCzqUYMz =>default
        $customer = $stripe->customers()->update($customer_id, [
            'default_source' => 'card_1HXN3CBs2CQ90tyBmCzqUYMz', //'card_1HXPNxBs2CQ90tyBlgkfXIF2',
        ]);

        $customer = $stripe->customers()->find($customer_id);
        dd($customer);*/
        return view('frontend.profile.index', $data);
    }

    public function editProfile($field)
    {
        $data['title'] = trans('frontend.choose_payment');
        $data['desc']  = trans('frontend.choose_payment');
        $data['user'] = $this->userRepo->get(\Auth::id());
        return view('frontend.profile.edit', $data)->with(['field' => $field]);
    }

    public function updateProfile(Request $request)
    {
        if ($request->field == "name") {
            $this->validate($request, [
                'name' => 'required|max:20|alpha_num',
            ]);
        } elseif ($request->field == "email") {
            $this->validate($request, [
                'current_email' => 'required|email',
                'email' => 'required|email',
                'current_password' => 'required',
            ]);
        } elseif ($request->field == "image_id") {
            $this->validate($request, [
                'image_id' => 'required',
            ]);
        } else {
            $this->validate($request, [
                'current_password' => 'required',
                'password' => 'required|confirmed',
            ]);
        }

        $response = $this->userRepo->editProfile($request, \Auth::user());
        if(!is_object($response)){
            return redirect()->back()->withErrors($response);
        }
        return redirect()->route('profile');
        
        
    }

    /////////////////// Manage Profiles       //////////////////////////
    public function allProfiles()
    {
        $data['user'] = $this->userRepo->get(\Auth::id());
        $data['child'] = $this->userRepo->getChild(\Auth::id());
        return view('frontend.profile.profiles', $data);
    }

    public function addProfile(){
        $data['title'] = trans('frontend.add_profile');
        return view('frontend.profile.add_profile', $data);        
    }

    public function storeProfile(Request $request){        
        
        $this->validate($request, [
            'name' => 'required|max:20',
            'email'    => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed|required_with:password_confirmation'
        ]);

        $response = $this->userRepo->storeProfile($request, \Auth::id());
        if(!is_object($response)){
            return redirect()->back()->withErrors($response);
        }
        return redirect()->route('all.profiles');
    }

    public function profileSetting($id){
        $data['user'] = $this->userRepo->get($id);
        $data['user_id'] = $id; 
        return view('frontend.profile.profile_setting', $data);
    }

    public function updateUserProfile(Request $request){
        if ($request->field == "name") {
            $this->validate($request, [
                'name' => 'required|max:20',
            ]);
        } elseif ($request->field == "email") {
            $this->validate($request, [
                'current_email' => 'required|email',
                'email' => 'required|email',
                'current_password' => 'required',
            ]);
        } elseif ($request->field == "image_id") {
            $this->validate($request, [
                'image_id' => 'required',
            ]);
        } else {
            $this->validate($request, [
                'current_password' => 'required',
                'password' => 'required|confirmed',
            ]);
        }
        $user =  $this->userRepo->get($request->user_id);
        $response = $this->userRepo->editProfile($request, $user);        
        if(!is_object($response)){
            return redirect()->back()->withErrors($response);
        }
        return redirect()->route('profile.setting',$request->user_id);
    }


    public function editUserProfile($field, $id)
    {
        $data['title'] = trans('frontend.choose_payment');
        $data['desc']  = trans('frontend.choose_payment');
        $data['user'] = $this->userRepo->get($id);
        return view('frontend.profile.edit_user', $data)->with(['field' => $field]);
    }


    ///////////////////// Payments  ////////////////////////////////

    public function addPayment()
    {

        $amount = $this->config->getByName('amount');
        $data['title'] = trans('frontend.choose_payment');
        $data['desc']  = trans('frontend.choose_payment');
        $data['amount']  = ($amount) ? $amount->value : null;

        return view('frontend.profile.add_payment', $data);
    }

    public function storePayment(Request $request)
    {
        $this->validate($request, [
            'card_number' => 'required',
            'expiration_month' => 'required',
            'expiration_year' => 'required',
            'name_on_card' => 'required',
            'card_code' => 'required|min:3|max:4',
        ]);

        $customer_id = $this->subscription->where('user_id', \Auth::id())->where('is_cancelled', '0')->pluck('customer_id')->first();

        $stripe = \Stripe::setApiKey(env('STRIPE_SECRET'));
        
        try {
            $token = $stripe->tokens()->create([
                'card' => [
                    'number'    => $request->card_number,
                    'exp_month' => $request->expiration_month,
                    'cvc'       => $request->card_code,
                    'exp_year'  => $request->expiration_year,
                ],
            ]);
        }catch (Exception $e) {
            return redirect()->back()->with(['msg-type' => 'error', 'msg' => $e->getMessage()]);
        }catch(\Cartalyst\Stripe\Exception\CardErrorException $e) {
            return redirect()->back()->with(['msg-type' => 'error', 'msg' => $e->getMessage()]);
        } catch(\Cartalyst\Stripe\Exception\MissingParameterException $e) {
            return redirect()->back()->with(['msg-type' => 'error', 'msg' => $e->getMessage()]);
        }
        
        try {
            $card = $stripe->cards()->create($customer_id, $token['id']);
        }catch (Exception $e) {            
            return redirect()->back()->with(['msg-type' => 'error', 'msg' => $e->getJsonBody()]);
        }
        
        if ($request->is_primary == 1) {
            $this->cards->unPrimaryCards(\Auth::id());
        }

        $cards = CreditCards::create([
            'user_id'      => Auth::user()->id,
            'type'         => $card['brand'],
            'name_on_card' => $card['name'],
            'expiration_month'  => $card['exp_month'],
            'expiration_year'  => $card['exp_year'],
            'card_code'  => "",                    
            'card_number'  => "************".$card['last4'],
            'is_default'   => true,
            'is_primary'   => $request->is_primary ?? 0,
            'card_token' => $card['id'],
        ]);

        $customer = $stripe->customers()->update($customer_id, [
            'default_source' => $card['id'],
        ]);

        //$card = $this->cards->set($request, \Auth::id());
        if ($request->is_primary == 1) {
            $this->subscription->where('user_id', \Auth::id())->update(['card_id' => $cards->id]);
        }
        //$this->cards->order($card, \Auth::id(), $request);*/

        return redirect()->route('profile')->with(['msg-type' => 'success', 'msg' => trans('frontend.added_successfully')]);
    }

    public function deletePayment($id)
    {

        $stripe = \Stripe::setApiKey(env('STRIPE_SECRET'));
        $customer_id = $this->subscription->where('user_id', \Auth::id())->where('is_cancelled', '0')->pluck('customer_id')->first();
        $card_id = CreditCards::where('user_id', \Auth::id())->where('id', $id)->pluck('card_token')->first();

        $stripe = \Stripe::setApiKey(env('STRIPE_SECRET'));
        
        try {
            $card = $stripe->cards()->delete($customer_id, $card_id);
        }catch (Exception $e) {
            $array = $e->getJsonBody();
            return redirect()->back()->with(['msg-type' => 'error', 'msg' => $array['error']['message']]);
        }

        CreditCards::where('user_id', \Auth::id())->where('id', $id)->delete();

        return redirect()->route('profile');
    }

    public function deleteDevice($id)
    {

        Device::where('user_id', \Auth::id())->where('id', $id)->delete();
        return redirect()->route('profile');
    }

    public function primaryPayment($id)
    {
        $this->cards->unPrimaryCards(\Auth::id());
        $this->cards->primaryCard($id);
        
        $customer_id = $this->subscription->where('user_id', \Auth::id())->where('is_cancelled', '0')->pluck('customer_id')->first();
        $card_id = CreditCards::where('user_id', \Auth::id())->where('id', $id)->pluck('card_token')->first();
        
        $stripe = \Stripe::setApiKey(env('STRIPE_SECRET'));
        
        $this->subscription->where('user_id', \Auth::id())->update(['card_id' => $id]);

        try {
            $customer = $stripe->customers()->update($customer_id, [
                'default_source' => $card_id,
            ]);
        }catch (Exception $e) {
            $array = $e->getJsonBody();
            return redirect()->back()->with(['msg-type' => 'error', 'msg' => $array['error']['message']]);
        }
        return redirect()->route('profile');
    }

    public function cancelSubscription($id)
    {
        $subscription = $this->subscription->where('id', $id)->first();
        /*$subscription->update(['is_cancelled' => 1]);
        $this->user->where('id', \Auth::id())->update(['is_premium' => 0]);
        $cancelCard = CreditCards::where('id', $subscription->card_id)->where('is_primary', 1)->delete();*/
        $this->removeSubscription();
        session()->flash('flash_message', 'Your request has been submitted successfully. Tasali Media Support Team will contact you very soon.');
        return redirect()->route('profile');
    }

    public function addToList(Request $request)
    {
        
        //$request['type'] = $type;
        //$request['id'] = $id;
        $this->userRepo->addToList($request, \Auth::user());
        
        return response()->json(['type' => 'success']);
        //return redirect()->back();
    }

    public function getMyList()
    {
        $data = $this->userRepo->getMyList(\Auth::user());
        return view('frontend.user-list', $data);
    }

    public function removeFromList(Request $request)
    {
        //$request['type'] = $type;
        //$request['id'] = $id;
        $this->userRepo->removeFromList($request, \Auth::user());
        return response()->json(['type' => 'success']);
        //return redirect()->back();
    }

    public function review(Request $request)
    {
        if ($request->type == "movie") {
            $this->movies->review($request, \Auth::id());
        } else {
            $this->shows->review($request, \Auth::id());
        }
        return redirect()->back();
    }

    public function deleteProfile($id){
        User::where('parent_id', \Auth::id())->where('id', $id)->delete();
        return redirect()->back();
    }

    public function removeSubscription(){
        $username = \Auth::user()->name;
        $email = \Auth::user()->email;
        $sendToMedia = array( 
            "name" => "Team Tasali Media",
            "message" => 'It is requested you to cancel my subscription from TasaliMedia. Here is my credentials.<br><br>Name: '.$username.'<br>Email : '.$email.'<br><br> Best Regards: '.$username.'<br>',
            "template" => "frontend.dynamic_email_template",
            "subject" => "Cancel My Subscription"
        );
        Mail::to('info@tasali.media')->send(new SendMail($sendToMedia));
    }

    public function checkSession(Request $request){  
        $device =  Device::where('device_token', $request->device_token)->where('user_id', $request->user_id)->get()->count();
        if($device !=null && $device > 0){
            return response()->json(['type' => 'error']);       
        }
        return response()->json(['type' => 'success']); 
    }
}
