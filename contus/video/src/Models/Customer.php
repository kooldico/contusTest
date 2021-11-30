<?php

namespace Contus\Video\Models;

use Contus\Base\BaseAuthenticatable;
use Contus\Video\Models\FavouriteVideo;
use Contus\Video\Models\Like;
use Contus\Video\Models\NotificationUser;
use Contus\Video\Models\Playlist;
use Contus\Video\Models\SubscriptionPlan;
use Contus\Video\Models\UserPlaylist;
use Contus\Video\Models\Video;
use Contus\Video\Models\WatchHistory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Carbon\Carbon;

class Customer extends BaseAuthenticatable implements JWTSubject
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'customers';
    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    /**
     * Morph class name
     *
     * @var string
     */
    protected $morphClass = 'customers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'phone', 'acesstype', 'is_active', 'device_token', 'device_type', 'profile_picture', 'age', 'country_code', 'iso'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password'];
    /**
     * The attribute will used to generate url
     *
     * @var array
     */
    protected $url = ['profile_picture'];

    /**
     * Tthe attributes used for soft delete
     */
    protected $dates = ['deleted_at'];

    /**
     * Constructor method
     * sets hidden for customers
     */
    public function __construct()
    {
        parent::__construct();
        $this->setHiddenCustomer(['id', 'password', 'access_otp_token', 'access_token', 'acesstype', 'created_at', 'creator_id', 'expires_at', 'facebook_auth_id', 'facebook_user_id', 'google_auth_id', 'google_user_id', 'is_active', 'login_type', 'remember_token', 'updated_at', 'updator_id', 'deleted_at', 'device_token', 'device_type', 'forgot_password', 'mypreferences', 'pivot', 'notify_comment', 'notification_status', 'notify_newsletter', 'notify_reply_comment', 'notify_videos']);
    }
    /**
     * funtion to automate operations while Saving
     */
    public function bootSaving()
    {
        $this->saveImage('profile_picture', 'required|mimes:jpeg,gif,png|image|max:5000');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Check if the current user is a subscribed user or not
     *
     * @vendor Contus
     *
     * @package Customer
     * @return boolean|date
     */
    public function isExpires()
    {
        $subscription = $this->activeSubscriber()->first();
        if (!isset($subscription) && is_null($this->expires_at)) {
            return false;
        }
        return $subscription->pivot->end_date;
    }
    /**
     * Method to retrive subscription information with belongsToMany relations
     *
     * @vendor Contus
     *
     * @package Customer
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function Subscriber()
    {
        return $this->belongsToMany(SubscriptionPlan::class, 'subscribers')->withPivot(['subscription_plan_id', 'customer_id', 'start_date', 'end_date', 'is_active']);
    }
    /**
     * Method to retrive subscription information with belongsToMany relations with only active subscription plan with start and end date
     *
     * @vendor Contus
     *
     * @package Customer
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany with active subscription plan
     */
    public function activeSubscriber()
    {
        return $this->belongsToMany(SubscriptionPlan::class, 'subscribers')->withPivot(['subscription_plan_id', 'start_date', 'end_date', 'is_active'])->where('subscribers.is_active', 1)->whereDate('subscribers.end_date', '>=', Carbon::today()->toDateString())->orderBy('subscribers.id', 'desc');
    }

    /**
     * Method for BelongsToMany relationship between video and favourite_videos
     *
     * @vendor Contus
     *
     * @package Customer
     * @return unknown
     */
    public function favourites()
    {
        return $this->hasMany(FavouriteVideo::class, 'customer_id', 'id');
    }
    /**
     * Method for BelongsToMany relationship between video and favourite_videos
     *
     * @vendor Contus
     *
     * @package Customer
     * @return unknown
     */
    public function followers()
    {
        return $this->belongsToMany(Playlist::class, 'follow_playlists', 'customer_id', 'playlist_id')->where('is_active', "1")->withTimestamps()->selectRaw('playlists.*,follow_playlists.created_at as follow_created_at, playlists.id as video_count, playlists.id as followers_count, playlists.id as following, playlists.id as video_info')->orderBy('follow_playlists.id', 'desc');
    }

    /**
     * Method for BelongsToMany relationship between video and favourite_videos
     *
     * @vendor Contus
     *
     * @package Customer
     * @return unknown
     */
    public function recentlyViewed()
    {
        return $this->belongsToMany(Video::class, 'recently_viewed_videos', 'customer_id', 'video_id')->withTimestamps()->selectRaw('videos.*,recently_viewed_videos.created_at as recent_created_at')->orderBy('recently_viewed_videos.id', 'desc');
    }

    /**
     * Function to format the firstname of the user
     * @param  [string] $value [Username]
     * @return [string]        [username]
     */
    public function getNameAttribute($value)
    {
        return ucfirst($value);
    }

    /**
     * Belongs to relationship between playlist and Playlist
     */
    public function userplaylist()
    {
        return $this->hasMany(UserPlaylist::class, 'id', 'user_id');
    }

    public function notificationUser()
    {
        return $this->hasOne(NotificationUser::class, 'user_id');
    }

    public function getFavouriteCountAttribute()
    {
        $favCount = 0;
        if (!empty(authUser()->id)) {
            $favCount = FavouriteVideo::where('customer_id', authUser()->id)->count();
        }
        return $favCount;
    }

    public function getLikedCountAttribute()
    {
        return Like::where('user_id', $this->id)->count();
    }

    public function getPlaylistCountAttribute()
    {
        return UserPlaylist::where('is_active', 1)->where('user_id', (string) $this->id)->count();
    }

    public function getNotificationCountAttribute()
    {
        $notificationUser = $this->notificationUser()->first();
        return ($notificationUser) ? $notificationUser->count : 0;
    }

    public function getWatchCountAttribute()
    {
        return WatchHistory::where('customer_id', (int) $this->id)->where('is_active', 1)->count();
    }

    public function getIsSubscribedAttribute()
    {
        $subscribe = 0;
        if (!empty($this->id)) {
            $subscribe = $this->isExpires() ? 1 : 0;
        }
        return $subscribe;
    }
}
