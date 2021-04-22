<?php

namespace App\Http\Controllers\Frontend\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Contracts\Options\OptionsContract;
use Contracts\Images\ImagesContract;
use Socialite;
use App\User;
use App\Device;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Agent;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use App\Country;
use Stevebauman\Location\Facades\Location;
use Symfony\Component\HttpFoundation\Session\Session;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;
    protected $redirectTo;

    public function __construct(OptionsContract $options,ImagesContract $images)
    {
        $this->middleware('guest', ['except' => 'logout']);
        $this->redirectTo = route('home');
        $this->options = $options;
        $this->images = $images;
    }

    public function showLoginForm()
    { 
        $data['title'] = trans('frontend.login');
        $login_image = $this->options->getByName('login_image');
        $data['image'] = $this->images->get($login_image->value);
        return view('auth.login', $data);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->invalidate();
        $val = $request->session()->get('home_loader',0);
        if($val == 0){
            $request->session()->put('home_loader',1);
        }       
        return redirect()->to('/login');
    }

    /**
     * Redirect the user to the Facebook authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Obtain the user information from Facebook.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback(Request $request)
    {
        if(isset($request->error) && $request->error_code =="200"){
            return redirect(route('/login'))->with(['msg-type' => 'error', 'msg' => $request->error_description]);
        }
        $userInfo = Socialite::driver('facebook')->user();
        $user = $this->createUser($userInfo);
        auth()->login($user);
        return redirect(route('home'));
    }
    
    function createUser($userInfo)
    {
        $user = User::where('fb_id', $userInfo->id)->first();
        if (!$user) {
            $user = User::create([
                'name'     => $userInfo->name,
                'email'    => $userInfo->email,
                'fb_id'    => $userInfo->id,
                'code' => request()->session()->get('code'),
                'amount' => request()->session()->get('amount'),
                'country' => request()->session()->get('country_name')
            ]);
        }
        return $user;
    }

    protected function attemptLogin(Request $request)
    {
        $result = $this->guard()->attempt($this->credentials($request), $request->filled('remember'));
        if($result){
            $ip = \Request::ip();
            $agent = new Agent();
            $user = User::where('email',$request->email)->first();
            if($user->role_id == 2)
                return $result;
            $device = Device::where('user_id', $user->id)->get();
            $count = $device->count();
            $device_token = $request->device_token;
            $existingDevice = Device::where(['user_id' => $user->id, 'device_token' => $device_token])->get();
            if($existingDevice->count() > 0){
                return $result;            
            }
            if($count < 2){
                $devices = new Device;
                $devices->ip = $ip;
                $devices->platform = $agent->platform();
                $devices->platform_version = $agent->version($agent->platform());
                $devices->browser = $agent->browser();
                $devices->browser_version = $agent->version($agent->browser());
                $devices->device = $agent->device();
                $devices->user_id = $user->id;
                $devices->device_token = $request->device_token;
                $devices->save();
                return $result;

            }else{
                $this->guard()->logout();
                $request->session()->put('user_id',$user->id);
                throw ValidationException::withMessages([
                    $this->username() => [trans('auth.device')],
                ]);
                return $result;
            }
        }else{
            return $result;
        }
    }

    protected function credentials(Request $request)
    {
        $credentials = $request->only($this->username(), 'password');
        $credentials['is_delete'] = 0;
        return $credentials;
    }

    protected function authenticated(Request $request, $user){
        $user->update([
            'last_login' => Carbon::now()->toDateTimeString(),
            'device_token' => $request->device_token           
        ]);

        $ip= \Request::ip();
        $position = Location::get('103.138.11.1');
        \DB::table('user_logs')->insert([
            "user_id" => $user->id, 
            "ip" => $ip, 
            "country" => $position->countryName, 
            "created_at" => Carbon::now()->toDateTimeString()
        ]);
    }

}
