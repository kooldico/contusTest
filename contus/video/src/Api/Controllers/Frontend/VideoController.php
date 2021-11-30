<?php

/**
 * Video Controller
 *
 * To manage the Video such as create, edit and delete
 *
 * @version 1.0
 * @author Contus Team <developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 *
 *
 */
namespace Contus\Video\Api\Controllers\Frontend;

use Contus\Video\Models\CustomerDeviceDetails;
use Contus\Video\Models\Subscribers;
use Contus\Video\Models\Setting;
use Contus\Video\Repositories\CategoryRepository;
use Contus\Video\Repositories\FavouriteVideoRepository;
use Contus\Video\Repositories\FrontVideoRepository;
use Contus\Video\Repositories\PlaylistRepository;
use Illuminate\Http\RedirectResponse;
use DateTime;
use Firebase\JWT\JWT;

class VideoController extends CustomVideoController
{
    public $awsRepository;
    /**
     * constructor funtion for video controller
     *
     * @param FrontVideoRepository $videosRepository
     * @param CustomerRepository $customerrepositary
     * @param CategoryRepository $categoryRepository
     * @param SubscriptionRepository $subscriptionRepository
     * @param TestimonialRepository $testimonialrepositary
     * @param PlaylistRepository $playlist
     * @param FavouriteVideoRepository $favourties
     */
    public function __construct(FrontVideoRepository $videosRepository, CategoryRepository $categoryRepository, PlaylistRepository $playlist, FavouriteVideoRepository $favourties)
    {
        parent::__construct();
        $this->repository = $videosRepository;
        $this->category = $categoryRepository;
        $this->playlist = $playlist;
        $this->favouritevideos = $favourties;
        $this->repoArray = ['repository', 'category', 'playlist'];
    }
    /**
     * This Function used to get all upcomming and recorded live videos list
     *
     * @return json
     */
    public function browseAllLiveVideos()
    {
        $fetch['server_time'] = date("Y-m-d H:i:s", time());
        $result = $this->repository->fetchLiveVideos();
        if (!$result['error']) {
            $result['data']['server_time'] = date("Y-m-d H:i:s", time());
            return $this->getSuccessJsonResponse(['message' => trans('video::videos.fetch.success'), 'response' => $result['data']]);
        } else {
            return $this->getErrorJsonResponse([], trans('video::videos.fetch.error'));
        }
    }

    /**
     * This Function used to get all upcomming and recorded live videos list
     *
     * @return json
     */
    public function recommendedVideos()
    {
        $videoIds = $this->request->slug_list;
        $result = $this->repository->fetchRecommendedVideos($videoIds);
        if (!$result['error']) {
            return $this->getSuccessJsonResponse(['message' => trans('video::videos.fetch.success'), 'response' => $result['data']]);
        } else {
            return $this->getErrorJsonResponse([], trans('video::videos.fetch.error'));
        }
    }

    /**
     * This Function used to get all upcomming and recorded live videos list
     *
     * @return json
     */
    public function browseMoreLiveVideos()
    {
        $fetch['server_time'] = date("Y-m-d H:i:s", time());
        $result = $this->repository->fetchMoreLiveVideos();
        if (!$result['error']) {
            $videoInfo['server_time'] = date("Y-m-d H:i:s", time());
            $videoInfo['upcoming_live_videos'] = $result['data'];
            return $this->getSuccessJsonResponse(['message' => trans('video::videos.fetch.success'), 'response' => $videoInfo]);
        } else {
            return $this->getErrorJsonResponse([], trans('video::videos.fetch.error'));
        }
    }

    /**
     * Function to send all video list, category list, tag list
     *
     * @return json
     */
    public function browseVideos()
    {
        $result = $this->repository->searchAllVideo();
        if (isset($result['error']) && $result['error']) {
            return $this->getErrorJsonResponse([], trans('video::videos.fetch.error'));
        } else {
            return $this->getSuccessJsonResponse(['message' => trans('video::videos.fetch.success'), 'response' => $result['data']]);
        }
    }
    /**
     * Function to send all video cast list, tag list
     *
     * @return json
     */
    public function castList()
    {
        $result = $this->repository->getVideoCast();
        if (isset($result['error']) && $result['error']) {
            return $this->getErrorJsonResponse([], trans('video::videos.fetch.error'));
        } else {
            return $this->getSuccessJsonResponse(['message' => trans('video::videos.fetch.success'), 'response' => $result]);
        }
    }

    public function canCustomerDownloadVideo()
    {
        $customer = auth()->user();

        if (!empty(config()->get('settings.site-global-settings.site-global-module-settings.screen_restriction')) && $customer) {
            $subscribed = Subscribers::where('customer_id', $customer->id)
                ->where('is_active', 1)
                ->with('subscriptionPlan')
                ->first();

            if ($subscribed != null && $subscribed->subscriptionPlan) {
                $customerIsPlayingDeviceCount = CustomerDeviceDetails::where('customer_id', $customer->id)
                    ->where('is_playing', true)->count();

                $deviceLimit = $subscribed->subscriptionPlan->device_limit;

                if ($deviceLimit && $customerIsPlayingDeviceCount >= $deviceLimit) {
                    return $this->getSuccessJsonResponse(['message' => 'Okay', 'response' => ['can_download' => false]]);
                }
            }
        }

        return $this->getSuccessJsonResponse(['message' => 'Okay', 'response' => ['can_download' => true]]);
    }

    public function postHeartBeat()
    {
        $result = $this->repository->startIsPlaying();
        // Using different response to reduce the payload because this API will be called Frequently
        if ($result) {
            return $this->getSuccessJsonResponse(['message' => 'Okay'], 200);
        } else {
            return $this->getErrorJsonResponse([], trans('video::videos.video_unauthorized_access'), 403);
        }
    }

    public function postStopHeartBeat()
    {
        $this->repository->stopIsPlaying();
        // Using different response to reduce the payload because this API will be called Frequently
        return $this->getSuccessJsonResponse(['message' => 'Okay'], 200);
    }

    /**
     * Function to send video details with, related videos, search tag, subscription details, comments
     *
     * @return json
     */
    public function browseVideo($slug = '', $playlist_id = '')
    {
        $fetchedVideos = $this->repository->getVideoSlug($slug);
        if (isset($fetchedVideos->is_live) && $fetchedVideos->is_live !== 0) {
            $fetch['video_info'] = $fetchedVideos->makeHidden(['video_url', 'youtube_id', 'liveStatus']);
            $fetch['related'] = $this->repository->getLiverelatedVideos($slug);
        } else {
            $fetch['video_info'] = $fetchedVideos;
            if ($playlist_id) {
                $fetch['related'] = $this->playlist->getPlaylistByVideosRelated($playlist_id, $slug);
            } else if ($fetchedVideos->is_web_series == 1) {
                $fetch['related'] = $this->repository->getSeasonVideoSlug($fetchedVideos, $fetchedVideos->season_id);
            } else {
                $fetch['related'] = $this->category->getRelatedVideoSlug($slug, 10, true);
            }
        }
        $fetch['continue_watching_detail'] = $this->repository->fetchContinueWatchingVideo($fetch['video_info']->id);

        $fetch['comments'] = $this->repository->getCommentsVideoSlug($slug, 3, false);
        $fetch['seasons'] = ($playlist_id != '' && $playlist_id != 0) ? [] : $this->repository->getSeasons($fetchedVideos);
        $fetch['payment_info'] = $this->repository->getVideoPaymentInfo($fetch['video_info']->id);
        $fetch['video_info']->seconds = $this->repository->getWatchHistory($fetch['video_info']->slug);
        $fetch['video_meta_data'] = $this->repository->getVideoMetaData($fetch['video_info']->id);
//        \Log::info(print_r($fetch, true));
         
                   // $fetch['drm_token'] = $jwt;
        if (array_filter($fetch)) {
            return $this->getSuccessJsonResponse(['message' => trans('video::videos.fetch.success'), 'response' => $fetch]);
        } else {
            return $this->getErrorJsonResponse([], trans('video::videos.fetch.error'));
        }
    }

    /**
     * Function to render meta tags in HTML page for a video page sharing
     * Works for both Facebook and Twitter
     *
     * @return blade or @return redirect
     */
    public function getMetaData($slug)
    {
        try {

            // Returns 'https://vplayed-uat.contus.us/' if WEB_SITE_URL is not set...
            $webAppDomainName = env('WEB_SITE_URL', 'https://vplayed-uat.contus.us/');
            $apiBasePath = env('API_URL', 'https://vplayed-uat.contus.us/medias/');

            if ($this->request->has('is_webseries')&& $this->request->is_webseries!=='0') {
                $videoDetails = $this->category->browseWebseries($slug, 'meta');
                $pathPrefix = $webAppDomainName . 'webseries/';
            } else {
                $videoDetails = $this->repository->getVideoSlug($slug);
                $pathPrefix = $webAppDomainName . 'video/';
            }

            if (
                strpos($_SERVER["HTTP_USER_AGENT"], "facebookexternalhit/") !== false ||
                strpos($_SERVER["HTTP_USER_AGENT"], "Facebot") !== false || // Facebook crawler Boot
                strpos($_SERVER["HTTP_USER_AGENT"], "Twitterbot") !== false// Twitter crawler Boot
            ) {
                return view('metadata', ['videoDetails' => $videoDetails, 'apiBasePath' => $apiBasePath]); // return the view page
            } else {
                $redirectUrl = $pathPrefix . $videoDetails->slug;

                return new RedirectResponse($redirectUrl); // return redirect to the actual page
            }
        } catch (Exception $ex) {

            throw new Exception($ex);
        }
    }

    /**
     * Get video id based on given slug
     */
    public function getVideoId($slug = '')
    {
        $video = $this->repository->getVideoId($slug);
        if ($video) {
            return $this->getSuccessJsonResponse(['message' => trans('video::videos.fetch.success'), 'response' => $video]);
        }
    }

    /**
     * Function to send video details with, related videos, search tag, subscription details, comments
     *
     * @return json
     */
    public function browseWebseriesSeasonVideo($slug, $season = '')
    {
        $fetch['season_list'] = $this->repository->getSeasonVideoSlug($slug, $season, 'web-series');
        if (array_filter($fetch)) {
            return $this->getSuccessJsonResponse(['message' => trans('video::videos.fetch.success'), 'response' => $fetch]);
        } else {
            return $this->getErrorJsonResponse([], trans('video::videos.fetch.error'));
        }
    }

    /**
     * Function to send video details
     *
     * @return json
     */
    public function saveTvodViewCount()
    {
       // \Log::info("tvod works fine");
        $transaction_id = $this->request->transaction_id;
        $complete_percentage = $this->request->complete_percentage;
        $data = $this->repository->getTransactionDetails($this->request->transaction_id);
        if ($data == 'success') {
            $result = $this->repository->getVideoPercentageDetail($transaction_id, $complete_percentage);
            if ($result == 'success') {
                $fetchedVideos = $this->repository->insertTvodViewCount($transaction_id);
                $videoResult = $this->getSuccessJsonResponse(['message' => trans('video::videos.view_count_updated'), 'response' => $fetchedVideos]);
            } else if ($result == 'updated') {
                $videoResult = $this->getSuccessJsonResponse([], trans('video::videos.get_view_count'));
            } else {
                $videoResult = $this->getErrorJsonResponse([], trans('video::videos.user_unauthorized_access'));
            }
        } else {
            $videoResult = $this->getErrorJsonResponse([], trans('video::videos.user_unauthorized_access'));
        }
        return $videoResult;
    }

    /**
     * This function used to get the related and trending videos based on type
     *
     * @return json
     */
    public function browseRelatedTrendingVideos()
    {
        $this->category->validateVideoType();

        if ($this->request->type == 'recent' || $this->request->type == 'related') {
            if ($this->request->has('playlist_id') && $this->request->playlist_id != '') {
                $fetch = $this->playlist->getPlaylistByVideosRelated($this->request->playlist_id, $this->request->id);
            } else if (!empty($this->request->id)) {
                $fetch = $this->category->getRelatedVideoSlug($this->request->id);
            } else {
                $fetch['recent'] = $this->repository->getVideoByType('recent');
            }
        } else {
            $fetch['trending'] = $this->repository->getVideoByType('trending');
        }

        if ($fetch) {
            $return = $this->getSuccessJsonResponse(['message' => trans('video::videos.fetch.success'), 'response' => $fetch]);
        } else {
            $return = $this->getErrorJsonResponse([], trans('video::videos.fetch.error'));
        }
        return $return;
    }

    /**
     * To diplayed the dashboard videos like banner, recent and trending videos
     *
     * @return json
     */
    public function getHome()
    {
        $fetch['section_one'] = $this->repository->getVideoBlockByType('section_one');
        $fetch['section_two'] = $this->repository->getVideoBlockByType('section_two');
        $fetch['section_three'] = $this->repository->getVideoBlockByType('section_three');
        $result['home_content'] = array_values($fetch);
        $result['statistics'] = $this->attachResponse();
        if ($fetch) {
            return $this->getSuccessJsonResponse(['message' => trans('video::videos.fetch.success'), 'response' => $result]);
        } else {
            return $this->getErrorJsonResponse([], trans('video::videos.fetch.error'));
        }
    }
    /**
     * Function to load next set of videos
     * @return json
     */
    public function getMoreVideos()
    {
        $result = $this->repository->getMore();
        if ($result['error']) {
            return $this->getErrorJsonResponse([], trans('video::videos.fetch.error'));
        } else {
            return $this->getSuccessJsonResponse(['message' => trans('video::videos.fetch.success'), 'response' => $result['data']]);
        }
    }

    /**
     * Function to clear the view
     * @return json
     */
    public function clearView()
    {
        $result = $this->repository->clearVideoView();
        if ($result['error']) {
            return $this->getErrorJsonResponse([], trans('video::videos.fetch.error'));
        } else {
            return $this->getSuccessJsonResponse(['message' => trans('video::videos.delete_history')]);
        }
    }

    /**
     * Function to fetch homepage banner
     * @return json
     */
    public function fetchHomePageBanner()
    {
        $fetch['banner'] = $this->repository->getVideoBlockByType('banner');
        $fetch['new'] = $this->repository->getVideoBlockByType('new');

        if ($fetch) {
            return $this->getSuccessJsonResponse(['message' => trans('video::videos.fetch.success'), 'response' => $fetch]);
        } else {
            return $this->getErrorJsonResponse([], trans('video::videos.fetch.error'));
        }
    }

    public function fetchContinueWatchingVideos()
    {
        //\Log::info("working");
        $response = $this->repository->fetchContinueWatchingVideos();

        if ($response) {
            return $this->getSuccessJsonResponse(['message' => trans('video::videos.fetch.success'), 'response' => $response]);
        } else {
            return $this->getErrorJsonResponse([], trans('video::videos.fetch.error'));
        }
    }

    /**
     * Function to send video details with, related videos, search tag, subscription details, comments
     *
     * @return json
     */
    public function browseSeasonVideo($slug, $season = '')
    {
        $fetch['season_list'] = $this->repository->getSeasonVideoSlug($slug, $season);
        if (array_filter($fetch)) {
            return $this->getSuccessJsonResponse(['message' => trans('video::videos.fetch.success'), 'response' => $fetch]);
        } else {
            return $this->getErrorJsonResponse([], trans('video::videos.fetch.error'));
        }
    }
     /**
     * Method to fetch trailer data of a video
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchTrailerData($slug = '')
    {
        $result = $this->repository->getWatchVideoSlug($slug, 'trailer');
        return ($result) ? $this->getSuccessJsonResponse(['message' => trans('video::videos.fetch.success'), 'response' => $result])
        : $this->getErrorJsonResponse([], trans('video::videos.fetch.error'));
    }
    /**
     * Get current user details
     *
     * @return json
     */

    public function getCurrentUserInfo()
    {
        try{
            if(authUser()) {
                $result['user_id'] = authUser()->id;
                $result['email'] = authUser()->email;
                $result['name'] = authUser()->name;
            } else {
                $result['user_id'] = '';
                $result['email'] = '';
            }
            return $this->getSuccessJsonResponse(['message' => trans('video::videos.fetch.success'),'response' => $result]);
        } catch(Exception $ex) {
            return $this->getErrorJsonResponse([], trans('video::videos.fetch.error'));
        }
    }

    /**
     * Function to get Watermark opacity
     *
     * @return json
     */
    public function getwatermarkOpacity(){
        $globalVideoCount = Setting::where('setting_name', '=', 'watermark_opacity')->first(); 
        return $this->getSuccessJsonResponse(
            ['opacity' => $globalVideoCount->setting_value]); 
    }

    /**
     * Function to get DRM Token
     *
     * @return json
     */
    public function getdrmtoken(){
        $key = "-----BEGIN EC PRIVATE KEY-----
MHcCAQEEIH7VC5LT0f68aeQf9PcpmDZn9P5SQhyfhdl62yvbvt3goAoGCCqGSM49
AwEHoUQDQgAE6iHYH+go+hOPVf0tj7QovURpzsa2EFo63E+40+LdUP8Yg7ezShj6
3l5XC+QDEAD9wEskAHOEhIYu2oaab48T0Q==
-----END EC PRIVATE KEY-----";
                    $expire = date("F j, Y, H:i", time()+1800);
                    $expire = strtotime($expire);
                   // \Log::info($expire);
                    //\Log::info("");
                    $payload = array(
                        "ver" => "1.1",
                        "iss" => "test",
                        "sub" => "testcontent",
                        "aud" => "urn:verimatrix:multidrm",
                         "nbf" => time(),
                        "iat" => time(),
                        "exp" => $expire
                    );
                    $jwt = JWT::encode($payload, $key, 'ES256', '5d507ea2-a815-44b6-923c-e355b38d257f' );
                   // \Log::info(print_r($jwt,true));
        return $this->getSuccessJsonResponse(
            ['drm_token' => $jwt]); 
    }


}
