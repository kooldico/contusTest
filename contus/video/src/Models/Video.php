<?php

/**
 * Video Model for videos table in database
 *
 * @name Video
 * @vendor Contus
 * @package Video
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2016 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Video\Models;

use Carbon\Carbon;
use Contus\Base\Helpers\StringLiterals;
use Contus\Base\Model;
use Contus\Video\Models\Cast;
use Contus\Video\Models\Comment;
use Contus\Video\Models\Customer;
use Contus\Video\Models\Tag;
use Contus\Video\Models\VideoCategory;
use Contus\Video\Models\VideoSeason;
use Contus\Video\Traits\VideoTrait;
use Jenssegers\Mongodb\Eloquent\HybridRelations;
use Symfony\Component\HttpFoundation\File\File;

class Video extends Model
{
    use VideoTrait, HybridRelations;
    /**
     * The database table used by the model.
     *
     * @vendor Contus
     *
     * @package Video
     * @var string
     */
    protected $table = 'videos';
    /**
     * Morph class name
     *
     * @var string
     */
    protected $morphClass = 'videos';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @vendor Contus
     *
     * @package Video
     * @var array
     */
    protected $fillable = ['category_id', 'title', 'description', 'is_featured', 'is_subscription', 'is_active', 'pdf', 'is_featured_time', 'published_on', 'video_category_slug', 'parent_category_slug', 'casts', 'crew', 'scenes'];

    /**
     * The attributes added from the model while fetching.
     *
     * @var array
     */
    protected $appends = ['genre_name', 'video_category_name', 'is_subscribed','is_subscriber','plan_name','user_subscribed_plan'];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
    /**
     * The attribute will used to generate url
     *
     * @var array
     */
    protected $url = ['thumbnail_image', 'poster_image'];

    protected $connection = 'mysql';
    /**
     * Constructor method
     * sets hidden for customers
     */
    public function __construct()
    {
        parent::__construct();
        $this->setHiddenCustomer(['id', 'notification_status', 'aws_prefix', 'is_hls', 'video_url', 'pipeline_id', 'preview_image', 'subscription', 'job_id', 'country_id', 'is_subscription', 'is_featured', 'trailer', 'disclaimer', 'subtitle_path', 'thumbnail_path', 'creator_id', 'updator_id', 'updated_at', 'archived_on', 'fine_uploader_uuid', 'fine_uploader_name', 'youtubePrivacy', 'liveStatus', 'pivot', 'created_at', 'short_description', 'broadcast_location', 'encoder_type', 'hosted_page_url', 'username', 'password', 'stream_name']);
    }

    /**
     * funtion to automate operations while Saving
     */
    public function bootSaving()
    {
        $this->setDynamicSlug('title');
        $this->saveImage('pdf');
        $this->saveImage('word');
        $keys = array('dashboard_categorynave', 'category_listing_page', 'dashboard_categories', 'dashboard_videos', 'category_live', 'category_tags', 'dashboard_live', 'dashboard_trending', 'dashboard_video_count', 'dashboard_pdf_count', 'dashboard_audio_count', 'is_live');
        $this->clearCache($keys);
    }

    /**
     * HasMany relationship between videos and video_categories
     */
    public function videocategory()
    {
        return $this->hasMany(VideoCategory::class);
    }

    /**
     * HasMany relationship between videos and video_countries
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'video_id');
    }

    /**
     * belongsToMany relationship between collection and collections_videos
     */
    public function playlists()
    {
        return $this->belongsToMany(Playlist::class, 'video_playlists', StringLiterals::VIDEOID, 'playlist_id');
    }

    /**
     * belongsToMany relationship between tag and video_tag
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'video_tag', StringLiterals::VIDEOID, 'tag_id');
    }

    /**
     * belongsToMany relationship between categories and video_categories
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'video_categories', StringLiterals::VIDEOID, 'category_id');
    }
    /**
     * Method for BelongsToMany relationship between video and favourite_videos
     *
     * @vendor Contus
     *
     * @package Customer
     * @return unknown
     */
    public function authfavourites()
    {
        if (config()->get('auth.providers.users.table') === 'customers') {
            return $this->belongsToMany(Customer::class, 'favourite_videos')->where('customer_id', (auth()->user()) ? auth()->user()->id : 0)->selectRaw('IF(count(*)>0,count(*),0) as favourite  , favourite_videos.created_at as favourite_created_at')->groupBy('favourite_videos.video_id');
        } else {
            return $this->belongsToMany(Customer::class, 'favourite_videos');
        }
    }
    /**
     * Get File Information Model
     * the model related for holding the uploaded file information
     *
     * @vendor Contus
     *
     * @package Base
     * @return Contus\Base\Model\Video
     */
    public function getFileModel()
    {
        return $this;
    }
    /**
     * Set the file to Staplaer
     *
     * @param \Symfony\Component\HttpFoundation\File\File $file
     * @param string $config
     * @return void
     */
    public function setFile(File $file, $config)
    {
        if (isset($config->image_resolution)) {
            $this->thumbnail_image = url("$config->storage_path/" . $file->getFilename());
            $this->thumbnail_path = $file->getPathname();
        }
        if (isset($config->is_file)) {
            $this->mp3 = url("$config->storage_path/" . $file->getFilename());
            $this->subtitle_path = $file->getPathname();
        }

        return $this;
    }
    /**
     * Store the file information to database
     * if attachment model is already has record will update
     *
     * @param Contus\Video\Models\Video $video
     * @return boolean
     */
    public function upload(Video $video)
    {
        return $video->save();
    }

    /**
     * Set explicit model condition for fronend
     *
     * {@inheritdoc}
     *
     * @see \Contus\Base\Model::whereCustomer()
     *
     * @return object
     */
    public function whereCustomer()
    {
        if (config()->get('auth.providers.users.table') === 'customers') {
            return $this->where('videos.is_active', '1')->where('job_status', 'Complete')->where('is_archived', 0)->whereIn('is_subscription', ((!empty(authUser()->id) && authUser()->isExpires()) ? [[0], [1]] : [0]));
        } else {
            return $this->where('job_status', 'Complete')->where('is_archived', 0);
        }
    }

    /**
     * Set explicit model condition for mobile
     *
     * {@inheritdoc}
     *
     * @see \Contus\Base\Model::whereliveVideo()
     *
     * @return object
     */
    public function whereliveVideo()
    {
        if (config()->get('auth.providers.users.table') === 'customers') {
            return $this->where('is_active', '1')->where('job_status', 'Complete')->where('is_archived', 0)->where('is_live', 1)->where('liveStatus', '!=', 'complete')->whereRaw('scheduledStartTime > "' . Carbon::now()->toDateString() . ' 00:00:00 "');
        }
    }
    /**
     * Get the scheduled as well as recorded live video lists
     */
    public function whereallliveVideo()
    {
        if (config()->get('auth.providers.users.table') === 'customers') {
            return $this->where('is_active', '1')->where('job_status', 'Complete')->where('is_archived', 0)->where('is_live', 1)->where('liveStatus', '!=', 'complete')->whereRaw('scheduledStartTime > "' . Carbon::now()->toDateString() . ' 00:00:00 "');
        }
    }
    /**
     * This function used to get the recorded live videos
     */
    public function whereRecordedliveVideo()
    {
        if (config()->get('auth.providers.users.table') === 'customers') {
            return $this->where('is_active', '1')->where('job_status', 'Complete')->where('is_archived', 0)->where('is_live', 1)->where('liveStatus', '!=', 'complete')->whereRaw('scheduledStartTime > "' . Carbon::now()->toDateString() . ' 00:00:00 "');
        }
    }
    /**
     * HasMany relationship between videos and video_posters
     */
    public function recent()
    {
        if (config()->get('auth.providers.users.table') === 'customers') {
            return $this->belongsTo(Customer::class)->where('customers.id', auth()->user()->id);
        } else {
            return $this->belongsToMany(Customer::class, 'recently_viewed_videos');
        }
    }
    public function season()
    {
        return $this->hasMany(VideoSeason::class, 'video_id', 'id');
    }

    /**
     * belongsToMany relationship between categories and video_seasons
     */
    public function castInfo()
    {
        return $this->belongsToMany(Cast::class, 'video_x_ray_cast', 'video_id', 'x_ray_cast_id')->where('x_ray_cast.is_active', 1);
    }

    /**
     * Get the category slug
     * @return string
     */
    public function getVideoCategorySlugAttribute()
    {
        $categoryString = '';
        $categories = $this->categories()->first();
        if (!empty($categories) && $categories->webseriesDetail()) {
            $categoryString = $categories->webseriesDetail['slug'];
        }
        return $categoryString;
    }

    public function getVideoDurationInSecAttribute()
    {
        // $this->video_duration
    }

    /**
     * Get the category name
     * @return string
     */
    public function getParentCategorySlugAttribute()
    {
        $categoryString = '';
        $categories = $this->categories()->first();
        if (!empty($categories) && $categories->parent_category()) {
            $categoryString = $categories->parent_category['slug'];
        }
        return $categoryString;
    }
    public function getPlanNameAttribute()
    {
        if(!empty($this->id)){
            $video_plan_detail = Video::where('id',$this->id)->first();
            if(!empty($video_plan_detail->plan_id)&&$video_plan_detail->plan_id !=="null"&&$video_plan_detail->is_special_video){
                $planid = $video_plan_detail->plan_id;
                $result_array = array();
                $planid = json_decode($planid);
                foreach ($planid as $each_number) {
                    $result_array[] = (int) $each_number;
                }
       $getPlanName = SubscriptionPlan::whereIn('id',$result_array)->select('name')->get();
       $result_array_name = array();
       foreach ( $getPlanName as $getPlanName) {
        $result_array_name[] =  $getPlanName->name;
    }
      return  implode (", ", $result_array_name);
    } } 
    }

    public function getUserSubscribedPlanAttribute() {
        if (!empty(authUser()->id)) {         
            $data = Subscribers::where('customer_id' , authUser()->id)->whereDate('end_date' , '>=',  Carbon::today()->toDateString())->where( 'is_active' , 1)->select('subscription_plan_id')->first();
            if(!empty($data)){
            $planName = SubscriptionPlan::where('id',$data->subscription_plan_id)->select('name')->first();
            return $planName->name; }
        } 
    }
}
