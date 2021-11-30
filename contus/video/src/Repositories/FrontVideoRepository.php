<?php

/**
 * Front Video Repository
 *
 * To manage the functionalities related to videos for the frontend
 *
 * @name FrontVideoRepository
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 *
 */

namespace Contus\Video\Repositories;

use Carbon\Carbon;
use Contus\Base\Helpers\StringLiterals;
use Contus\Base\Repository as BaseRepository;
use Contus\Geofencing\Models\GeoGlobalAllowedCountries;
use Contus\Geofencing\Models\GeoIndividualAllowedCountries;
use Contus\Geofencing\Models\GeoSettings;
use Contus\Video\Models\Category;
use Contus\Video\Models\Genre;
use Contus\Video\Models\Group;
use Contus\Video\Models\Comment;
use Contus\Video\Models\PaymentTransactions;
use Contus\Video\Models\Tag;
use Contus\Video\Models\TvodVideoPercentage;
use Contus\Video\Models\Video;
use Contus\Video\Models\VideoCategory;
use Contus\Video\Models\VideoTag;
use Contus\Video\Models\WatchHistory;
use Contus\Video\Traits\CollectionTrait;
use Contus\Video\Traits\VideoTrait as VideoTrait;
use Illuminate\Pagination\LengthAwarePaginator;
use DB;
use Contus\Video\Models\CustomerSelectedParental;
class FrontVideoRepository extends BaseRepository
{
    use VideoTrait, CollectionTrait;
/**
 * Construct method initialization
 *
 * Validation rule for user verification code and forgot password.
 */
    public function __construct()
    {
        parent::__construct();

        /**
         * Set other class objects to properties of this class.
         */
        $this->video = new Video();
        $this->category = new Category();
        $this->tag = new Tag();
        $this->videoTag = new VideoTag();
        $this->videoCategory = new VideoCategory();
        $this->categoryRepository = new CategoryRepository(new Category());
        $this->watchHistory = new WatchHistory();
        $this->geoGlobalAllowedCountries = new GeoGlobalAllowedCountries();
        $this->geoIndividualAllowedCountries = new GeoIndividualAllowedCountries();
        $this->geoSettings = new GeoSettings();
        $this->setRules([StringLiterals::TITLE => StringLiterals::REQUIRED, 'video_url' => StringLiterals::REQUIRED, 'is_featured_time' => StringLiterals::REQUIRED]);
    }

    /**
     * function to get video with complete information using slug
     *
     * @return unknown
     */
    public function getVideoSlug($slug, $commentList = false)
    {
        $this->video = new Video();
        $test = $this->getKeySlugorId();
        if($test == 'slug'){
            $genrevideoid = $this->video->select('id')->where('slug', $slug)->first();
            $genrevideoid = $genrevideoid->id;
        } else{
            $genrevideoid =  $slug;
        }
        //\Log::info($genrevideoid);
       // exit;
        $videoid = $this->video->select('id')->where($this->getKeySlugorId(), $slug);
       // \Log::info(print_r($this->getKeySlugorId(), true));
        //exit;
        $this->video = $this->video->whereCustomer()->where('is_active', 1)->where('videos.' . $this->getKeySlugorId(), $slug);

        if (is_null($this->video->first())) {
            $this->throwJsonResponse(false, 404, trans('video::videos.slugResponse'));
        }

        $fields = 'videos.id,videos.episode_order,videos.title,videos.slug,videos.description,videos.is_parental,videos.is_drm,videos.age_limit,videos.stream_id, videos.source_url,videos.release_date, videos.thumbnail_image,videos.video_duration,videos.hls_playlist_url,videos.dash_playlist_url,videos.is_live,videos.scheduledStartTime,videos.published_on,videos.presenter,videos.casts,videos.crew,videos.scenes,videos.is_premium, videos.poster_image, videos.view_count, videos.trailer_hls_url, videos.trailer_status, videos.id as is_favourite, videos.id as video_category_name, videos.id as is_like, videos.id as is_dislike, videos.id as like_count, videos.id as dislike_count, videos.id as auto_play, videos.id as season_name, videos.id as season_id, videos.subtitle, videos.trailer_hls_url, videos.id as passphrase, videos.created_at, videos.sprite_image, videos.id as ads_url, videos.id as comments_count, videos.price , id as global_video_view_count, videos.id as video_category_slug, videos.id as parent_category_slug';

        $this->video = $this->video->selectRaw($fields)->groupBy('videos.id');

        $this->video = ($this->request->header('x-request-type') == 'mobile') ? $this->video->first() : $this->video->with('tags')->first();
        $this->video['is_restricted'] = $this->handleVideoGeoFencing($this->video);
        $this->video['is_coupon_enabled'] = (config()->get('settings.site-global-settings.site-global-module-settings.coupon_code')) ? 1 : 0;
 $value_slug = $this->getKeySlugorId();
if($value_slug == "slug") { 
  $videoid = DB::table('videos')
        ->where('slug', $slug)
        ->pluck('id');
} else {
$videoid = $slug;
        }
  $audiotracks = DB::table('video_audio_uploads')
        ->select('audio_title')
        ->where('video_id', $videoid)
        ->get()
        ->toArray();
        $this->video['audio_tracks'] = $audiotracks;
  if(!empty(authUser()->id) && $this->video->is_parental == 0) {
        $userparental = CustomerSelectedParental::where('customer_id', authUser()->id)->first();
\Log::info("userparental");
\Log::info(print_r($userparental, true));
            if (!empty($userparental)) {
		$customer_certificatelist = array(); 
                $customer_certificatelist = json_decode($userparental->certificates, true);
\Log::info(print_r($customer_certificatelist, true));
\Log::info("agelimit");
\Log::info($this->video->age_limit);
                if(!empty($customer_certificatelist)){
                    if(in_array($this->video->age_limit, $customer_certificatelist )){
                        $this->video->is_parental = 1;
                    }
	        }
                
            }
        }
    
        $genre=Genre::where('video_id',$genrevideoid)->get()->toArray();


        if($genre){
            $group_id= json_decode($genre[0]['group_id']);
            $genre_name=Group::whereIn('id', $group_id)->select('name')->get()->toArray();
            $gname = array();
            foreach($genre_name as $g)
            {
                array_push($gname, $g['name']);             
            }
           
            $this->video->genre=$gname;
        }
        
       
        
        
        return $this->video;
    }

    /**
     * function to get payment details of the user for a particular video
     *
     * @return unknown
     */
    public function getVideoPaymentInfo($videoId)
    {
        $id = !empty(authUser()->id) ? authUser()->id : 0;

        $getPaymentInfo = PaymentTransactions::select('video_id', 'status', 'transaction_id', 'view_count', 'global_view_count')->where('customer_id', $id)->where('video_id', $videoId)->where('status', 'Paid')->where('transaction_id', '!=', '')->first();
        if (!empty($getPaymentInfo)) {
            $getPaymentDetail['is_bought'] = 1;
            $getPaymentDetail['transaction_id'] = $getPaymentInfo->transaction_id;
            $getPaymentDetail['user_view_count'] = $getPaymentInfo->view_count;
            $getPaymentDetail['global_view_count'] = $getPaymentInfo->global_view_count;
        } else {
            $getPaymentDetail['is_bought'] = 0;
            $getPaymentDetail['transaction_id'] = null;
            $getPaymentDetail['user_view_count'] = null;
            $getPaymentDetail['global_view_count'] = null;
        }
        return $getPaymentDetail;
    }

    /**
     * function to store/get TVOD video complete percentage details of the user for a particular video
     *
     * @return unknown
     */
    public function getVideoPercentageDetail($transaction_id, $complete_percentage)
    {
        $result = '';
        $tvodViewPercentage = new TvodVideoPercentage();
        $percentageData = $tvodViewPercentage->where('transaction_id', $transaction_id)->first();
        if (!empty($percentageData->transaction_id)) {
            if ($percentageData->complete_percentage == 50 && $complete_percentage == 100) {
                $tvodViewPercentage->where('transaction_id', $transaction_id)->delete();
                $result = 'success';
            } else {
                $result = 'updated';
            }
        } else {
            if ($complete_percentage == 50) {
                $tvodCompletePercentageData = [
                    'transaction_id' => $transaction_id,
                    'complete_percentage' => $complete_percentage,
                ];
                $tvodViewPercentage->fill($tvodCompletePercentageData);
                $tvodViewPercentage->save();
                $result = 'updated';
            } else {
                $result = 'updated';
            }
        }
        return $result;
    }
    /**
     * function to get video with complete information using slug
     *
     * @return unknown
     */
    public function getWatchVideoSlug($slug, $type=null)
    {
        $status = '';
        $response['payMethod'] = 'SVOD';
        $this->video = new Video();
        $this->video = $this->video->whereCustomer()->with(['recentlyWatched' => function ($query) {
            $query->where('customer_id', (auth()->user()) ? auth()->user()->id : 0)->orderBy('updated_at', 'desc');
        }])->where('is_active', 1)->where('videos.' . $this->getKeySlugorId(), $slug);

        if (is_null($this->video->first())) {
            $this->throwJsonResponse(false, 404, trans('video::videos.slugResponse'));
        }
        $this->video = $this->video->selectRaw('videos.id, videos.slug, videos.hls_playlist_url, videos.dash_playlist_url, videos.stream_id, videos.source_url, videos.is_premium, videos.is_drm, videos.is_live, videos.id as is_subscribed, videos.scheduledStartTime, videos.title, videos.subtitle, videos.id as passphrase, videos.sprite_image, videos.id as season_name, videos.id as ads_url, videos.price,videos.trailer_hls_url, videos.trailer_status')->first();
        if (empty($type)) {
            $videoPaymentInfo = $this->verifyVideoPayment($this->video);
            $status = $videoPaymentInfo['status'];
            $response = $videoPaymentInfo['data'];
        } else {
            $status = 'authorized';
        }
        $is_restricted = $this->handleVideoGeoFencing($this->video);
        $next_episode_slug = $this->handleNextEpisodeSlug($this->video);
        $this->video->next_episode_slug = $next_episode_slug;
        $this->video->is_restricted = $is_restricted;
        return ($status == 'authorized') ? ['status' => $status, 'data' => $this->video]
        : ['status' => $status, 'data' => $response];
    }

    /**
     * Method to get next episode url if its a webseries video
     */
    public function handleNextEpisodeSlug($videoValue)
    {
        $result = '';
        if ($videoValue->is_webseries) {
            $video = $this->getVideoSlug($this->request->header('x-request-type') == 'mobile' ? $videoValue->id : $videoValue->slug);
            $episodes = [];
            $seasons = [];
            $current_key;
            $current_season_key;
            $season = $video->season_id;
            $seasons = $this->getSeasons($video);
            $episodes = $this->getEpisodes($video, $season)->toArray();
            $current_key = array_search($video->slug, array_column($episodes, 'slug')) + 1;
            if (array_key_exists($current_key, $episodes)) {
                $result = $this->fetchNextVideoInEpisode($episodes, $current_key);
            } else {
                $episodes = [];
                $current_key = null;
                $current_season_key = array_search($season, array_column($seasons, 'id')) + 1;
                if (array_key_exists($current_season_key, $seasons)) {
                    $seasonId = $seasons[$current_season_key]['id'];
                    $episodes = $this->getEpisodes($video, $seasonId)->toArray();
                    $current_key = 0;
                    if (array_key_exists($current_key, $episodes)) {
                        $result = $this->fetchNextVideoInEpisode($episodes, $current_key);
                    }
                } else {
                    $result = null;
                }
            }
        } else {
            $result = null;
        }
        return $result;

    }

    /**
     * Method get Episodes
     */
    public function getEpisodes($video, $season)
    {
        if (!empty($video->categories()->first())) {
            $category = $video->categories()->first()->id;
        }
        $this->videoInstance = new Video();
        $this->videoInstance = $this->videoInstance->whereCustomer()->where('is_active', 1);
        $this->videoInstance = $this->videoInstance->whereHas('season', function ($query) use ($season) {
            $query->where('season_id', $season);
        })->whereHas('categories', function ($query) use ($category) {
            $query->where('categories.id', $category);
        })->selectRaw('videos.id, videos.episode_order, videos.slug')->groupBy('videos.id')->orderBy('videos.episode_order', 'asc');
        return $this->videoInstance->get();
    }

    /**
     * function to post video details in tvod_view_count Collection
     *
     * @return unknown
     */
    public function insertTvodViewCount($transaction_id)
    {
        $paymentTransactions = new PaymentTransactions;
        $paymentTransactions->where('transaction_id', $transaction_id)->increment('view_count');
        $data = $paymentTransactions->select('video_id', 'status', 'view_count', 'global_view_count')->where('transaction_id', $transaction_id)->first();
        if ($data->view_count >= $data->global_view_count) {
            $paymentTransactions->where('transaction_id', $transaction_id)->update(['status' => 'Expired']);
            $data = $paymentTransactions->select('video_id', 'status', 'view_count', 'global_view_count')->where('transaction_id', $transaction_id)->first();
        }

        return $data;
    }
    /**
     * function to post video details in tvod_view_count Collection
     *
     * @return unknown
     */
    public function getTransactionDetails($transaction_id)
    {
        $result = PaymentTransactions::select('video_id', 'status')->where('transaction_id', $transaction_id)->where('status', 'Paid')->first();
        return !empty($result->video_id) ? 'success' : 'failure';
    }
    /**
     * function to post video details in watch history
     *
     * @return unknown
     */
    public function postWatchHistory($video)
    {
        if ($video) {
            $video->increment('view_count');
            $ip = getIPAddress();
            if (!empty(authUser()->id)) {
                $this->watchHistory = $this->watchHistory->where('video_id', $video->id)->where('customer_id', authUser()->id)->first();
            } else {
                $this->watchHistory = $this->watchHistory->where('video_id', $video->id)->where('ip_address', $ip)->first();
            }
            if (is_object($this->watchHistory) && !empty($this->watchHistory->id)) {
                $this->watchHistory->is_active = 1;
                $this->watchHistory->updated_at = Carbon::now()->toDateTimeString();
                $this->watchHistory->save();
            } else {
                $watchHistory = new WatchHistory();
                $watchHistory->video_id = $video->id;
                $watchHistory->customer_id = (!empty(authUser()->id)) ? authUser()->id : '';
                $watchHistory->ip_address = (!empty(authUser()->id)) ? '' : $ip;
                $watchHistory->is_active = 1;
                $watchHistory->save();
            }
        }
    }
    /**
     * Method to handle geofencing for videos
     *
     * @param array $video
     * return boolean
     */
    public function handleVideoGeoFencing($video)
    {
        if ($video) {
            $videoId = (string) $video->id;
            $is_restricted = 0;
            $geoSettings = $this->geoSettings->select('type')->where('is_active', 1)->first();
            $geoSettingsType = $geoSettings->type;
            if (!empty($geoSettingsType) && $geoSettingsType != 'all_countries') {
                $userIPAddress = get_client_ip_env();
                /** sometimes IP is received as comma separated eg:143.110.129.41, 172.31.43.58 values so it is splitted to use the first address by default */
                if (strpos($userIPAddress, ',') !== false) {
                    $userIPAddress = explode(',', $userIPAddress);
                    $userIPAddress = (is_array($userIPAddress) && !empty($userIPAddress)) ? trim($userIPAddress[0]) : null;
                }
                $geoData = geoip($userIPAddress);
                $countryCode = $geoData['iso_code'];
                $regionCode = $geoData['state'];
                if ($countryCode === 'NA') {
                    $is_restricted = 1;
                } else {
                    $is_restricted = $this->verifyGeoLocations($geoSettingsType, $videoId, $countryCode, $regionCode);
                }
            }
            return $is_restricted;
        }
    }
    /**
     * function to get comments for video using slug
     *
     * @return unknown
     */
    public function getCommentsVideoSlug($slug, $getCount = 10, $paginate = true)
    {
        $inputArray = $this->request->all();
        if (!empty($inputArray['parent_id'])) {
            $commentList = Comment::with(['customer' => function ($query) {
                $query->withTrashed();
            }])->where('_id', $inputArray['parent_id'])->orderBy('_id', 'desc')->paginate(config('access.perpage'));
        } else {
            $video = Video::where($this->getKeySlugorId(), $slug)->first();
            $commentList = Comment::with(['customer' => function ($query) {
                $query->withTrashed();
            }])->where('video_id', $video->id)->whereNull('parent_id')->orderBy('_id', 'desc')->paginate(config('access.perpage'));
        }
        return $commentList;
    }

    /**
     * function to get live related videos
     *
     * @return object
     */
    public function getLiverelatedVideos($slug)
    {
        return $this->video->whereliveVideo()->where($this->getKeySlugorId(), '!=', $slug)->orderBy('scheduledStartTime', 'desc')->paginate(10, ['videos.id', 'videos.title', 'videos.slug', 'videos.thumbnail_image', 'videos.id as is_favourite', 'videos.is_live', 'videos.view_count', 'videos.is_premium', 'videos.price', 'videos.trailer_hls_url', 'videos.trailer_status', 'videos.is_parental', 'videos.age_limit']);
    }

    /**
     * function to get scheduled as well as upcomming live video lists
     *
     * @return array
     */
    public function getAllLiveVideos()
    {
        $videos = $this->video->whereallliveVideo()->orderBy('scheduledStartTime', 'ASC')->get()->toArray();
        return ['data' => $videos, 'next_page_url' => null, 'total' => count($videos)];
    }

    /**
     * function to get recent videos for video using slug
     *
     * @return array
     */
    public function getVideoByType($type)
    {
        $userId = (!empty(authUser()->id)) ? authUser()->id : 0;
        $video = $this->video->whereCustomer();
        if ($type == 'banner') {
            $video = $video->leftJoin('favourite_videos as f1', function ($j) {
                $j->on('videos.id', '=', 'f1.video_id')->on('f1.customer_id', '=', $userId);
            })->selectRaw('videos.*,count(f1.video_id) as is_favourite')->groupBy('videos.id')->with(['categories.parent_category.parent_category'])->where('is_live', '==', 0)->orderBy('id', 'desc')->take(5)->get();
        } elseif ($type == 'recent') {
            $video = $this->video->where('is_active', '1')->where('job_status', 'Complete')->where('is_archived', 0)->leftJoin('recently_viewed_videos as f1', function ($j) {
                $j->on('videos.id', '=', 'f1.video_id');
            })->where('f1.customer_id', '=', $userId)->selectRaw('videos.*')->groupBy('videos.id')->with(['categories.parent_category.parent_category'])->where('is_live', '==', 0)->orderBy('id', 'desc')->take(4)->get();
            foreach ($video as $k => $v) {
                $video[$k]['is_favourite'] = $v->authfavourites()->get()->count();
            }
            if (!count($video) > 0) {
                $video = $this->video->where('is_active', '1')->where('job_status', 'Complete')->where('is_archived', 0)->where('trailer_status', 1)->leftJoin('favourite_videos as f1', function ($j) {
                    $j->on('videos.id', '=', 'f1.video_id')->on('f1.customer_id', '=', $userId);
                })->selectRaw('videos.*,count(f1.video_id) as is_favourite')->groupBy('videos.id')->with(['categories.parent_category.parent_category'])->where('is_live', '==', 0)->orderBy('id', 'desc')->take(4)->get();
            }
        } elseif ($type == 'trending') {
            $video = $video->join('recently_viewed_videos', 'videos.id', '=', 'recently_viewed_videos.video_id')->where('recently_viewed_videos.created_at', '>', Carbon::now()->subDays(30))->selectRaw('videos.*,count("video_id") as count')->groupBy('recently_viewed_videos.video_id')->where('is_live', '==', 0)->orderBy('count', 'desc')->take(10)->get();
            foreach ($video as $k => $v) {
                $video[$k]['is_favourite'] = $v->authfavourites()->get()->count();
            }
        }
        return $video;
    }
    public function fetchContinueWatchingVideo($videoId)
    {
        if (empty((array) (authUser())) || empty($videoId)) {
            return null;
        }

        $customerId = authUser()->id;

        $watchHistory = WatchHistory::where('customer_id', $customerId)
            ->whereNotNull('seconds')
            ->where('seconds', '!=', '0')
            ->where('video_id', $videoId)
            ->where('seconds', '!=', '0.000')
            ->where('seconds', '!=', 0.0)
            ->where('continue_watching_is_active', 1)
            ->with(['video'])
            ->orderBy('updated_at', 'DESC')
            ->limit(15)
            ->first();

        if (empty($watchHistory) || empty($watchHistory->seconds)) {
            return null;
        }

        $watchedTill = $watchHistory->seconds;
        // "seconds":"88.909" // Sample

        $watchedTillInSec = round($watchedTill);

        $totalVideoDuration = $watchHistory->video->video_duration;
        // "video_duration":"2:33" // Sample

        $totalVideoDurationSplit = explode(":", $totalVideoDuration);

        $totalDurationInSec = 0;

        if (count($totalVideoDurationSplit) === 3) {
            $totalDurationInSec = $totalVideoDurationSplit[0] * 60 * 60 + $totalVideoDurationSplit[1] * 60 + $totalVideoDurationSplit[2];
        } elseif (count($totalVideoDurationSplit) === 2) {
            $totalDurationInSec = $totalVideoDurationSplit[0] * 60 + $totalVideoDurationSplit[1];
        }

        $percentage = ($watchedTillInSec * 100) / $totalDurationInSec;

        $watchHistory['progress_percentage'] = round($percentage);

        return $watchHistory;
    }

    public function fetchContinueWatchingVideos($videoId = null)
    {
        if (empty((array) (authUser()))) {
            return [];
        }

        $customerId = authUser()->id;

        $watchHistories = WatchHistory::
            select('video_id', 'seconds', 'customer_id', 'continue_watching_is_active', 'updated_at', 'created_at')
            ->where('customer_id', $customerId)
            ->whereNotNull('seconds')
            ->where('seconds', '!=', '0')
            ->where('seconds', '!=', '0.000')
            ->where('seconds', '!=', 0.0)
            ->where('continue_watching_is_active', 1);
        if ($videoId !== null) {
            $percentage = 0;
            $watchHistories->where('video_id', $videoId);
        }
        $watchHistories = $watchHistories->with(['video'])
            ->groupBy('customer_id', 'video_id')
            ->orderBy('updated_at', 'DESC')
            ->limit(15)
            ->get();

        foreach ($watchHistories as $key => $watchHistory) {
            $watchedTill = $watchHistory->seconds;
            // "seconds":"88.909" // Sample

            $watchedTillInSec = round($watchedTill);

            $totalVideoDuration = $watchHistory->video->video_duration;
            // "video_duration":"2:33" // Sample

            $totalVideoDurationSplit = explode(":", $totalVideoDuration);

            $totalDurationInSec = 0;

            if (count($totalVideoDurationSplit) === 3) {
                $totalDurationInSec = $totalVideoDurationSplit[0] * 60 * 60 + $totalVideoDurationSplit[1] * 60 + $totalVideoDurationSplit[2];
            } elseif (count($totalVideoDurationSplit) === 2) {
                $totalDurationInSec = $totalVideoDurationSplit[0] * 60 + $totalVideoDurationSplit[1];
            }

            $percentage = ($totalDurationInSec) ? ($watchedTillInSec * 100) / $totalDurationInSec : 0;
            $watchHistories[$key]['progress_percentage'] = round($percentage);

        }
        return $watchHistories;
    }

    /**
     * function to get recent videos for video using slug
     *
     * @return array
     */
    public function getVideoBlockByType($type)
    {
        $video = $this->video->whereCustomer();

//    \Log::info(print_r($video, true));   
 $fields = 'videos.id, videos.title, videos.slug, videos.thumbnail_image, videos.poster_image, videos.id as is_favourite, videos.is_live,videos.view_count,videos.is_premium, videos.price, videos.trailer_hls_url,videos.trailer_status,videos.is_parental,videos.age_limit,videos.is_kids';
        switch ($type) {
            case $type == 'banner':
                $fields = $fields . ' , videos.description, videos.poster_image ';
                $video = $this->fetchBannerVideos($fields, $this->video->where('videos.is_live', 0)->where('videos.is_active', 1));
                $video = $this->formatResponse('', $fields, $video, $type);
                break;
            case $type == 'recent':
                $video = $this->fetchRecentVideos($fields, $this->video);
                $video = $this->formatResponse('', $fields, $video, $type);
                break;
            case $type == 'trending':
                $video = $this->getTrendingVideos([]);
                $video = $this->formatResponse('', $fields, $video, $type);
                break;
            case $type == 'section_one':
                $nthCategory = $this->getTopNthCategory();
                $video = $this->formatResponse($nthCategory, $fields, $video, $type);
                break;
            case $type == 'section_two':
                $nthCategory = $this->getTopNthCategory(1);
                $video = $this->formatResponse($nthCategory, $fields, $video, $type);
                break;
            case $type == 'section_three':
                $nthCategory = $this->getTopNthCategory(2);
                $video = $this->formatResponse($nthCategory, $fields, $video, $type);
                break;
            default:
                $video = $this->fetchNewVideos($fields, $video);
                $video = $video->toArray();
                $video = $this->formatResponse('', $fields, $video, $type);
                break;
        }

        return $video;
    }
    public function formatResponse($nthCategory, $fields, $video, $type)
    {
        if (in_array(strtolower($type), ['section_one', 'section_two', 'section_three'])) {
            $categoryArray = $this->fetchChildren($nthCategory);
            $video = $this->fetchPopularVideos($video, $categoryArray, $fields);
            $video['category_name'] = (!empty($nthCategory)) ? trans('video::videos.popular_in') . ' ' . $nthCategory->title : '';
            $video['category_slug'] = (!empty($nthCategory)) ? $nthCategory->slug : '';
        } else {
            if ($type == 'banner') {
                $video['category_name'] = trans('general.banner_videos');
            } else if ($type == 'recent') {
                $video['category_name'] = trans('general.recent_videos');
            } else if ($type == 'trending') {
                $video['category_name'] = trans('general.trending_videos');
            } else {
                $video['category_name'] = trans('general.new_videos');
            }
            $video['category_slug'] = (!empty($nthCategory)) ? $nthCategory->slug : '';
        }
        $video['type'] = $type;
        return $video;
    }
    public function fetchChildren($category)
    {
        $catId = !empty($category['id']) ? $category['id'] : 0;

        $categoryArray = [];
        $categoryInfo = $this->category->with(['child_category'])->where('id', $catId)->first();
        if (!empty($categoryInfo)) {
            if (isset($categoryInfo['child_category'])) {
                foreach ($categoryInfo['child_category'] as $cat) {
                    $cat = $cat->makeVisible(['id']);
                    $categoryArray[$cat->id] = $cat->id;
                }
            }
            $categoryArray[] = $categoryInfo->id;
        }
        return $categoryArray;

    }

    public function fetchPopularVideos($video, $categoryArray, $fields = '')
    {
        $inputArray = $this->request->all();
        if ($fields == '') {
            $fields = 'videos.id, videos.title, videos.slug, videos.description,  videos.thumbnail_image, videos.hls_playlist_url, videos.id as is_favourite,  videos.poster_image,videos.is_live,videos.view_count,videos.is_premium, videos.trailer_hls_url, videos.trailer_status';
        }

        $result = $video->leftjoin('video_categories as vc', 'videos.id', '=', 'vc.video_id')
            ->selectRaw($fields)->where('is_live', '==', 0)->whereIn('vc.category_id', $categoryArray);

        if (!empty($inputArray['is_web_series'])) {
            $condition = '';
            $categoryString = (!empty($categoryArray)) ? implode(',', $categoryArray->toArray()) : '';
            if ($categoryString != '') {
                $condition = ' and vc2.category_id in (' . $categoryString . ')';
            }

            $sql = '(select v2.id, max(v2.view_count) as maxView, vc2.category_id
                                from videos as v2
                                left join video_categories as vc2 on (vc2.video_id = v2.id)
                                where v2.is_active = 1 and v2.is_archived = 0 and v2.job_status = "Complete" ' . $condition . ' group by  vc2.category_id) as sub ';

            $myfinal = Video::selectRaw($fields)
                ->leftjoin('video_categories as vc1', 'vc1.video_id', '=', 'videos.id')
                ->join(\DB::raw($sql), function ($query) {
                    $query->on('sub.maxView', '=', 'videos.view_count');
                    $query->on('sub.category_id', '=', 'vc1.category_id');
                })->whereIn('vc1.category_id', $categoryArray)->where('videos.is_live', 0)->where('videos.is_active', 1)->where('videos.job_status', 'Complete')->where('videos.is_archived', 0);
            $result = $myfinal->orderBy('videos.view_count', 'desc');
        } else {
            $result = $result->groupBy('videos.id')->orderBy('videos.view_count', 'desc');
        }
        return $result->paginate(config('access.perpage'))->toArray();

    }

    /**
     * Function to fetch new videos
     * @param  [string] $fields - sql fields
     * @param  [object] $video - Vreturn app('cache')->tags([getCacheTag(), 'videos', 'categories','video_categories'])->remember(getCacheKey().'_popular_videos', getCacheTime(), function () use($video, $categoryArray, $fields) {ideo object
     * @return object
     */
    public function fetchNewVideos($fields, $video, $categoryArray = [])
    {
        $order = 'id';
        $sort = 'desc';
        $inputArray = $this->request->all();
        if (isset($inputArray['order']) && !empty($inputArray['order'])) {
            $sort = (!empty($inputArray['sort'])) ? $inputArray['sort'] : 'asc';
            $order = $inputArray['order'];
        }

        $info = $video->selectRaw($fields)->where('is_live', '==', 0)->where('is_active', 1)->where('is_webseries', 0);
        if (!empty($categoryArray)) {
            $info->whereHas('categories', function ($query) use ($categoryArray) {
                $query->whereIn('categories.id', $categoryArray);
            });
        }

        if (!empty($inputArray['is_web_series'])) {
            $info = $info->leftjoin('video_categories as vc', 'videos.id', '=', 'vc.video_id')->groupBy('vc.category_id')->orderBy($order, $sort)->take(50)->get()->toArray();
        } else {
            $info = $info->groupBy('videos.id')->orderBy($order, $sort)->take(50)->get()->toArray();
        }

        $currentPage = (!empty($inputArray['page'])) ? $inputArray['page'] : 1;
        $perPage = config('access.perpage');
        if ($currentPage == 1) {
            $start = 0;
        } else {
            $start = ($currentPage - 1) * $perPage;
        }

        $currentPageCollection = array_slice($info, $start, $perPage);
        $paginatedTop100 = new LengthAwarePaginator(array_values($currentPageCollection), count($info), $perPage);
        $paginatedTop100->setPath(LengthAwarePaginator::resolveCurrentPath());
        return $paginatedTop100;

    }

    /**
     * Method to get the video watch history
     * @param Slug
     * @return Seconds
     */
    public function getWatchHistory($slug)
    {
        $video_id = Video::where('slug', $slug)->orWhere('id', $slug)->first();
        $video_id = $video_id->id;
        if (!empty(authUser()->id)) {
            $watchHistory = $this->watchHistory->where('video_id', $video_id)->where('customer_id', authUser()->id)->first();
        } else {
            $ip = getClientIp();
            $watchHistory = $this->watchHistory->where('video_id', $video_id)->where('ip_address', $ip)->first();
        }
        if ($watchHistory) {
            return $watchHistory->seconds;
        } else {
            return null;
        }
    }
}
