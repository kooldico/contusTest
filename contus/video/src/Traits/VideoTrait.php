<?php

/**
 * VideoTrait
 *
 * To manage the functionalities related to the Videos module from Video Controller
 *
 * @vendor Contus
 *
 * @package Video
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Video\Traits;

use Carbon\Carbon;
use Contus\Base\Helpers\StringLiterals;
use Contus\Video\Models\Ads;
use Contus\Video\Models\Category;
use Contus\Video\Models\Comment;
use Contus\Video\Models\Customer;
use Contus\Video\Models\PaymentTransactions;
use Contus\Video\Models\MobilePaymentTransactions;
use Contus\Video\Models\CustomerDeviceDetails;
use Contus\Video\Models\FavouriteVideo;
use Contus\Video\Models\Group;
use Contus\Video\Models\Like;
use Contus\Video\Models\Season;
use Contus\Video\Models\SeasonTranslation;
use Contus\Video\Models\Subscribers;
use Contus\Video\Models\UserPlaylist;
use Contus\Video\Models\Video;
use Contus\Video\Models\VideoAds;
use Contus\Video\Models\VideoAnalytic;
use Contus\Video\Models\VideoCategory;
use Contus\Video\Models\VideoMetaData;
use Contus\Video\Models\VideoTranslation;
use Contus\Video\Models\WatchHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Location;

trait VideoTrait
{
    /**
     * HasMany relationship between videos and video_posters
     */
    public function recentlyViewed()
    {
        return $this->belongsToMany(Customer::class, 'recently_viewed_videos', 'video_id', 'customer_id');
    }

    /**
     * HasMany relationship between videos and video_countries
     */
    public function recentlyWatched()
    {
        return $this->hasMany(WatchHistory::class, 'video_id', 'id');
    }

    public function getIsFavouriteAttribute()
    {
        $favStatus = false;
        if (!empty(authUser()->id)) {
            $favStatus = FavouriteVideo::where('customer_id', authUser()->id)->where('video_id', (int) $this->id)->exists();
        }

        return ($favStatus) ? 1 : 0;
    }

    /**
     * Method for BelongsToMany relationship between video and favourite_videos
     *
     * @vendor Contus
     *
     * @package Customer
     * @return unknown
     */
    public function favourite()
    {
        return $this->belongsToMany(Customer::class, 'favourite_videos');
    }

    /**
     * belongsToMany relationship between collection and collections_videos
     */
    public function group()
    {
        return $this->belongsToMany(Group::class, 'collections_videos', StringLiterals::VIDEOID, 'group_id')->withTimestamps();
    }

    public function getCollectionAttribute()
    {
        if ($this->group()->count() > 0) {
            return $this->group()->first()->toArray();
        }
        return new \stdClass();
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
    public function whereliveVideos()
    {
        if (config()->get('auth.providers.users.table') === 'customers') {
            return $this->where('is_active', '1')->where('job_status', 'Complete')->where('is_archived', 0)->where('is_live', 1)->where('liveStatus', '!=', 'complete');
        }
    }

    /**
     * Check whether user is liked the video or not
     *
     * @return object
     */
    public function getIsLikeAttribute()
    {
        $likeStatus = false;
        if (auth()->user()) {
            $likeStatus = Like::where('user_id', auth()->user()->id)->where('video_id', (int) $this->id)->where('type', Like::TYPE['like'])->exists();
        }
        return ($likeStatus) ? 1 : 0;
    }

    /**
     * Check whether user is disliked the video or not
     *
     * @return object
     */
    public function getIsDislikeAttribute()
    {
        $likeStatus = false;
        if (auth()->user()) {
            $likeStatus = Like::where('user_id', auth()->user()->id)->where('video_id', (int) $this->id)->where('type', Like::TYPE['dislike'])->exists();
        }
        return ($likeStatus) ? 1 : 0;
    }

    /**
     * Get the count of liked videos
     *
     * @return object
     */
    public function getLikeCountAttribute()
    {
        return Like::where('video_id', (int) $this->id)->where('type', Like::TYPE['like'])->count();
    }

    /**
     * Get the count of disliked videos
     *
     * @return object
     */
    public function getDislikeCountAttribute()
    {
        return Like::where('video_id', (int) $this->id)->where('type', Like::TYPE['dislike'])->count();
    }

    /**
     * Get the count of comments videos
     *
     * @return object
     */
    public function getCommentsCountAttribute()
    {
        return Comment::where('video_id', (int) $this->id)->count();
    }

    /**
     * Method for BelongsToMany relationship between video and favourite_videos
     *
     * @vendor Contus
     *
     * @package Customer
     * @return unknown
     */
    public function userPlaylist()
    {
        return $this->belongsToMany(UserPlaylist::class, 'playlist_videos', 'video_id', 'playlist_id');
    }

    /**Category
     * Get the category name
     * @return string
     */
    public function categoryName($id)
    {
        $categoryString = '';
        $category = Category::with('parent_category')->find($id);
        if (!empty($category->parent_category)) {
            $categoryString = $category->parent_category->title . ',';
        }
        return $categoryString . $category->title;
    }

    /**
     * Get the genre name
     * @return string
     */
    public function genreName($id)
    {
        return Group::find($id)->name;
    }

    /**
     * Get the genre name
     * @return string
     */
    public function getGenreNameAttribute()
    {
        $genre = $this->group()->first();
        if (!empty($genre)) {
            return $genre->name;
        }
        return '';
    }

    /**
     * Get the category name
     * @return string
     */
    public function getVideoCategoryNameAttribute()
    {
        $categoryString = '';
        $categories = $this->categories()->first();
        if (!empty($categories)) {
            $categoryString = $categories->title;
        }
        return $categoryString;
    }

    /**
     * Get the category name
     * @return string
     */
    public function getCategoryNameAttribute()
    {
        $categoryString = '';
        $categories = $this->categories()->first();
        if (!empty($categories) && $categories->parent_category()) {
            $categoryString = $categories->parent_category->title . ',';
        }
        if (!empty($categories)) {
            $categoryString .= $categories->title;
        }
        return $categoryString;
    }
    /**
     * Get the season name
     * @return string
     */
    public function getSeasonNameAttribute()
    {
        $videoIds = $this->id;
        $seasonData = Season::whereHas('videoSeason', function ($query) use ($videoIds) {
            $query->where('video_id', $videoIds);
        })->select('id', 'title')->where('is_active', 1)->first();
        if (!empty($seasonData)) {
            $trans = SeasonTranslation::select('title')
                ->where('language_id', $this->fetchLanugageId())->where('season_id', $seasonData->id)->first();
            if (!empty($trans)) {
                return $trans->title;
            }
            return $seasonData->title;
        }
    }
    /**
     * Get the season id
     */
    public function getSeasonIdAttribute()
    {
        $videoIds = $this->id;
        $seasonName = Season::whereHas('videoSeason', function ($query) use ($videoIds) {
            $query->where('video_id', $videoIds);
        })->select('id')->where('is_active', 1)->first();

        return !empty($seasonName) ? $seasonName->id : '';
    }

    /**
     * Get the tags names
     * @return string
     */
    public function tagNames()
    {
        return implode(',', $this->tags()->get()->pluck('name')->toArray());
    }

    /**
     * Funtion to append the demo feature in video listing page and detail page
     *
     * @return boolean
     */
    public function getIsSubscriberAttribute()
    {
        if (!empty(authUser()->id)) {
            return authUser()->isExpires() ? 1 : 0;
        } else {
            return 0;
        }
    }
    public function getIsSubscribedAttribute()
    {
        if (!empty(authUser()->id)) {  
            $video_detail = Video::where('id', $this->id)->select('is_special_video','plan_id')->first();
            if($video_detail && $video_detail->is_special_video){
                $data = Subscribers::where('customer_id' , authUser()->id)->whereDate('end_date' , '>=',  Carbon::today()->toDateString())->where( 'is_active' , 1)->select('subscription_plan_id')->first();
                if($data && $video_detail->plan_id!=="null"){
                    if(in_array($data->subscription_plan_id, json_decode($video_detail->plan_id))){
                        return  1 ;} else { return 0; }
                } else { return 0; }    
            }  else {  return authUser()->isExpires() ? 1 : 0;} 
         } else {return 0;}
    }
    /**
     * Funtion to append the demo feature in video listing page and detail page
     *
     * @return boolean
     */
    public function getAutoPlayAttribute()
    {
        if (!empty(authUser()->id) && authUser()->notificationUser()->count()) {
            return authUser()->notificationUser->auto_play;
        } else {
            return 0;
        }
    }

    /**
     * Get the is web series
     */
    public function getIsWebSeriesAttribute()
    {
        $isWebSeries = 0;
        $videoIds = $this->id;
        $video = Video::where('id', $videoIds)->first();
        if (!empty($video->categories()->first())) {
            $catInfo = $video->categories()->first();
            $parentInfo = $catInfo->parent_category()->first();
            if (!empty($parentInfo) && $parentInfo->is_web_series == 1) {
                $isWebSeries = 1;
            }
        }
        return $isWebSeries;
    }

    public function getPublishedOnAttribute()
    {
        return Carbon::parse($this->created_at)->toDateString();
    }
    /**
     * Method to record video analytics data
     * @param $video array
     *
     * @return boolean
     */
    public function addVideoAnalytics($video)
    {
        $ip = '';
        $videoAnalyticsData = array();
        /** This is call to the helper method to the get the IP address */
        $ip = getIPAddress();
        /** This is call to a method to get the current logged in user country based on the IP */
        $getcurrentIPLocation = Location::get($ip);
        $getcurrentIPLocationFlag = (isset($getcurrentIPLocation->countryName)) ? $getcurrentIPLocation->countryName : 'unknown';
        /** Call to method to get the platform (Web, ios or android) of the request */
        $platform = getPlatform();
        $customerId = (!empty(authUser()->id)) ? authUser()->id : 0;
        $videoAnalyticsData = [
            'video_id' => $video->id,
            'video_title' => $video->title,
            'customer_id' => $customerId,
            'country' => $getcurrentIPLocationFlag,
            'platform' => $platform,
        ];
        /** Set validator to check if all the parameters exist needed for video analytics */
        $validator = Validator::make($videoAnalyticsData, [
            'video_id' => 'required|integer',
            'video_title' => 'required|string',
            'customer_id' => 'required|integer',
            'country' => 'required|string',
            'platform' => 'required|string',
        ]);
        if ($validator->fails()) {
            $messages = $validator->messages()->toArray();
            foreach ($messages as $message) {
                app('log')->error(' ###File : VideoTrait.php ##Message : The video analytics insertion failed  ' . ' #Error : ' . $message[0]);
            }
        } else {
            $videoAnalytic = new VideoAnalytic();
            try {
                $videoAnalytic->fill($videoAnalyticsData);
                return $videoAnalytic->save();
            } catch (\Exception $e) {
                app('log')->error(' ###File : VideoTrait.php ##Message : The video analytics insertion failed  ' . ' #Error : ' . $e->getMessage());
            }
        }
        return false;
    }

    public function getVideoSeasons($webseries_details)
    {
        $category_id = Category::where('slug', $webseries_details->slug)->pluck('id');
        $videoCategory = DB::table('video_categories')->whereIn('category_id', $category_id)->get();
        if (count($videoCategory) > 0) {
            $fetchedVideos = Video::find($videoCategory[0]->video_id);
            return $this->getSeasons($fetchedVideos);
        } else {
            return [];
        }
    }
    /**
     * Function to fetch season in video detail Api
     */
    public function getSeasons($video)
    {
        $videoIds = [];
        $seasonArray = [];
        if (!empty($video->categories()->first())) {
            $catInfo = $video->categories()->first();
            $parentInfo = $catInfo->parent_category()->first();

            if (!empty($parentInfo) && $parentInfo->is_web_series == 1) {
                $category = $catInfo->id;
                $videoIds = $this->getvideoIdByCategory($category);
                $seasonArray = Season::whereHas('videoSeason', function ($query) use ($videoIds) {
                    $query->whereIn('video_id', $videoIds);
                })->where('is_active', 1)->get()->toArray();
            }
        }
        return $seasonArray;

    }
    /**
     * Function to fetch video ids
     */
    public function getvideoIdByCategory($category)
    {
        return VideoCategory::whereHas('video', function ($query) {
            $query->where('is_active', '1')->where('job_status', 'Complete')->where('is_archived', 0);
        })->where('category_id', $category)->pluck('video_id')->toArray();
    }

    /**
     * Function to fetch season videos
     */
    public function getSeasonVideoSlug($video, $season)
    {

        $category = '';
        if (!is_object($video)) {
            $video = Video::where($this->getKeySlugorId(), $video)->first();
        }
        if (!empty($video->categories()->first())) {
            $category = $video->categories()->first()->id;
        }

        $this->video = new Video();
        $this->video = $this->video->whereCustomer()->where('is_active', 1);

        $this->video = $this->video->whereHas('season', function ($query) use ($season) {
            $query->where('season_id', $season);
        })->whereHas('categories', function ($query) use ($category) {
            $query->where('categories.id', $category);
        })->selectRaw('videos.id,videos.title, videos.slug, videos.thumbnail_image, videos.id as is_favourite, videos.id as video_category_name, videos.id as is_like, videos.id as is_dislike, videos.id as like_count, videos.id as dislike_count, videos.id as auto_play, videos.id as season_name, videos.id as season_id, videos.price, videos.poster_image,videos.id as video_category_slug, videos.id as parent_category_slug, videos.trailer_hls_url,videos.trailer_status')->groupBy('videos.id')->orderBy('videos.id', 'asc');
        return $this->video->paginate(config('access.perpage'))->toArray();
    }
    public function getVideoSeasonVideoSlug($fetchedVideos, $season_id)
    {
        return $this->getSeasonVideoSlug($fetchedVideos, $season_id, 'web-series');
    }
    /**
     * belogsToMany relationship between video and video_translation
     */
    public function videoTranslation()
    {
        return $this->hasMany(VideoTranslation::class, 'video_id');
    }

    public function getTitleAttribute($value)
    {
        $trans = $this->videoTranslation()->where('language_id', $this->fetchLanugageId())->first();
        if (!empty($trans)) {
            return $trans->title;
        }
        return $value;
    }

    public function getDescriptionAttribute($value)
    {
        $trans = $this->videoTranslation()->where('language_id', $this->fetchLanugageId())->first();
        if (!empty($trans)) {
            return $trans->description;
        }
        return $value;
    }

    public function getPresenterAttribute($value)
    {
        $trans = $this->videoTranslation()->where('language_id', $this->fetchLanugageId())->first();
        if (!empty($trans)) {
            return $trans->presenter;
        }
        return $value;
    }

    public function fetchTranslationInfo($vId)
    {
        return app('cache')->tags([getCacheTag(), 'video_translation'])->remember(getCacheKey(1) . '_global_video_translation_' . $vId, getCacheTime(), function () {
            return $this->videoTranslation()->where('language_id', $this->fetchLanugageId())->first();
        });
    }

    public function fetchVideoUrl($videoId)
    {
        $videoInfo = Video::where('id', $videoId)->pluck('video_url');

        $result['org'] = $videoInfo[0];
        $result['path'] = (!empty($videoInfo)) ? cryptoJsAesEncrypt($videoInfo[0]) : '';
        return $result;
    }

    public function getSubtitleAttribute($value)
    {
        $result['base_url'] = env('AWS_BUCKET_URL');
        $result['subtitle_list'] = [];
        if ($value != '') {
            $result['subtitle_list'] = json_decode($value);
        }
        return $result;
    }

    public function getPassphraseAttribute()
    {
        $referer = app()->request->header('Referer');
        $TitleWithTime = $time = '';

        if ($referer == env('DOWNLOAD_REFERER') || isWebsite()) {
            $time = time();
            $TitleWithTime = cryptoJsAesEncrypt($time);
        }
        return $TitleWithTime;
    }

    public function getSpriteImageAttribute($value)
    {
        $imagePath = '';
        if ($value != '') {
            $imagePath = env('AWS_BUCKET_URL') . $value;
        }
        return $imagePath;
    }

    public function favouriteVideo()
    {
        return $this->hasMany(FavouriteVideo::class, 'video_id', 'id');
    }

    public function videoAds()
    {
        return $this->hasOne(VideoAds::class, 'video_id', 'id');
    }

    public function getAdsUrlAttribute()
    {
        $ads_url = '';
        $info = $this->videoAds()->first();
        if (!empty($info)) {
            $ads = $info->ads()->first();
            $ads_url = !empty($ads) ? $ads->ads_url : '';
        }
        return $ads_url;
    }
    /**
     * Method to convert geofencing regions into array
     *
     * @param array/object $geoData
     * @return array
     */
    public function convertRegionsintoArray($geoData)
    {
        $globallyAllowedRegions = array();
        array_walk_recursive($geoData, function ($v, $k) use (&$globallyAllowedRegions) {
            $k;
            array_push($globallyAllowedRegions, $v);
        });
        return $globallyAllowedRegions;
    }
    /**
     * Method to return global video view count from the settings
     *
     * @return string
     */
    public function getGlobalVideoViewCountAttribute()
    {
        return (int) config()->get('settings.general-settings.site-settings.video_view_count');
    }
    /**
     * Method to return the customer video view count
     *
     * @return int
     */
    public function getCustomerVideoViewCountAttribute()
    {
        if (!empty(authUser()->id)) {
            return WatchHistory::where('video_id', '=', $this->id)->where('customer_id', '=', authUser()->id)->count();
        }
    }

    /**
     * function to get all tags
     *
     * @vendor Contus
     *
     * @package video
     * @return unknown
     */
    public function getVideoCast()
    {
        return Video::where($this->getKeySlugorId(), $this->request->slug)
            ->where('is_active', 1)
            ->where('job_status', 'Complete')
            ->where('is_archived', 0)
            ->select('id')
            ->with('castInfo')
            ->first();
    }

    public function startIsPlaying()
    {
        $result = true;
        $result = $this->updateIsPlaying(true);
        if (isMobile()) {
            $customer = auth()->user();
            if (!empty($customer)) {
                $subscribed = Subscribers::where('customer_id', $customer->id)
                    ->where('is_active', 1)
                    ->with('subscriptionPlan')
                    ->first();
                $customerIsPlayingDeviceCount = CustomerDeviceDetails::where('customer_id', $customer->id)
                    ->where('is_playing', true)->count();
                $deviceLimit = $subscribed->subscriptionPlan->device_limit;
                if ($deviceLimit && $customerIsPlayingDeviceCount > $deviceLimit) {
                    $result = false;
                    $this->updateIsPlaying(false);
                }
            }
        }
        return $result;
    }

    public function stopIsPlaying()
    {
        return $this->updateIsPlaying(false);
    }

    public function updateIsPlaying($isPlaying)
    {
        $deviceId = $this->request->header('X-DEVICE-ID');
        $customer = auth()->user();

        if (!$customer || !$deviceId) {
            return true;
        }

        $customerDeviceDetails = CustomerDeviceDetails::where('customer_id', $customer->id)->where('device_id', $deviceId)->first();

        if ($customerDeviceDetails) {
            $customerDeviceDetails->is_playing = $isPlaying;
            $customerDeviceDetails->updated_at = Carbon::now(); // UTC Time is saved in DB
            $customerDeviceDetails->save();
        }

        return true;
    }

    /**
     * function to get video Id
     *
     * @package video
     */
    public function getVideoId($slug)
    {
        return DB::table('videos')->where('slug', $slug)->select('id', 'slug')->first();
    }

    /**
     * Function to load more videos in homescreen
     */
    public function getMore()
    {
        $result['error'] = false;
        $result['message'] = '';
        $result['data'] = '';
        $this->setRules(['type' => 'required|in:new,recent,section_one,section_two,section_three,banner,trending,genre']);
        $this->validate($this->request, $this->getRules());
        try {
            if ($this->request->type == 'genre') {
                $result['data'] = $this->fetchPopularGenre();
            } else {
                $result['data'] = $this->getVideoBlockByType($this->request->type);
            }
        } catch (\Exception $e) {
            $result['error'] = true;
            $result['message'] = $e->getMessage();
        }
        return $result;
    }
    /**
     * Method to verify video payment respective to customers
     *
     * @param object $video
     * @return array
     */
    public function verifyVideoPayment($video)
    {
        $status = '';
        $response['payMethod'] = 'SVOD';
        $isVideoPremium = $video->is_premium;
        $price = $video->price;
        $response['videoSlug'] = $video->slug;
        $is_bought = $this->getVideoPaymentInfo($video->id);
        /** Call to method to check if the user is authorized/premium user to watch the video */
        $isCustomerSubscribed = $this->getIsSubscriberAttribute();
        if (!$isVideoPremium && !$isCustomerSubscribed) {
            if ($price > 0 && $is_bought['is_bought'] === 0) {
                $status = 'unauthorized';
                $response['payMethod'] = 'TVOD';
            } else {
                $status = 'authorized';
            }
        } else if (($isVideoPremium && $isCustomerSubscribed) || (!$isVideoPremium)) {
            $status = 'authorized';
        } else if ($isVideoPremium && $is_bought['is_bought'] === 1) {
            $status = 'authorized';
        } else if ($price > 0 && $is_bought['is_bought'] === 0 && !$isCustomerSubscribed) {
            $status = 'unauthorized';
            $response['payMethod'] = 'TVOD';
        } else {
            $status = 'authorized';
        }
        return ['status' => $status, 'data' => $response];
    }
    /**
     * Method to verify the video permitted geo Locations
     *
     * @param string $geoSettingsType
     * @param int $videoId
     * @param string $countryCode
     * @param string $regionCode
     *
     * @return int
     */
    public function verifyGeoLocations($geoSettingsType, $videoId, $countryCode, $regionCode)
    {
        $allowedRegions = array();
        $geoModel = ($geoSettingsType == 'global_allowed_countries') ? $this->geoGlobalAllowedCountries
        : $this->geoIndividualAllowedCountries->where('video_id', '=', $videoId);
        $geoCollection = $geoModel->get();
        $allowedRegionsData = $geoModel->where('country_short_code', '=', (string) $countryCode)->pluck('regions')->toArray();
        $allowedRegions = $this->convertRegionsintoArray($allowedRegionsData);
        if ((count($geoCollection) > 0) && !in_array($regionCode, $allowedRegions, true)) {
            return 1;
        }
    }
    /**
     * Method to fetch next video in a episode
     *
     * @param array $episodes
     * @param int $current_key
     * @return array
     */
    public function fetchNextVideoInEpisode($episodes, $current_key)
    {
        return Video::where($this->getKeySlugorId(), $this->request->header('x-request-type') == 'mobile' ? $episodes[$current_key]['id'] : $episodes[$current_key]['slug'])->first();
    }
    /**
     * Function to fetch popular genre videos
     */
    public function fetchPopularGenre($paginate = true)
    {

        $this->setRules(['order' => 'sometimes|in:title', 'sort' => 'sometimes|in:asc,desc']);
        $this->validate($this->request, $this->getRules());

        $inputArray = $this->request->all();
        $collectionObj = new Group();
        $collection = $collectionObj
            ->join('collections_videos', 'collections_videos.group_id', '=', 'groups.id')
            ->join('videos', 'videos.id', '=', 'collections_videos.video_id')
            ->selectRaw('groups.*, count("collections_videos.id") as video_count')
            ->where('groups.is_active', 1)
            ->where('videos.is_active', 1)->where('videos.job_status', 'Complete')->where('videos.is_archived', 0);
        if (isset($inputArray['order']) && !empty($inputArray['order'])) {
            $sortName = ($inputArray['order'] == 'title') ? 'name' : $inputArray['order'];
            $collection = $collection->orderBy($sortName, $inputArray['sort']);
        } else {
            $collection = $collection->orderBy('video_count', 'desc');
        }

        $collection = $collection->groupBy('groups.id');
        $collection = ($paginate) ? $collection->paginate(config('access.perpage')) : $collection->get();
        $collection = $collection->toArray();
        $collection['category_name'] = trans('general.genre_videos');
        return $collection;
    }
    /**
     * Method to fetch the video meta title,description and keywords
     *
     * @param int $videoId
     * @return object
     */
    public function getVideoMetaData($videoId)
    {
        return VideoMetaData::select('title', 'description', 'keyword')->where('video_id', $videoId)->first();
    }
}
