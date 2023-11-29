<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;


class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use Notifiable;

    use Authenticatable, Authorizable, CanResetPassword;

    protected $table  = "user";
    protected $hidden = ["password"];
    protected $fillable = ['name','surname','email','username','password','role','status', 'picture', 'api_token'];

    public static $adminEmailUserIds = [7,31,52];
    public static $salesUserIds = [15,14,28,32,50,20,42,73];
    public static $mappingPageUserIds = [7, 31, 52, 66, 102, 125,  132];
    public static $roles = ['None', 'Administrator', 'Ad Ops', 'Publisher', 'API Client'];
    public static $statuses = ['None', 'Active', 'Suspended', 'Deleted'];

    const AD_OPS_HEAD = 31; //Nicholas Putter

    public static function generateFakeId($id)
    {
        $id  = $id + Auth::user()->id;
        $fakeId =  \Illuminate\Support\Facades\Crypt::encrypt($id);
        return $fakeId;
    }

    public static function generateRealId($encrypted)
    {
        $id = \Illuminate\Support\Facades\Crypt::decrypt($encrypted);
        $id = $id -  Auth::user()->id;
        return $id;
    }
    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    public static function withFakeId($data)
    {
        foreach ($data as $key => $value) {
            $data[$key]->fakeId = self::generateFakeId($value->id);
        }
        return $data;
    }

    public static function sales()
    {
        $ids = array_merge(self::$salesUserIds, User::where("role", 1)->pluck("id")->toArray());
        return self::where("status", 1)->whereIn("role", [1,2])->orderBy("name")->whereIn("id", $ids)->get();
    }

    public static function getRoles()
    {
        $roles = array_map(function($name, $id){
            return (object) [
                'name' => $name,
                'id' => $id
            ];
        }, self::$roles, array_keys(self::$roles));

        return $roles;
    }

    public static function getStatues()
    {
        $statuses = array_map(function($name, $id){
            return (object) [
                'name' => $name,
                'id' => $id
            ];
        }, self::$statuses, array_keys(self::$statuses));

        return $statuses;
    }

    public static function getPubisherIds()
    {
        $publisher_ids = [];

        if (Auth::user()->publishers && count(Auth::user()->publishers)) {
            foreach (Auth::user()->publishers as $publisher) {
                $publisher_ids[] = $publisher->publisher_id;
            }
        }
        return $publisher_ids;
    }

    public static function formatName($user)
    {
        return $user->name." ".$user->surname;
    }

    public function getFullname()
    {
        return static::formatName($this);
    }

    public static function adOps()
    {
        $ids = self::where("status", 1)->whereIn("role", [1,2])->orderBy("name")->pluck("id");
        return self::whereIn("id", $ids)->get();
    }

    public static function getCcAdminUsers()
    {
        $ids = self::$adminEmailUserIds;
        $users =  self::where("status", 1)
        ->whereIn("role", [1,2])
        ->orderBy("name")
        ->whereIn("id", $ids);

        return $users->get();
    }

    public function getRoleAttribute($role)
    {
        $r = static::getRoles();
        return $r[$role];
    }

    public function getStatusAttribute($status)
    {
        $s = static::getStatues();
        return $s[$status];
    }

    public function campaigns()
    {
        return $this->hasMany('App\Campaign', 'user_id_ad_op', 'id');
    }

    public function publishers()
    {
        return $this->hasMany('App\UserPublisher');
    }

    public function settings()
    {
        return $this->hasMany('App\UserSettings');
    }

    public function pastSearch()
    {
        return $this->hasMany('App\PastSearchedLocation', 'user_id', 'id');
    }

    public function savePublishers($publishers = [])
    {
        UserPublisher::where('user_id', $this->id)->delete();
        $data  = [];

        if (!empty($publishers)) {
            foreach ($publishers as $publisher) {
                $data[] = new UserPublisher(['publisher_id' => $publisher,'user_id'=>$this->id]);
            }

            $this->publishers()->saveMany($data);
        }
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function getRememberToken()
    {
        return $this->remember_token;
    }

    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }

    public function getRememberTokenName()
    {
        return "remember_token";
    }

    public function getReminderEmail()
    {
        return $this->email;
    }

    public function generatePictureHash()
    {
        return md5($this->email . User::formatName($this) . config('app.key') . time());
    }

    public function getInitials()
    {
        $initials = $this->name[0];

        if(!empty($this->surname)){
            preg_match_all('/(\w+)\s*/', $this->surname, $matches, PREG_SET_ORDER, 0);
            $surname = end($matches);
            $initials .= $surname[1][0];
        }

        return strtoupper($initials);
    }

    public function getAvatar(String $type = 'pin')
    {
        $ppFormat = $type == 'card' ? '<img class="img-responsive img-avatar-card" src="%s" />' : '<img class="img-circle img-avatar-pin" src="%s" />';
        $noFormat = $type == 'card' ? '<span class="img-initials img-initials-card">%s</span>'  : '<span class="img-circle img-initials-pin">%s</span>';

        return sprintf($this->picture ? $ppFormat : $noFormat, $this->picture ?? $this->getInitials());
    }

    public function getGravatar(){
        $hash = md5( strtolower( trim( $this->email ) ) );
        $size = 50;
        $grav_url = "https://www.gravatar.com/avatar/" . $hash . "?s=" . $size;
        return $grav_url;
    }
}
