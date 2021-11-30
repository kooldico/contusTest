<?php

namespace Contus\Video\Api\Controllers\Frontend;

use Contus\Video\Models\CustomerDeviceDetails;
use Contus\Video\Models\Subscribers;
use Contus\Video\Models\TranscodedVideo;
use Contus\Video\Models\VideoPreset;
use Contus\Video\Repositories\AWSUploadRepository;
use Contus\Video\Repositories\FrontVideoRepository;

class VideoDRMController extends VideoValidation
{
    public function __construct(FrontVideoRepository $videosRepository)
    {
        $this->awsRepository = new AWSUploadRepository(new TranscodedVideo(), new VideoPreset());
        $this->repository = $videosRepository;
    }
    /**
     * Function to send video details
     *
     * @return json
     */
    public function browseWatchVideo($slug = '')
    {

        if (!$this->domainLicenseValidation()) {
            return $this->getErrorJsonResponse([], 'License key has expired', 200);
        }

        $fetchedVideos = $this->repository->getWatchVideoSlug($slug);

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
                    return $this->getErrorJsonResponse(['response' => $fetchedVideos], trans('video::videos.video_unauthorized_access'), 403);
                }
            }
        }

        if ($fetchedVideos['status'] == 'authorized') {
            $fetch['videos'] = (isset($fetchedVideos['data']->is_live) && $fetchedVideos['data']->is_live !== 0)
            ? $fetchedVideos['data']->makeHidden(['video_url', 'youtube_id', 'liveStatus'])
            : $fetchedVideos['data'];
            $fetch['payment_info'] = $this->repository->getVideoPaymentInfo($fetch['videos']->id);
            $fetch['videos']->device_restriction_type = (!empty(config()->get('settings.site-global-settings.site-global-module-settings.screen_restriction'))) ? "player" : "login";
            $fetch['videos']->seconds = $this->repository->getWatchHistory($slug);
            $fetch['video_meta_data'] = $this->repository->getVideoMetaData($fetch['videos']->id);
            return array_filter($fetch) ? $this->getSuccessJsonResponse(['message' => trans('video::videos.fetch.success'), 'response' => $fetch])
            : $this->getErrorJsonResponse([], trans('video::videos.fetch.error'));
        } else {
            return $this->getErrorJsonResponse(['response' => $fetchedVideos], trans('video::videos.video_unauthorized_access'));
        }
    }

    /**
     * Function to validate the key request
     * @return string
     */
    public function getKey()
    {
        
        if (!$this->domainLicenseValidation()) {
            return $this->getErrorJsonResponse([], 'License key has expired', 200);
        }
        $key = request()->input('key');
        \Log::info("key");
        \Log::info($key);
        $referer = request()->header('Referer');
        $Title = request()->header('Title');
        $errorMsg = '';
        $TitleWithTime = cryptoJsAesDecrypt($Title);
        \Log::info(print_r($TitleWithTime,true));
        $platform = getPlatform();
        $getTime = explode("/", $TitleWithTime);
        $timeKey = (isset($getTime[1])) ? $getTime[1] : 0;
        $differenceInSeconds = time() - $timeKey;
        if ($referer == env('DOWNLOAD_REFERER') || $platform == 'web') {
            if (strpos($key, 'FFMPEG') !== false) {
                $key = explode('/', $key);
                array_pop($key);
                $key = implode('/', $key) . '/enc.key';
            } else {
                $key = str_replace("m3u8", "key", $key);
            }
            $result = $this->awsRepository->fetchFileFromS3Bucket($key);
            $key = $this->fetchChunkData($platform, $differenceInSeconds, $result);
            if ($key == '') {
                $errorMsg = 'Unauthorized';
            } else {
                return response($key, 200);
            }
        } else {
            $errorMsg = 'Crossdomain access denied';
        }
        return $this->getErrorJsonResponse([], $errorMsg);
    }
    public function fetchChunkData($platform, $time, $result)
    {
        $key = '';
        $diffSec = 30;
        $userAgent = request()->header('User-Agent');

        $castDevice = request()->header('cast-device-capabilities');
        $cast = false;
        if (!empty($castDevice)) {
            $cast = true;
        }
        if ($result) {
            switch ($platform) {
                case $platform == 'web':
                    if (stripos($userAgent, 'iphone') || stripos($userAgent, 'ipad') || isAdmin() || $cast || (stripos($userAgent, 'Roku') !== false)) {
                        $key = $result['Body'];
                    } else {
                        $key = ($time <= $diffSec) ? cryptoJsAesEncrypt($result['Body']) : '';
                    }
                    break;
                case $platform == 'ios':
                    $key = ($time <= $diffSec) ? $result['Body'] : '';
                    break;
                case $platform == 'android':
                    $key = ($time <= $diffSec) ? cryptoJsAesEncrypt($result['Body']) : '';
                    break;
                default:
                    $key = '';
                    break;
            }
        }
        return $key;
    }
}
