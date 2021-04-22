<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    use RegistersUsers;
    protected $redirectTo;

    public function __construct()
    {
        $this->redirectTo = route('registerStep2');
    }

    public function showRegistrationForm()
    {
        $data['title'] = trans('frontend.register');
        $data['desc']  = trans('frontend.register');

        return view('auth.register', $data);
    }

    public function step2()
    {
        $data['title'] = trans('frontend.choose_payment');
        $data['desc']  = trans('frontend.choose_payment');

        return view('auth.register.step_2', $data);
    }

    public function payment(Request $request)
    {
        $this->validate($request, [
            'card_number'   => 'required|min:14|max:14',
            'exp_date'      => 'required',
            'security_code' => 'required|min:3|max:4',
            'agree'         => 'required'
        ]);

        return redirect()->route('home')->with(['msg-type' => 'success', 'msg' => trans('frontend.register_successfully')]);
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
            'email'    => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
    }

    protected function create(array $data)
    {
        return User::create([
            'name' => "",
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'preference' => $data['preference'],
        ]);
    }
}
