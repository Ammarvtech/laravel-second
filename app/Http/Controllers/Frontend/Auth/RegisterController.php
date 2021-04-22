<?php

namespace App\Http\Controllers\Frontend\Auth;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Contracts\CreditCards\CreditCardContract;
use Contracts\Options\OptionsContract;
use Contracts\Genres\GenresContract;
use Illuminate\Support\Facades\Mail;
use App\Mail\sendMail;
use App\Rules\GoogleRecaptcha;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Country;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    use RegistersUsers;
    protected $redirectTo;

    public function __construct(User $user, CreditCardContract $cards, OptionsContract $config, GenresContract $genres)
    {
        $this->middleware('guest');
        $this->redirectTo = route('registerStep2');
        $this->cards = $cards;
        $this->config = $config;
        $this->genres = $genres;
        $this->user = $user;
    }

    public function showRegistrationForm()
    {
        //$config = $this->config->getByName('amount');
        $data['title'] = trans('frontend.register');
        $data['desc']  = trans('frontend.register');
        $data['genres']    = $this->genres->getAll();
        $data['countries']  = Country::getAllCountries();
        return view('auth.register', $data);
    }

    public function step3()
    {
        $data['title'] = trans('frontend.setup_payment');
        $data['desc']  = trans('frontend.setup_payment');

        return view('auth.register.step_3', $data);
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:20',
            'email'    => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed|required_with:password_confirmation',
            'country' => 'required',
            'city'    => 'required'
        ]);
    }

    protected function create(array $data)
    {   
        $new_array = array(
            "name" => $data['name']
        );
        
        $user = array( 
            'name' =>  $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            /*'code' => request()->session()->get('code'),
            'amount' => request()->session()->get('amount'),*/
            'country' => $data['country'],
            'city' => $data['city'],
            'preference' => "",
            'is_premium' => 0,
            'premium_start_date' => Carbon::now(),
            'premium_end_date' => Carbon::now()->addDays(14),
            'device_token' => $data['device_token']
        );

        $to_name = $data['name'];
        $to_email = $data['email'];
          
        Mail::send('frontend.signup-email', $new_array, function($message) use ($to_name, $to_email) {
            $message->to($to_email, $to_name)
            ->subject('Tasali Media Sign Up');
            $message->from('tasali@tasali.media','Tasali');
        });
        
        $user = User::create($user);
        $this->user->saveDevice($user);
        //Mail::to($data['email'])->send(new SendMail($new_array));
         
        return $user;
        
    }

    public function getCitiesList(Request $request){
        $cities = Country::getAllCities($request->code);
        return \Response::json($cities);
    }
}
