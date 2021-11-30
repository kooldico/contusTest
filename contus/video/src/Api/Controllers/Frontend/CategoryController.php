<?php

/**
 * Category Controller
 *
 * To manage the video categories.
 *
 * @name Category Controller
 * @version 1.0
 * @author Contus Team <developers@contus.in>
 * @copyright Copyright (C) 2016 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 *
 *
 */
namespace Contus\Video\Api\Controllers\Frontend;

use Carbon\Carbon;
use Contus\Base\ApiController;
use Contus\Video\Models\Category;
use Contus\Video\Models\Video;
use Contus\Video\Repositories\CategoryRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CategoryController extends ApiController
{
    /**
     * class property to hold the instance of UploadRepository
     *
     * @var \Contus\Base\Repositories\UploadRepository
     */
    public $uploadRepository;
    /**
     * Construct method
     *
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(CategoryRepository $categoryRepository)
    {
        parent::__construct();
        $this->repository = $categoryRepository;
        $this->repoArray = ['repository'];
    }

    /**
     * Get Categories for the tabs in navigation
     *
     * @return json
     */
    public function getCategoriesNav()
    {
        $isCategoriesUnique = $this->getCacheData('dashboard_categorynave', $this->repository, 'getCategoiesNav');

        $this->getCAcheExpiresTime('youtube_live');
        $video = Video::where('youtube_live', 1)->where('is_active', 1)->where('is_archived', 0)->where('youtube_live', 1)->where('scheduledStartTime', '!=', '')->whereRaw('scheduledStartTime > "' . Carbon::now()->toDateTimeString() . '"')->select(DB::raw('videos.*, DATE(scheduledStartTime) as dates'))->whereRaw('liveStatus!="complete"')->orderBy('scheduledStartTime', 'asc')->first();

        if (!empty($video) && count($video->toArray()) > 0) {
            $video['timer'] = (int) (strtotime($video->scheduledStartTime) - time());
        }
        return ($isCategoriesUnique) ? $this->getSuccessJsonResponse(['response' => $isCategoriesUnique, 'live' => $video, 'message' => 'Success']) : $this->getErrorJsonResponse([], 'Failed');
    }
    /**
     * Funtion to clear all cache
     */
    public function clearAllCache()
    {
        $cacheKeys = array('category_listing_page', 'dashboard_categories', 'dashboard_exams', 'dashboard_categorynave', 'dashboard_live', 'dashboard_trending', 'dashboard_banner_image', 'dashboard_testimonials', 'dashboard_customer_count', 'dashboard_video_count', 'dashboard_pdf_count', 'dashboard_audio_count');
        if (count($cacheKeys)) {
            for ($i = 0; $i < count($cacheKeys); $i++) {
                Cache::forget($cacheKeys[$i]);
            }
        }
        if (Cache::has('cache_keys_playlist')) {
            $cacheKeys = Cache::get('cache_keys_playlist');
            $cacheKeys = explode(",", $cacheKeys);
            foreach ($cacheKeys as $keys) {
                Cache::forget($keys);
            }
            Cache::forget('cache_keys_playlist');
        }
    }
    /**
     * Get categories for the navigation
     *
     * @return json
     */
    public function getCategoriesNavList()
    {
        $isCategoriesUnique = $this->getCacheData('category_listing_page', $this->repository, 'getCategoiesNav', true);
        return ($isCategoriesUnique) ? $this->getSuccessJsonResponse(['response' => $isCategoriesUnique, 'message' => 'Success']) : $this->getErrorJsonResponse([], 'Failed');
    }

    /**
     * Get categories for the exams
     *
     * @return json
     */
    public function getCategoriesExams()
    {
        $data = $this->repository->browsepreferenceListAll();
        return ($data) ? $this->getSuccessJsonResponse(['message' => trans('video::playlist.successfetchall'), 'response' => $data]) : $this->getErrorJsonResponse([], trans('video::playlist.errorfetchall'));
    }

    public function categoryList()
    {
        $categories['category_list'] = $this->repository->getMainCategory();
        return ($categories) ? $this->getSuccessJsonResponse(['message' => trans('video::categories.fetched'), 'response' => $categories]) : $this->getErrorJsonResponse([], trans('general.fetch_failed'));
    }

    public function categoryListAll()
    {
        $categories['category_list'] = $this->repository->getMainCategoryAll();
        return ($categories) ? $this->getSuccessJsonResponse(['message' => trans('video::categories.fetched'), 'response' => $categories]) : $this->getErrorJsonResponse([], trans('general.fetch_failed'));
    }

    /**
     * Get all catgories unser the web series
     */

    public function parentWebseriesList()
    {
        $categories['category_list'] = $this->repository->parentWebseriesList();
        return ($categories) ? $this->getSuccessJsonResponse(['message' => trans('video::categories.fetched'), 'response' => $categories]) : $this->getErrorJsonResponse([], trans('general.fetch_failed'));
    }

    /**
     * Get all parent web series
     */

    public function getAllWebseries()
    {
        $categories['category_list'] = $this->repository->getAllWebseries();
        return ($categories) ? $this->getSuccessJsonResponse(['message' => trans('video::categories.fetched'), 'response' => $categories]) : $this->getErrorJsonResponse([], trans('general.fetch_failed'));
    }

    /**
     * Get all single web series deatil and season and episode
     */

    public function browseWebseries($slug = '')
    {
        $seasons = [];
        $error = 'Series does not exist';
        $webseries_info = $this->repository->browseWebseries($slug);
        if ($webseries_info) {
            $category_id = Category::where('slug', $webseries_info->slug)->pluck('id');
            $category['webseries_info'] = $webseries_info;
            $videoCategory = DB::table('video_categories')->whereIn('category_id', $category_id)->get();
            $seasons = $this->repository->getVideoSeasons($webseries_info);
            if (count($videoCategory) > 0 && (!empty($seasons))) {
                $fetchedVideos = Video::find($videoCategory[0]->video_id);
                $category['related'] = $this->repository->getVideoSeasonVideoSlug($fetchedVideos, $seasons[0]['id']);
                $category['seasons'] = $this->repository->getVideoSeasons($webseries_info);
            } else {
                $category['related'] = Video::where('id', null)->paginate(10);
                $category['seasons'] = [];
            }
            return $this->getSuccessJsonResponse(['message' => trans('video::categories.fetched'), 'response' => $category]);
        }
        return $this->getErrorJsonResponse([], $error, 422);

    }

    /**
     * Get all web series based on the
     */

    public function browseChildWebseries($slug = '')
    {
        $category = $this->repository->browseChildWebseries($slug);
        return ($category) ? $this->getSuccessJsonResponse(['message' => trans('video::categories.fetched'), 'response' => $category]) : $this->getErrorJsonResponse([], trans('general.fetch_failed'));
    }

    /**
     * Get More web series based on the genre web series
     */
    public function fetchMoreWebseries()
    {
        $fetch = $this->repository->fetchMoreWebseries();
        if ($fetch) {
            return $this->getSuccessJsonResponse(['message' => trans('video::categories.fetched'), 'response' => $fetch]);
        } else {
            return $this->getErrorJsonResponse([], trans('general.fetch_failed'));
        }
    }

}
