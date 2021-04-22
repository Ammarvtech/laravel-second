<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use App\Device;
use Jenssegers\Agent\Agent;

class User extends Authenticatable
{
    use Notifiable;
    protected $with = ['role'];
    protected $hidden = ['password', 'remember_token'];
    protected $fillable = [
        'parent_id',
        'name',
        'email',
        'fb_id',
        'is_admin',
        'is_verified',
        'password',
        'device_token',
        'device_type',
        "is_premium",
        "premium_start_date",
        "premium_end_date",
        "avatar",
        'preference',
        'amount',
        'code',
        'country',
        'last_login'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function followingMovies()
    {
        return $this->belongsToMany(Movie::class, "movie_series_users");
    }

    public function followingSeries()
    {
        return $this->belongsToMany(Show::class, "movie_series_users", "user_id", "series_id");
    }

    /*  public function pausedMovies()
     {
         return $this->belongsToMany(Movie::class, "continue_watching");
     }

     public function pausedSeries()
     {
         return $this->belongsToMany(Show::class, "continue_watching", "user_id", "series_id");
     } */

    public function pause()
    {
        return $this->hasMany(ContinueWatching::class);
    }

    public function favourite()
    {
        return $this->hasMany(Favourites::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class)->latest();
    }

    public function primaryCreditCards()
    {
        return $this->hasOne(CreditCards::class);
    }
    
    public function image()
    {
        return $this->belongsTo(Image::class, 'avatar');
    }

    ////////////////// SAve Device ///////////////

    public function saveDevice($user){
        $ip = \Request::ip();
        $agent = new Agent();
        $device = new Device;    
        $device->ip = $ip;
        $device->platform = $agent->platform();
        $device->platform_version = $agent->version($agent->platform());
        $device->browser = $agent->browser();
        $device->browser_version = $agent->version($agent->browser());
        $device->device = $agent->device();
        $device->user_id = $user->id;
        $device->device_token = $user->device_token;
        $device->save();
    }

}
