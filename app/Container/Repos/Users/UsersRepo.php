<?php

namespace Repos\Users;


use App\CreditCards;
use App\Option;
use App\Subscription;
use Contracts\CreditCards\CreditCardContract;
use Contracts\Subscriptions\SubscriptionContract;
use Contracts\Users\UsersContract;
use Illuminate\Http\Request;
use App\User;
use Carbon\Carbon;
use Contracts\Images\ImagesContract;
use Tymon\JWTAuth\JWTAuth as CustomAuth;
use PhpParser\Error;
use Symfony\Component\HttpFoundation\Session\Session;

class UsersRepo implements UsersContract
{
    private $pagination = 20;

    public function __construct(
        User $user,
        //        CustomAuth $auth,
        Option $option,
        CreditCardContract $cards,
        SubscriptionContract $subscription_contract,
        ImagesContract $images
    ) {
        $this->user = $user;
        //        $this->auth                  = $auth;
        $this->option                = $option;
        $this->cards                 = $cards;
        $this->subscription_contract = $subscription_contract;
        $this->images = $images;
    }

    public function get($id)
    {
        return $this->user->findOrFail($id);
    }

    public function getChild($id){
        return $this->user->where('parent_id',$id)->get();
    }

    public function getPaginated()
    {
        return $this->user->paginate($this->pagination);
    }

    public function getAll()
    {
        return $this->user->all();
    }

    public function getAllByRole()
    {
        return $this->user->where('role_id', 1)->get()->all();
    }

    public function BackendFilter($request)
    {
        $q = $this->user;

        if (isset($request->keyword) && !empty($request->keyword)) {
            $q = $q->where(function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->keyword . '%')
                    ->orWhere('email', 'LIKE', '%' . $request->keyword . '%');
            });
        }

        if (isset($request->role) && $request->role != 'all') {
            $q = $q->where('role_id', $request->role);
        }

        return (object) [
            'count' => $q->count(),
            'data'  => $q->latest()->paginate($this->pagination)
        ];
    }

    public function set($data)
    {
        $this->user->name     = $data->name;
        $this->user->email    = $data->email;
        $this->user->password = bcrypt($data->password); // hash password first
        $this->user->role_id  = $data->role;
        $this->user->code = request()->session()->get('code');
        $this->user->amount = request()->session()->get('amount');
        $this->user->country = request()->session()->get('country_name');

        return $this->user->save();
    }

    public function update($data, $id)
    {
        $user = $this->get($id);

        $user->name    = $data->name;
        $user->email   = $data->email;
        $user->role_id = $data->role;

        if (!isset($data->password) || !empty($data->password)) {
            $user->password = bcrypt($data->password);
        }

        return $user->save();
    }

    public function delete($id)
    {
        return $this->get($id)->delete();
    }

    public function hasPermission($user, $key, $redirect = false)
    {
        $permissions = $user->role->permissions;

        foreach ($permissions as $permission) {
            if ($permission->name == $key) {
                return true;
            }
        }

        if ($redirect === true) {
            return abort(403, trans('backend.error_access'));
        }

        return false;
    }

    public function changePasword($pass, $user)
    {
        $user->password = bcrypt($pass);
        return $user->save();
    }

    public function getByEmail($email)
    {
        return $this->user->where('email', $email)->first();
    }

    public function login($data)
    {
        if (isset($data->fb_id)) {
            $user = $this->socialLoginOrRegistration($data);
            if (gettype($user) == 'integer') {
                return $user;
            }
            //            $token = $this->auth->fromUser($user, ["exp" => strtotime("+1 year", time())]);
        } else {
            //            $credentials = $data->only('email', 'password');
            $user        = $this->getByEmail($data->email);

            if (is_null($user)) {
                return config('api.response_code.wrong_credentials');
            }

            //check if current password are the same
            if (!password_verify($data->password, $user->password)) {
                return config("api.response_code.wrong_credentials");
            }
            $user->device_token = $data->device_token;
            $user->save();
        }

        // all good so return the token
        return $user;
    }

    protected function socialLoginOrRegistration($data)
    {
        try {
            $user = $this->user->where('fb_id', $data->fb_id)->first();
            if (is_null($user)) {
                $user = $this->user->firstOrCreate([
                    "name"         => $data->name,
                    "password"     => "",
                    "email"        => $data->email,
                    "fb_id"        => $data->fb_id,
                    "device_type"  => $data->device_type,
                    "device_token" => $data->device_token,
                    'avatar'       => (!is_null($data->image)) ? $this->upload_avatar($data) : $data->avatar,
                ]);
            }
        } catch (\Exception $exc) {
            return config("api.response_code.not_unique_email");
        }

        return $user;
    }

    protected function upload_avatar($data)
    {
        if (is_null($data->image)) {
            return "";
        }

        $ext      = $data->image->extension();
        $fileName = md5(microtime()) . rand(0000, 9999) . '.' . $ext;
        $path     = "avatar/" . date("Y") . "/" . date('m');

        $avatarPath = $data->image->storeAs($path, $fileName, [
            'disk'       => 's3',
            'visibility' => 'public',
        ]);

        return \Storage::disk('s3')->url($avatarPath);
    }

    public function register($data)
    {
        if (isset($data->fb_id)) {
            $user = $this->socialLoginOrRegistration($data);
            if (gettype($user) == 'integer') {
                return $user;
            }
        } else {
            $user = $this->user->create([
                'name'         => $data->name,
                'email'        => $data->email,
                'password'     => bcrypt($data->password),
                'device_token' => (isset($data->device_token)) ? $data->device_token : null,
                'device_type'  => (isset($data->device_type)) ? $data->device_type : null,
                "avatar"       => $this->upload_avatar($data)
            ]);
        }

        $this->renewalSubscription($data, $user);

        return $this->get($user->id);
    }

    public function forgetPassword($data)
    {
        $user = $this->getByEmail($data->email);
        if (!$user) {
            return config("api.response_code.email_not_exist");
        }

        if (!empty($user->fb_id)) {
            return config("api.response_code.social_email");
        } else {
            $token = generateToken();
            try {
                \DB::table('password_resets')->insert([
                    "email" => $data->email,
                    "token" => $token
                ]);
                $user->sendPasswordResetNotification($token);
            } catch (\Exception $exc) {
                return config("api.response_code.failed_send_mail");
            }
        }

        return config("api.response_code.success");
    }

    public function addToList($data, $user)
    {
        // TODO: Implement addToList() method.
        //        $user = $this->auth->toUser();
        if (is_null($user)) {
            return config("api.response_code.unauthorized_user");
        }

        if ($data->type == "movie") {
            $movie_ids = $user->followingMovies->pluck('id')->toArray();
            $user->followingMovies()->sync(array_merge($movie_ids, [$data->id]));
        } else {
            $series_ids = $user->followingSeries->pluck('id')->toArray();
            $user->followingSeries()->sync(array_merge($series_ids, [$data->id]));
        }

        return config("api.response_code.success");
    }

    public function getMyList($user)
    {
        // TODO: Implement addToList() method.
        //        $user = $this->auth->toUser($this->auth->getToken());
        if (is_null($user)) {
            return config("api.response_code.unauthorized_user");
        }

        return [
            "movie"  => $user->followingMovies,
            "series" => $user->followingSeries
        ];
    }

    public function logout()
    {
        try {
            $token = $this->auth->getToken();
            $this->auth->invalidate($token);
            $this->user->where('token', $token)->update(['token' => null]);

            return config("api.response_code.success");
        } catch (\Throwable $exception) {
            return config("api.response_code.error");
        }
    }

    public function editProfile($data, $user)
    {        
        $payment_config = $this->option->where("name", "freeSignUp")->first()['value'];

        if (!is_null($data->name)) {
            $user->name = $data->name;
        }

        if (!is_null($data->current_email)) {
            $old_email    = $this->getByEmail($data->email);

            if (!is_null($old_email)) {
                return config('api.response_message.not_unique_email');
            }

            if ($user->email != $data->current_email) {
                return config("api.response_message.wrong_email");
            }
            
            if (!password_verify($data->current_password, $user->password)) {
                return config("api.response_message.wrong_credentials");
            }
            $user->email = $data->email;
        }

        if (!is_null($data->password)) {

            //check if account in social one or not
            if (!is_null($user->fb_id)) {
                return config("api.response_message.social_email");
            }

            //check if current password are the same
            if (!password_verify($data->current_password, $user->password)) {
                return config("api.response_message.wrong_credentials");
            }

            return ($this->changePasword($data->password, $user)) ?
                array_merge(
                    $user->toArray(),
                    [
                        "required_payment" => $this->checkUserSubscription(
                            $user,
                            ($payment_config === 'true') ? false : true
                        )
                    ]
                ): config("api.response_message.error");
        }
        if (!is_null($data->image_id)) {
            $user->avatar = $data->image_id; //$this->images->set($data->image); //$this->upload_avatar($data);
        }

        return ($user->save()) ?
            array_merge(
                $user->toArray(),
                [
                    "required_payment" => $this->checkUserSubscription(
                        $user,
                        ($payment_config === 'true') ? false : true
                    )
                ]
            ) : config("api.response_message.error");
    }


    public function addProfile($data, $user)
    {        
        $payment_config = $this->option->where("name", "freeSignUp")->first()['value'];

        if (!is_null($data->name)) {
            $user->name = $data->name;
        }

        if (!is_null($data->current_email)) {
            $old_email    = $this->getByEmail($data->email);

            if (!is_null($old_email)) {
                return config('api.response_message.not_unique_email');
            }

            if ($user->email != $data->current_email) {
                return config("api.response_message.wrong_email");
            }
            
            if (!password_verify($data->current_password, $user->password)) {
                return config("api.response_message.wrong_credentials");
            }
            $user->email = $data->email;
        }

        if (!is_null($data->password)) {

            //check if account in social one or not
            if (!is_null($user->fb_id)) {
                return config("api.response_message.social_email");
            }

            //check if current password are the same
            if (!password_verify($data->current_password, $user->password)) {
                return config("api.response_message.wrong_credentials");
            }

            return ($this->changePasword($data->password, $user)) ?
                config("api.response_message.success") : config("api.response_message.error");
        }
        if (!is_null($data->image_id)) {
            $user->avatar = $data->image_id; //$this->images->set($data->image); //$this->upload_avatar($data);
        }

        return ($user->save()) ?
            array_merge(
                $user->toArray(),
                [
                    "required_payment" => $this->checkUserSubscription(
                        $user,
                        ($payment_config === 'true') ? false : true
                    )
                ]
            ) : config("api.response_message.error");
    }

    public function uploadImage($data, $user)
    {
        $ext      = $data->image->extension();
        $fileName = md5(microtime()) . rand(0000, 9999) . '.' . $ext;
        $path     = "avatar/" . date("Y") . "/" . date('m');

        $user->avatar = $data->image->storeAs($path, $fileName, [
            'disk'       => 's3',
            'visibility' => 'public',
        ]);

        return ($user->save()) ?
            \Storage::disk('s3')->url($user->avatar) : config("api.response_code.error");
    }

    public function setPayment($data, $user, $withRenewalSubscription = false)
    {
        (!is_null($user->primaryCreditCards))
            ? $this->cards->update($user->primaryCreditCards->id, $data)
            : $this->cards->set($data, $user->id);

        if ($withRenewalSubscription) {
            $this->renewalSubscription($data, $user);
        }

        return config("api.response_code.success");
    }

    public function getPayment($user)
    {
        $payment   = $user->primaryCreditCards()->first();
        $subscribe = $user->subscriptions()->first();

        $data['payment']      = (!is_null($payment))
            ? $payment
            : null;
        $data['subscription'] = (!is_null($subscribe))
            ? $subscribe
            : null;

        return $data;
    }

    public function cancelSubscription($data, $user)
    {
        $user_subscription = $user->subscriptions()->first();
        if (is_null($user_subscription)) {
            return config("api.response_code.error");
        }
        $flag = $user_subscription->update([
            'is_cancelled' => true
        ]);

        $this->cards->delete($user_subscription->card_id);

        return ($flag) ?
            config("api.response_code.success") : config("api.response_code.error");
    }

    public function renewalSubscription($data, $user)
    {
        $freeSignUp_config = $this->option->where("name", "freeSignUp")->first()['value'];
        $freeSignUp        = ($freeSignUp_config === "true") ? true : false;

        if ($freeSignUp) {
            $time                     = \Carbon\Carbon::now();
            $user->is_premium         = false;
            $user->premium_start_date = $time;
            $user->premium_end_date   = $time;

            $user->save();

            $this->subscription_contract->set($user, $freeSignUp);

            return config("api.response_code.success");
        }


        if (is_null($user->primaryCreditCards()->latest()->first())) {
            return config("api.response_code.error");
        }


        // new created account
        if (!$user->is_premium) {
            $duration = $this->option->where("name", "trial")->first()['value'];
        } else {
            $duration = $this->option->where("name", "subscription_duration")->first()['value'];
        }

        //payment action either for verification nor payment process
        $payment = true;

        if (!$payment) {
            $this->auth->invalidate($user->token);
            $user->update(['token' => null]);

            return config("api.response_code.error");
        }

        $time                     = \Carbon\Carbon::now();
        $user->is_premium         = true;
        $user->premium_start_date = $time;
        $user->premium_end_date   = \Carbon\Carbon::parse($time)->addDays($duration);

        $user->save();

        $this->subscription_contract->set($user, $freeSignUp);

        return config("api.response_code.success");
    }

    public function checkUserSubscription($user, $freeSignUpOption)
    {
        if (!$freeSignUpOption) {
            if ($user->premium_end_date < \Carbon\Carbon::now()) {
                return true;
            }

            return true;
        }

        return false;
    }

    protected function invalidateToken($user)
    {
        try {
            $previous_token = $user->token;
            if ($previous_token && $this->auth->authenticate($previous_token)) {
                $this->auth->invalidate($previous_token);
            }
        } catch (\Exception $exception) {
            //do nothing
        }

        return true;
    }

    public function removeFromList($data, $user)
    {
        // TODO: Implement addToList() method.
        if (is_null($user)) {
            return config("api.response_code.unauthorized_user");
        }
        if ($data->type == "movie") {
            $user->followingMovies()->detach($data->id);
        } else {
            $user->followingSeries()->detach($data->id);
        }

        return config("api.response_code.success");
    }

    public function countAll(){
        return $this->user->count();
    }

    public function totalVisitors($from_date, $to_date){
         return $this->user->whereDate('last_login', '<=', $from_date)->whereDate('last_login', '>=', $to_date)->count();
    }

    public function dateFilter($from_date, $to_date){
        $q = $this->user;
        $q =  $q->whereDate('created_at', '<=', $from_date);
        $q =  $q->whereDate('created_at', '>=', $to_date);
        return (object) [
            'count' => $q->count()
        ];
    }

    /////////////////////// Manage Profile /////////////////////

    public function storeProfile($data, $id){
        $user = $this->user->create([
                'parent_id'    => $id,
                'name'         => $data->name,
                'email'        => $data->email,
                'password'     => bcrypt($data->password),
                'device_token' => (isset($data->device_token)) ? $data->device_token : null,
                'device_type'  => (isset($data->device_type)) ? $data->device_type : null,
                'code' => request()->session()->get('code'),
                'amount' => request()->session()->get('amount'),
                'country' => request()->session()->get('country_name'),
            ]);
        return $user;
    }
}
