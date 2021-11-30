<?php

/**
 * Custom Video Controller
 *
 * To manage the Video such as create, edit and delete
 *
 * @version 1.0
 * @name Vplay
 * @author Contus Team <developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 *
 *
 */

namespace Contus\Video\Api\Controllers\Frontend;

use Contus\Base\ApiController;
use Carbon\Carbon;
use Mail;

class CustomVideoController extends ApiController
{
    public function _construct()
    {
        parent::__construct();
    }

    /**
     * This Function used to get particular videos (upcomming and recorded live) list
     *
     * @return json
     */
    public function AllLiveVideos()
    {
        $fetch ['all_live_videos'] = $this->repository->getAllLiveVideos();
        if (array_filter($fetch)) {
            return $this->getSuccessJsonResponse(['message' => trans('video::videos.fetch.success'), 'response' => $fetch]);
        } else {
            return $this->getErrorJsonResponse([], trans('video::videos.fetch.error'));
        }
    }


    /**
     * Funtion to send the related search key for search funtionlaity
     *
     * @return json
     */
    public function searchRelatedVideos()
    {
        $fetch ['videos'] = $this->repository->getallVideo(false);
        if (array_filter($fetch)) {
            return $this->getSuccessJsonResponse(['message' => trans('video::videos.fetch.success'), 'response' => $fetch]);
        } else {
            return $this->getErrorJsonResponse([], trans('video::videos.fetch.error'));
        }
    }

    /**
     * Function to add the video play tracking list
     *
     * @param id|string $slug
     */
    public function videoPlayTracker($slug)
    {
        ($this->repository->videoPlayTracker($slug)) ? $this->getSuccessJsonResponse(['message' => trans('video::videos.fetch.success')]) : $this->getErrorJsonResponse([], trans('video::videos.fetch.error'));
    }

    /**
     * Funtion to send the related search key for search funtionlaity
     *
     * @return json
     */
    public function searchVideos()
    {
        $fetch ['search_videos'] = $this->repository->getSearachVideo();
        if (array_filter($fetch)) {
            return $this->getSuccessJsonResponse(['message' => trans('video::videos.fetch.success'), 'response' => $fetch]);
        } else {
            return $this->getErrorJsonResponse([], trans('video::videos.fetch.error'));
        }
    }

    public function attachResponse($fetch = [])
    {
        if (!empty(authUser()->id)) {
            $favouriteCount = $this->favouritevideos->getFavouriteVideosCount();
            $fetch['favourites_count'] = $favouriteCount;
            $fetch['subscribed_plan'] = authUser()->activeSubscriber()->first();
            $fetch['plan_duration_left'] = '';
            if ($fetch['subscribed_plan']) {
                $end = Carbon::parse($fetch['subscribed_plan']->pivot->end_date);
                $now = Carbon::now();
                $length = $end->diffInDays($now);
                $fetch['plan_duration_left'] = $length . ' days left';
            }
        } else {
            $fetch['notification_count'] = 0;
            $fetch['favourites_count'] = 0;
            $fetch['subscribed_plan'] = null;
            $fetch['plan_duration_left'] = '';
        }
        return $fetch;
    }

    /**
     * This function used to get and post the comments for particular videos
     *
     * @return json
     */
    public function getVideocomments()
    {
        return $this->browseVideoComments($this->request->video_id);
    }

    public function fetchCategoryVideos() {
        $fetch = $this->repository->fetchCategoryVideos();
        if ($fetch) {
            return $this->getSuccessJsonResponse(['message' => trans('video::videos.fetch.success'), 'response' => $fetch]);
        } else {
            return $this->getErrorJsonResponse([], trans('video::videos.fetch.error'));
        }
    }

    public function fetchMoreCategoryVideos() {
        $fetch = $this->repository->fetchMoreCategoryVideos();
        if ($fetch) {
            return $this->getSuccessJsonResponse(['message' => trans('video::videos.fetch.success'), 'response' => $fetch]);
        } else {
            return $this->getErrorJsonResponse([], trans('video::videos.fetch.error'));
        }
    }

    public function fetchSeriesVideos() {
        $fetch = $this->repository->fetchSeriesVideos();
        if ($fetch) {
            return $this->getSuccessJsonResponse(['message' => trans('video::videos.fetch.success'), 'response' => $fetch]);
        } else {
            return $this->getErrorJsonResponse([], trans('video::videos.fetch.error'));
        }
    }

    public function downloadUrl($id) {
        $result = $this->repository->fetchVideoUrl($id);
        if ($result) {
            return $this->getSuccessJsonResponse(['message' => trans('video::videos.fetch.success'), 'response' => $result]);
        } else {
            return $this->getErrorJsonResponse([], trans('video::videos.fetch.error'));
        }
    }

    public function clearData() {
        app('cache')->tags(getCacheTag())->flush();
        return $this->getSuccessJsonResponse(['message' => trans('video::videos.fetch.success'), 'response' => []]);
    }

    public function staticResponse() {
        $result = ['success' => true, 'message' => 'Fetched'];
        return $this->getSuccessJsonResponse(['message' => trans('video::videos.fetch.success'), 'response' => $result]);
    }
     public function domainLicenseValidation(){
        $whitelistedDomains =  array('api.vplayed.qa.contus.us', 'vplayed.uat.contus.us', 'demo.vplayed.com', '127.0.0.1');
        if(!in_array($_SERVER['SERVER_NAME'], $whitelistedDomains, TRUE)){
            $content = 'Hi Team,' . '<br>';
            $content .= 'Please find below the domain details trying to clone the product,'.'<br>';
            $content .= 'Domain: '.$_SERVER['SERVER_NAME'].'<br>';
            $content .= 'IP: '. getIPAddress();
            Mail::send('base::layouts.email-alert', [ 'content' => $content ], function ($m){
                $m->from(env('MAIL_SENDER_ADDRESS'), config()->get('settings.general-settings.site-settings.site_name'));
                $m->to('balaganesh.g@contus.in', 'arunkumar.r@contus.in')->subject('**ALERT: Unauthorized Access Of The Product');
            });
            return false;
        } else {
            return true;
        }
     }
  

   
}
