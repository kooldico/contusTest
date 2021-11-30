<?php

/**
 * CategoryTrait
 *
 * To manage the functionalities related to the Categories module from Categories Controller
 *
 * @vendor Contus
 *
 * @package Categories
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Video\Traits;

use Carbon\Carbon;
use Contus\Base\Controller;
use Contus\Video\Models\Banner;
use Contus\Video\Models\Category;
use Contus\Video\Models\CollectionVideo;
use Contus\Video\Models\Group;
use Contus\Video\Models\Video;
use Contus\Video\Models\WatchHistory;
use Contus\Video\Repositories\FrontVideoRepository;
use Illuminate\Support\Facades\DB;

trait CollectionTrait
{

    /**
     * Repository function to delete custom thumbnail of a video.
     *
     * @param integer $id
     * The id of the video.
     * @return boolean True if the thumbnail is deleted and false if not.
     */
    public function deleteThumbnail($id)
    {
        $video = new video();
        /**
         * Check if video id exists.
         */
        if (!empty($id)) {
            $video = $video->findorfail($id);
            /**
             * Delete the thumbnail image using the thumbnail path field from the database.
             */
            $video->thumbnail_image = '';
            $video->thumbnail_path = '';
            $video->save();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Repository function to delete subtitle of a video.
     *
     * @param integer $id
     * The id of the video.
     * @return boolean True if the subtitle is deleted and false if not.
     */
    public function deleteSubtitle($id)
    {
        $video = new video();

        /**
         * Check if video id exists.
         */
        if (!empty($id)) {
            $video = $video->findorfail($id);
            /**
             * Delete the subtitle image using the subtitle path field from the database.
             */
            $video->mp3 = '';
            $video->subtitle_path = '';
            $video->save();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Funtion to send the related search key for search funtionlaity
     *
     * @return json
     */
    public function searchRelatedVideos()
    {
        $fetch['videos'] = FrontVideoRepository::getallVideo(false);
        if (array_filter($fetch)) {
            return Controller::getSuccessJsonResponse(['message' => trans('video::videos.fetch.success'), 'response' => $fetch]);
        } else {
            return Controller::getErrorJsonResponse([], trans('video::videos.fetch.error'));
        }
    }
    /**
     * Function to add the video play tracking list
     *
     * @param id|string $slug
     */
    public function videoPlayTracker($slug)
    {
        (FrontVideoRepository::videoPlayTracker($slug)) ? Controller::getSuccessJsonResponse(['message' => trans('video::videos.fetch.success')]) : Controller::getErrorJsonResponse([], trans('video::videos.fetch.error'));
    }

    /**
     * This function used to get the all the scheduled and recorded videos
     */
    public function AllLiveVideos()
    {
        $fetch['all_live_videos'] = FrontVideoRepository::getAllLiveVideos();
        if (array_filter($fetch)) {
            return Controller::getSuccessJsonResponse(['message' => trans('video::videos.fetch.success'), 'response' => $fetch]);
        } else {
            return Controller::getErrorJsonResponse([], trans('video::videos.fetch.error'));
        }
    }

    /**
     * Funtion to search Videos with respect to video title and description
     *
     * @return json
     */
    public function getSearachVideo()
    {

        $this->setRules(['search' => 'required', 'order' => 'sometimes|in:title', 'sort' => 'sometimes|in:asc,desc']);
        $this->validate($this->request, $this->getRules());

        $searchKey = $this->request->search;
        $video = $this->video->whereCustomer()->where(function ($query) use ($searchKey) {
            $query->orwhere('slug', 'like', '%' . $searchKey . '%')
                ->orwhere('title', 'like', '%' . $searchKey . '%');
        });

        $fields = 'videos.id, videos.title, videos.slug, videos.description, videos.thumbnail_image, videos.hls_playlist_url, videos.id as is_favourite, videos.id as collection, videos.trailer_hls_url, videos.trailer_status';

        $video->with(['categories'])->leftJoin('recently_viewed_videos as f1', function ($j) {
            $j->on('videos.id', '=', 'f1.video_id');
        })->selectRaw($fields)->where('is_live', '==', 0)->groupBy('videos.id');

        $inputArray = $this->request->all();
        if (isset($inputArray['order']) && !empty($inputArray['order'])) {
            $video->orderBy($inputArray['order'], $inputArray['sort']);
        } else {
            $video->orderBy('id', 'desc');
        }

        return $video->paginate(config('access.perpage'));
    }

    /**
     * Function to get the top nth Categories
     * @param  integer $limit - Get the offset of the category to be fetched
     * @return [object]  categoryObject
     */
    public function getTopNthCategory($limit = 0)
    {

        $catObj = new Category();
        return $catObj->where('parent_id', 0)->where('is_web_series', 0)->where('level', 0)->where('is_active', 1)->orderBy('category_order', 'asc')->skip($limit)->take(1)->first();

    }

    public function fetchRecentVideos($fields, $video)
    {
        $userId = (!empty(authUser()->id)) ? authUser()->id : 0;
        $videoInfo = $video->whereHas('recentlyWatched', function ($query) use ($userId) {
            $query->where('customer_id', $userId)->where('watch_history_is_active', 1)->orderBy('updated_at', 'desc');
        })->with(['categories'])->selectRaw($fields)->where('is_live', '==', 0)->groupBy('videos.id')->orderBy('updated_at', 'desc')->paginate(config('access.perpage'));

        return $videoInfo->toArray();

    }

    /**
     * Function to get all video to frontend with filters and search
     *
     * @vendor Contus
     *
     * @package video
     * @return array
     */
    public function searchAllVideo()
    {
        $result['error'] = false;
        $result['message'] = '';
        $inputArray = $this->request->all();

        $this->setRules(['order' => 'sometimes|in:title', 'sort' => 'sometimes|in:asc,desc']);
        $this->validate($this->request, $this->getRules());

        $fields = 'videos.id, videos.title, videos.slug, videos.description, videos.thumbnail_image, videos.hls_playlist_url, videos.video_duration, videos.id as is_favourite, videos.id as collection, videos.poster_image,
        videos.trailer_hls_url,videos.trailer_status';

        $this->video = $this->video->whereCustomer()->where('is_live', '!=', 1)->has('categories')->with('categories');

        $this->video = $this->constructSearchQuery($this->video);

        $video = $this->video->leftJoin('favourite_videos as f1', function ($j) {
            $j->on('videos.id', '=', 'f1.video_id')->on('f1.customer_id', '=', DB::raw(!empty(authUser()->id) ? authUser()->id : 0));
        })->selectRaw($fields)->groupBy('videos.id');

        if ($this->request->has('video_id')) {
            $video = $video->where('videos.id', '!=', $this->request->video_id);
        }

        $video = $video->paginate(9)->toArray();

        $paramArray = array_filter($inputArray);
        if ((!isset($inputArray['page']) || $inputArray['page'] <= 1) && (!isset($paramArray['category'])) && (!isset($paramArray['genre']))) {
            $genreInfo = $this->fetchPopularGenre(false);
            unset($genreInfo['category_name']);
            $final['genres'] = $genreInfo;
            $final['categories'] = $this->getChildrenCategory();
        }

        $final['videos'] = $video;
        $result['data'] = $final;
        return $result;
    }

    /**
     * Function to fetch child categories for the given main category
     * @return Object- Return child category object
     */
    public function getChildrenCategory()
    {
        $category = Category::With(['child_category' => function ($query) {
            $query->selectRaw('*, id as video_count');
        }])->where($this->getKeySlugorId(), $this->request->main_category);
        return $category->first();
    }

    /**
     * Function to construct search query based on the requested params
     * @param  Object $videoObj Video Object
     * @return Object Video Object
     */
    public function constructSearchQuery($videoObj)
    {
        $inputArray = $this->request->all();

        if (!empty($inputArray)) {
            foreach ($inputArray as $inputKey => $inputValue) {
                if ($inputValue != '') {
                    switch ($inputKey) {
                        case 'search':
                            $videoObj = $videoObj->where('title', 'like', '%' . $this->request->search . '%');
                            break;
                        case 'main_category':
                            $videoObj = $videoObj->whereHas('categories.parent_category', function ($q) {
                                $q->where($this->getKeySlugorId(), $this->request->main_category);
                            });
                            break;
                        case 'category':
                            $categoryArray = explode(',', $this->request->category);
                            $videoObj = $videoObj->whereHas('categories', function ($q) use ($categoryArray) {
                                $q->whereIn('categories.' . $this->getKeySlugorId(), $categoryArray);
                            });
                            break;
                        case 'genre':
                            $genreArray = explode(',', $this->request->genre);
                            $videoObj = $videoObj->whereHas('collections', function ($q) use ($genreArray) {
                                $q->whereIn('groups.' . $this->getKeySlugorId(), $genreArray);
                            });
                            break;
                        default:
                            break;
                    }
                }
            }

            if (isset($inputArray['order']) && !empty($inputArray['order'])) {
                $videoObj = $videoObj->orderBy($inputArray['order'], $inputArray['sort']);
            } else {
                $videoObj = $videoObj->orderBy('video_order', 'desc');
            }
        }
        return $videoObj;
    }

    /**
     * Function to clear the video view history
     * @return Array
     */
    public function clearVideoView()
    {
        $result['error'] = false;
        $result['message'] = '';
        $videoIds = [];
        $videoIds = $this->fetchVideoIds();
        try {
            if (!empty($videoIds)) {
                WatchHistory::whereIn('video_id', $videoIds)->where('customer_id', (!empty(authUser()->id)) ? authUser()->id : 0)->update(['watch_history_is_active' => 0]);
            } else {
                WatchHistory::where('customer_id', (!empty(authUser()->id)) ? authUser()->id : 0)->update(['watch_history_is_active' => 0]);
            }
        } catch (\Exception $e) {
            $result['error'] = true;
            $result['message'] = trans('video::videos.fetch.error');
        }

    }

    /**
     * Function to fetch video ids for the given slug
     * @param  string $slug - video slug
     * @return Array - Video id Array
     */
    public function fetchVideoIds()
    {
        $videoIds = [];
        if ($this->request->has('video_id') && !empty($this->request->video_id)) {
            $videoIds = explode(',', $this->request->video_id);
        }
        if (!isMobile()) {
            $videoIds = Video::whereIn('slug', $videoIds)->pluck('id')->toArray();
        } else {
            if ($this->request->has('video_id') && !empty($this->request->video_id)) {
                $videoIds = array_map('intval', $videoIds);
            }
        }
        return $videoIds;
    }

    public function fetchLiveVideos()
    {

        try {
            $result['error'] = false;
            $result['message'] = '';
            $result['data'] = '';

            $fields = 'videos.id, videos.title, videos.slug, videos.description, videos.thumbnail_image, videos.hls_playlist_url, videos.id as is_favourite, videos.id as collection, videos.poster_image,videos.is_live, videos.scheduledStartTime,videos.is_premium';

            $videos = $this->video->whereliveVideos()->whereRaw('scheduledStartTime < "' . Carbon::now()->now() . '" ')->orderBy('id', 'desc')->with(['categories.parent_category'])->selectRaw($fields)->get();

            $videoObj = new Video();
            $todayLive = $videoObj->whereliveVideos()->whereRaw('scheduledStartTime > "' . Carbon::now()->now() . '" ')->whereRaw('scheduledStartTime < "' . Carbon::now()->toDateString() . ' 23:59:59 "')->orderBy('scheduledStartTime', 'asc')->with(['categories.parent_category'])->selectRaw($fields)->get();

            $upcomingLive = $this->fetchMoreLiveVideos();
            $videoInfo['banner'] = $this->getLiveBanner();
            $videoInfo['current_live_videos'] = $videos->toArray();
            $videoInfo['today_live_videos'] = $todayLive->toArray();
            $videoInfo['upcoming_live_videos'] = (!empty($upcomingLive['data'])) ? $upcomingLive['data']->toArray() : [];
            $result['data'] = $videoInfo;
        } catch (\Exception $e) {
            $result['error'] = true;
            $result['message'] = $e->getMessage();
            $result['data'] = '';
        }
        return $result;
    }

    public function getLiveBanner()
    {
        return Banner::selectRaw('id, id as banner_url, banner_image')->where('is_live', 1)->where('is_active', 1)->orderBy('id', 'desc')
            ->paginate(10);
    }

    public function fetchMoreLiveVideos()
    {

        $fields = 'videos.id, videos.title, videos.slug, videos.description, videos.thumbnail_image, videos.hls_playlist_url, videos.id as is_favourite, videos.id as collection, videos.poster_image,videos.is_live, videos.scheduledStartTime';

        try {
            $result['error'] = false;
            $result['message'] = '';
            $result['data'] = $this->video->whereliveVideos()->whereRaw('scheduledStartTime > "' . Carbon::now()->now() . '" ')->with(['categories.parent_category'])->selectRaw($fields)->orderBy('scheduledStartTime', 'asc')->paginate(config('access.perpage'));
        } catch (\Exception $e) {
            $result['error'] = true;
            $result['message'] = $e->getMessage();
        }
        return $result;

    }

    public function fetchBannerVideos($fields, $video)
    {

        $bannerArray = [];

        $bannerInfo = Banner::selectRaw('id, id as banner_url, banner_image, video_id')->where('is_active', 1)->get();
        if (!empty($bannerInfo)) {
            foreach ($bannerInfo as $bKey => $bImages) {
                $bKey;
                $bannerArray[$bImages->video_id] = $bImages->toArray();
            }
        }

        $videoKeys = array_keys($bannerArray);
        $video = $video->with(['categories'])->selectRaw($fields)->whereIn('videos.id', $videoKeys)->groupBy('videos.id')->orderBy('video_order', 'asc')->orderBy('id', 'desc')->paginate(5);

        if (empty($video)) {
            return $video->toArray();
        }

        if (!isMobile()) {
            $videoCollection = $video->getCollection();
            $video->makeVisible('id');
            $video->setCollection($videoCollection);
            $video = $video->toArray();
            foreach ($video['data'] as $key => $value) {
                $video['data'][$key]['poster_image'] = $bannerArray[$value['id']]['banner_url'];
            }
        } else {
            $video = $video->toArray();
            foreach ($video['data'] as $key => $value) {
                $video['data'][$key]['banner_image'] = $bannerArray[$value['id']]['banner_url'];
            }
        }

        return $video;
    }

    public function fetchCategoryVideos()
    {
        $this->setRules(['category' => 'required', 'section' => 'sometimes|']);
        $this->validate($this->request, $this->getRules());

        $inputArray = $this->request->all();

        $section = (!empty($inputArray['section'])) ? $inputArray['section'] : 1;

        $categoryArray = [];
        $videoInfo['main'] = [];
        $videoInfo['category_videos'] = [];
        $videoInfo['genre_videos'] = [];

        $fields = 'videos.id, videos.title, videos.slug, videos.thumbnail_image, videos.poster_image, videos.is_live, videos.is_premium, videos.price,videos.trailer_hls_url,videos.trailer_status,videos.trailer_status';

        $categoryInfo = $this->fetchChildrens(false);
        $categoryArray = $this->fetchChildrens(true, $categoryInfo);

        if ($section == 1) {
            $video = $this->video->whereCustomer();
            $new = $this->fetchNewVideos($fields, $video, $categoryArray);
            $new = $this->formatCatVideos('new', $new, $categoryInfo);

            $video = $this->video->whereCustomer();
            $popular = $this->fetchPopularVideos($video, $categoryArray, $fields);
            $popular = $this->formatCatVideos('popular', $popular, $categoryInfo);

            $videoInfo['main'][] = $new;
            $videoInfo['main'][] = $popular;
        } else {
            $genreArray = $this->fetchGenre($categoryInfo);
            $videoInfo['genre_videos'] = $this->fetchGenreVideos($genreArray);
            if (!empty($categoryInfo) && !$categoryInfo->is_web_series) {
                $videoInfo['category_videos'] = $this->fetchSubCategoryVideos($categoryArray);
            }
        }

        $videoInfo['web_series'] = (!empty($categoryInfo) && !$categoryInfo->is_web_series) ? 0 : 1;
        return $videoInfo;
    }

    public function fetchGenre($category)
    {
        $infoArray['video_info'] = CollectionVideo::where('parent_cateogry_id', $category['id'])->pluck('video_id')->toArray();
        $infoArray['group_info'] = CollectionVideo::where('parent_cateogry_id', $category['id'])->pluck('group_id')->toArray();

        $this->request->request->add(['fetched_category_ids' => $infoArray['video_info']]);

        return $infoArray['group_info'];

    }

    public function getTrendingVideos($categoryArray)
    {
        $inputArray = $this->request->all();
        if (isset($inputArray['order']) && !empty($inputArray['order'])) {
            $sort = (!empty($inputArray['sort'])) ? $inputArray['sort'] : 'asc';
            $order = $inputArray['order'];
        }

        $fields = 'videos.id, videos.title, videos.slug, videos.description, videos.thumbnail_image,  videos.hls_playlist_url, videos.id as is_favourite, videos.id as collection, videos.poster_image,videos.is_live,videos.view_count,videos.is_premium, videos.trailer_hls_url,videos.trailer_status';

        $video = $this->video->whereCustomer();

        $perPage = config('access.perpage');
        $order = (!empty($order)) ? $order : 'count';
        $sort = (!empty($sort)) ? $sort : 'desc';
        $video = $video->with(['categories'])->join('recently_viewed_videos', 'videos.id', '=', 'recently_viewed_videos.video_id')->where('recently_viewed_videos.created_at', '>', Carbon::now()->subDays(30))->selectRaw($fields)->where('is_live', '==', 0)->where('is_active', '==', 1);

        if (!empty($categoryArray)) {
            $video->whereHas('categories', function ($query) use ($categoryArray) {
                $query->whereIn('categories.id', $categoryArray);
            });
        }

        $video = $video->groupBy('recently_viewed_videos.video_id')->orderBy($order, $sort)->paginate($perPage);
        return $video->toArray();
    }

    public function fetchChildrens($children = true, $categoryInfo = [])
    {
        $inputArray = $this->request->all();

        $categoryArray = [];

        if (!empty($categoryInfo) && $children) {
            return $categoryInfo->child_category->pluck('id');
        }

        if (!empty($inputArray['category'])) {
            $categoryInfo = Category::where($this->getKeySlugorId(), $inputArray['category'])->first();
            if (!empty($categoryInfo)) {
                $categoryInfo = $categoryInfo->makeVisible(['id']);
            }
            if (!empty($categoryInfo) && $children) {
                $categoryArray = $categoryInfo->child_category->pluck('id');
            }
        }
        return ($children) ? $categoryArray : $categoryInfo;
    }

    public function fetchSubCategoryVideos($categoryArray)
    {
        return Category::selectRaw('*, id as video_list')->whereIn('id', $categoryArray)->orderBy('category_order', 'asc')->orderBy('title', 'asc')->get()->toArray();

    }

    public function fetchGenreVideos($genreArray)
    {
        return Group::selectRaw('*, id as video_list')->whereIn('id', $genreArray)->orderBy('order', 'asc')->orderBy('name', 'asc')->get()->toArray();

    }

    public function fetchMoreCategoryVideos()
    {
        $result = [];
        $this->setRules([
            'type' => 'required|in:trending,popular,category,genre,new',
            'category' => 'required_if:type,in:category,trending',
            'genre' => 'required_if:type,genre',
        ]);
        $this->validate($this->request, $this->getRules());

        $inputArray = $this->request->all();
        $type = $inputArray['type'];

        switch ($type) {
            case $type == 'category':
                $categoryInfo = $this->fetchChildrens(false);
                if (!empty($categoryInfo)) {
                    $result = $this->fetchSubCategoryVideos([$categoryInfo['id']]);
                    // To convert Array into single object in response format
                    $result = !empty($result[0]) ? $result[0] : [];
                }
                break;
            case $type == 'genre':
                $categoryInfo = $this->fetchChildrens(false);
                $this->fetchGenre($categoryInfo);
                $genreInfo = Group::where($this->getKeySlugorId(), $inputArray['genre'])->first();
                if (!empty($genreInfo)) {
                    $result = $this->fetchGenreVideos([$genreInfo['id']]);
                    // To convert Array into single object in response format
                    $result = !empty($result[0]) ? $result[0] : [];
                }
                break;
            case $type == 'trending':
                $categoryArray = $this->fetchChildrens();
                $result = $this->getTrendingVideos($categoryArray);
                $result = $this->formatCatVideos('trending', $result);
                break;
            case $type == 'popular':
                $video = $this->video->whereCustomer();
                $categoryArray = $this->fetchChildrens();
                $result = $this->fetchPopularVideos($video, $categoryArray);
                $result = $this->formatCatVideos('popular', $result);
                break;
            default:
                $video = $this->video->whereCustomer();
                $fields = 'videos.id, videos.title, videos.slug, videos.description, videos.thumbnail_image, videos.hls_playlist_url, videos.id as is_favourite, videos.poster_image,videos.is_live,videos.view_count,videos.is_premium,videos.trailer_hls_url, videos.trailer_status';
                $categoryArray = $this->fetchChildrens();
                $video = $this->fetchNewVideos($fields, $video, $categoryArray);
                $video = $video->toArray();
                $result = $this->formatCatVideos('new', $video);
                $video['category_name'] = trans('general.new_videos');
                break;
        }

        $videoInfo['more_category_videos'] = $result;
        return $videoInfo;

    }

    public function formatCatVideos($type, $result, $categoryInfo = [])
    {
        $final['video_list'] = $result;

        $title = trans('video::videos.new');
        if ($type == 'trending') {
            $title = trans('video::videos.trending');
        } else if ($type == 'popular') {
            $title = trans('video::videos.popular');
        }

        if (!empty($categoryInfo)) {
            $title .= ($type == 'new') ? ' ' . trans('video::videos.on') . ' ' . $categoryInfo->title : ' ' . trans('video::videos.in') . ' ' . $categoryInfo->title;
        }

        $final['title'] = $title;
        $final['type'] = $type;

        if ($this->request->has('category')) {
            $final['id'] = (isMobile() ? (int) $this->request->category : $this->request->category);
        } else {
            $final['id'] = '';
        }
        return $final;
    }

    public function fetchSeriesVideos()
    {
        $this->setRules(['category' => 'required']);
        $this->validate($this->request, $this->getRules());

        $categoryArray = [];
        $video = $this->video->whereCustomer();

        $fields = 'videos.id, videos.title, videos.slug, videos.description, videos.thumbnail_image, videos.hls_playlist_url, videos.id as is_favourite, videos.id as collection, videos.poster_image,videos.is_live,videos.view_count, videos.is_premium, videos.trailer_hls_url,videos.trailer_status';

        $categoryInfo = $this->fetchChildrens(false);
        $categoryArray = $this->fetchChildrens(true, $categoryInfo);
        $genreArray = $this->fetchGenre($categoryInfo);

        $new = $this->fetchNewVideos($fields, $video, $categoryArray);
        $new = $this->formatCatVideos('new', $new);

        $popular = $this->fetchPopularVideos($video, $categoryArray);
        $popular = $this->formatCatVideos('popular', $popular);

        $videoInfo['main'] = [];
        $videoInfo['main'][] = $popular;
        $videoInfo['main'][] = $new;

        $videoInfo['genre_videos'] = $this->fetchGenreVideos($genreArray);
        if (!$categoryInfo->is_web_series) {
            $videoInfo['category_videos'] = $this->fetchSubCategoryVideos($categoryArray);
            $videoInfo['web_series'] = 0;
        } else {
            $videoInfo['web_series'] = 1;
            $videoInfo['category_videos'] = [];
        }

        return $videoInfo;
    }

    public function fetchRecommendedVideos()
    {

        $fields = 'videos.id, videos.title, videos.slug, videos.description, videos.thumbnail_image, videos.hls_playlist_url, videos.id as is_favourite, videos.id as collection, videos.poster_image,videos.is_live, videos.scheduledStartTime,videos.is_premium, videos.view_count,videos.trailer_hls_url,videos.trailer_status';
        $result = array();
        try {
            $result['error'] = false;
            $result['message'] = '';
            $result['data'] = $this->video->whereIn('slug', $this->request->slug_list)->where('job_status', 'Complete')->where('is_archived', 0)->where('is_active', 1)->selectRaw($fields)->orderBy('scheduledStartTime', 'asc')->paginate(config('access.perpage'));
        } catch (\Exception $e) {
            $result['error'] = true;
            $result['message'] = $e->getMessage();
        }
        return $result;

    }
}
