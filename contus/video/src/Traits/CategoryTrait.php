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
 * @copyright Copyright (C) 2016 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Video\Traits;

use Contus\Customer\Models\MypreferencesVideo;
use Contus\Video\Models\Category;
use Contus\Video\Models\Collection;
use Contus\Video\Models\Group;
use Contus\Video\Models\Season;
use Contus\Video\Models\Video;
use Contus\Video\Models\VideoCategory;
use Contus\Video\Models\Webseries;
use DB;
use Illuminate\Http\Request;

trait CategoryTrait
{
    /**
     * Function to get all categories.
     *
     * @return array All categories.
     */
    public function getChildCategoryEach($value)
    {
        $subcatvalue = array();
        foreach ($value['child_category'] as $newvalue) {
            if (config()->get('auth.providers.users.table') === 'customers') {
                $subcatvalue[$newvalue[$this->getKeySlugorId()]] = $newvalue['title'];
            } else {
                $subcatvalue[$newvalue['id']] = $value['title'] . ' > ' . $newvalue['title'];
            }
        }
        return $subcatvalue;
    }

    /**
     * Function to get all categories.
     *
     * @return array All categories.
     */
    public function getAllCategoriesSlugs()
    {
        if ($this->request->has('main_category') && !empty($this->request->main_category)) {
            return $this->_category->where('parent_id', 0)->where($this->getKeySlugorId(), $this->request->main_category)->has('child_category.child_category.videos')->where('is_active', 1)->with(['child_category' => function ($query) {
                return $query->has('child_category.videos')->with(['child_category' => function ($query) {
                    return $query->has('videos')->with('videosCount')->orderBy('id', 'desc');
                }])->orderBy('is_leaf_category', 'asc');
            }])->first();
        }
        return $this->_category->where('parent_id', 0)->where('is_active', 1)->has('child_category.child_category.videos')->with(['child_category' => function ($query) {
            return $query->has('child_category.videos')->with(['child_category' => function ($query) {
                return $query->has('videos')->with('videosCount');
            }]);
        }])->get();
    }
    /**
     * Funtion to get related category video with complete information using slug
     *
     * @vendor Contus
     *
     * @package video
     * @return array
     */
    public function getRelatedVideoSlug($slug, $getCount = 10, $paginate = true)
    {

        $video = new Video();
        $currentVideo = $video->whereCustomer()->where($this->getKeySlugorId(), $slug)->first();
        $genre = $currentVideo->group()->first();

        if (!empty($genre)) {
            $video = new Video();
            // ->where('videos.is_active', '1')->where('job_status', 'Complete')->where('is_archived', 0)
            $video = $video->whereCustomer()->selectRaw('videos.id, videos.title, videos.slug, videos.thumbnail_image, videos.poster_image, videos.id as is_favourite, videos.is_live,videos.view_count,videos.is_premium, videos.price,videos.trailer_hls_url,videos.trailer_status')->whereHas('group', function ($query) use ($genre, $currentVideo) {
                $query->where('groups.id', $genre->id)->where('collections_videos.video_id', '!=', $currentVideo->id);
            });
        } else {
            $video = new Video();
            $video = $video->whereCustomer()->where($this->getKeySlugorId(), $slug)->first()->categories();

            if (!empty($video->get()->toArray())) {

                if ($currentVideo->is_live == 1) {
                    return $this->repository->getLiverelatedVideos($slug)->toArray();
                }

                $video = $video->first()->videos()->where('videos.' . $this->getKeySlugorId(), '!=', $slug)->where('is_live', '==', 0)->orderBy('video_order', 'asc');
                $video = $video->where('is_live', '==', 0)->orderBy('video_order', 'asc');
            }
        }
        if ($paginate) {
            $video = $video->paginate($getCount, ['videos.id', 'videos.title', 'videos.slug', 'videos.thumbnail_image', 'videos.poster_image', 'videos.id as is_favourite', 'videos.is_live', 'videos.view_count', 'videos.is_premium', 'videos.price', 'videos.trailer_hls_url', 'videos.trailer_status']);
        } else {
            $video = ($getCount) ? $video->take($getCount)->get() : $video->get();
        }
        return ($paginate) ? $video->toArray() : $video;
    }
    /**
     * Funtion to get parent category video with complete information using slug
     *
     * @vendor Contus
     *
     * @package video
     * @return array
     */
    public function getParentCategory($slug)
    {
        return $this->_category->where('level', 0)->where('is_active', 1)->where($this->getKeySlugorId(), $slug)->first()->parent_category()->first()->parent_category()->first();
    }
    /**
     * Funtion to get parent category video with complete information using slug
     *
     * @package video
     * @return array
     */
    public function getChidCategory($slug)
    {
        return $this->_category->where('level', 1)->where('is_active', 1)->where($this->getKeySlugorId(), $slug)->has('child_category.videos')->with(['child_category' => function ($q) {
            return $q->where('is_active', 1)->has('videos')->orderBy('is_leaf_category', 'desc')->with(['videosCount']);
        }])->first();
    }

    /**
     * Funtion to get count of category video with complete information using slug
     *
     * @return array
     */
    public function getChidCategoryCount($slug)
    {
        return $this->_category->where('level', 1)->where('is_active', 1)->where($this->getKeySlugorId(), $slug)->with('child_category')->get()->count();
    }
    /**
     * Funtion to get category for navigation
     *
     * @vendor Contus
     *
     * @package video
     * @return array
     */
    public function getCategoiesNav($detail = false)
    {
        if ($detail) {
            $return = $this->_category->where('level', 1)->where('is_active', 1)->has('child_category.videos')->with(['parent_category', 'child_category' => function ($q) {
                return $q->where('is_active', 1)->has('videos')->orderBy('is_leaf_category', 'desc')->with('videosCount');
            }])->orderBy('is_leaf_category', 'desc')->get();
        } else {
            $return = $this->_category->where('level', 1)->where('is_active', 1)->has('child_category.videos')->with('parent_category')->take(8)->orderBy('is_leaf_category', 'desc')->get();
            foreach ($return as $k => $v) {
                $return[$k]['child_category'] = $v->child_category()->has('videos')->with('videosCount')->orderBy('is_leaf_category', 'desc')->paginate(11)->toArray();
            }
        }
        return $return;
    }
    /**
     * Function to get all exams by categories
     *
     * @return object
     */
    public function getAllExamsByCategories()
    {
        $collection = new Collection();
        if ($this->request->has('exam_id')) {
            $collection = $collection->where('is_active', 1)->where('slug', $this->request->exam_id)->first()->groups()->has('group_videos')->with(['group_videos' => function ($query) {
                $query->selectRaw('count(videos.id) as count')->groupBy('group_id');
            }])->orderByRaw(' convert(`order`, decimal) desc ')->get();
        } else {
            $collection = $collection->where('is_active', 1)->has('groups')->orderBy('order', 'desc')->get();

            if (count($collection)) {
                foreach ($collection as $k => $v) {
                    $collection[$k]['exams'] = $collection[$k]->groups()->has('group_videos')->with(['group_videos' => function ($query) {
                        $query->selectRaw('count(videos.id) as count')->groupBy('group_id');
                    }])->orderByRaw('convert(`order`, decimal) desc')->get();
                }
            }
        }
        return $collection;
    }

    /**
     * Funtion to get category types and exam types
     *
     * @return array
     */
    public function browsepreferenceListPlaylist()
    {
        $customer_preferences = MypreferencesVideo::where('user_id', $this->authUser->id)->pluck('category_id')->toArray();
        $subcategory = $this->_category->where('is_active', 1)->where('level', 1)->whereNotIn('id', $customer_preferences)->get();
        $exams = Collection::where('is_active', 1)->whereNotIn('id', $customer_preferences)->get();
        if (isset($customer_preferences) || (!empty($subcategory)) && $this->request->header('x-request-type') == 'mobile') {
            return ['sub-categories' => $subcategory, 'exam' => $exams];
        }
    }
    /**
     * Funtion to get all category and exam types
     *
     * @return array
     */
    public function browsepreferenceListAll()
    {
        $subcategory = Category::where('is_active', 1)->where('level', 1)->has('child_category.videos')->with(['child_category_count' => function ($q) {
            return $q->where('is_active', 1)->has('videos');
        }])->orderBy('is_leaf_category', 'desc')->get();
        $exams = Collection::where('is_active', 1)->get();
        return ['sub-categories' => $subcategory, 'exam' => $exams];
    }

    /**
     * Funtion to validate the input types
     *
     * @return object
     */
    public function validateVideoType()
    {
        $this->setRules(['type' => 'required|in:recent,related,trending', 'id' => 'required_if:type,related']);
        $this->_validate();
    }

    public function getMainCategory() {
        return Category::where('parent_id', 0)->where('level', 0)->where('is_active', 1)->orderBy('category_order', 'asc')->paginate(8);
    }

    public function getMainCategoryAll() {
        return Category::where('parent_id', 0)->where('level', 0)->where('is_active', 1)->orderBy('category_order', 'asc')->paginate(100);
    }

    /**
     * Get all parent web series
     */
    public function parentWebseriesList()
    {
        return Category::where('parent_id', 0)->where('level', 0)->where('is_web_series', 1)->where('is_active', 1)->orderBy('category_order', 'asc')->paginate(10);
    }

/**
 * Get all category under the web series
 */
    public function getAllWebseries()
    {
        $weseriesCategoriesId = Category::where('video_webseries_detail_id', '!=', null)->pluck('video_webseries_detail_id');
        $weseriesCategories = Webseries::whereIn('id', $weseriesCategoriesId)->where('is_active', 1)->where('is_active_home', 1)->orderBy('webseries_order', 'asc')->paginate(10);
        return $weseriesCategories->toArray();
    }
 
/**
 *  Browse web series based on parent categories
 */
    public function browseChildWebseries($slug)
    {

        $inputArray = $this->request->all();
        $section = (!empty($inputArray['section'])) ? $inputArray['section'] : 1;
        $webseriesInfo['main_webseries'] = [];
        $webseriesInfo['genre_webseries'] = [];
        $parentCategory = Category::where($this->getKeySlugorId(), $slug)->where('is_active', 1)->first();
        if ($parentCategory) {
            if ($section == 1) {
                $webseriesId = Category::where('parent_id', $parentCategory->id)->orderBy('category_order', 'asc')->pluck('video_webseries_detail_id');
                $webseries = Webseries::whereIn('id', $webseriesId)->where('is_active', 1)->with('genre');
                $webseries = $webseries->paginate(12)->toArray();
                $webseries['category_name'] = $parentCategory->title;
                $webseriesInfo['main_webseries'][0] = $webseries;
                $this->fetchWebseriesGenre($parentCategory);
                $webseriesInfo['genre_webseries'] = [];
            } else {
                $genreArray = $this->fetchWebseriesGenre($parentCategory);
                $webseriesInfo['genre_webseries'] = $this->fetchGenreWebseries($genreArray, $section);
            }
            return $webseriesInfo;
        } else {
            $webseries = Webseries::where('id', null)->paginate(12); // For pagination like data we are using null where condition
            $webseries = $webseries->toArray();
            $webseries['category_name'] = empty($parentCategory->title) ? '' : $parentCategory->title;
            $webseriesInfo['main_webseries'][0] = $webseries;
            return $webseriesInfo;
        }
    }

/**
 *  Browse web series detail
 */
    public function browseWebseries($slug, $type = null)
    {
        $webseries = Webseries::where($this->getKeySlugorId(), $slug)->where('is_active', 1)->first();
        if ($type && $type === 'meta') {
            return $webseries;
        }
        if ($webseries) {
            return Category::with('webseriesDetail.genre', 'webseriesDetail.parent_category')->where('video_webseries_detail_id', $webseries->id)->orderBy('category_order', 'asc')->first();
        }
        return null;
    }

    /**
     * Fetch genre from the web series
     */
    public function fetchWebseriesGenre($webseries)
    {
        $webseriesId = Category::where('parent_id', $webseries->id)->orderBy('category_order', 'asc')->pluck('video_webseries_detail_id');
        $webseries = Webseries::whereIn('id', $webseriesId)->where('is_active', 1);
        $result['series_info'] = $webseries->pluck('id')->toArray();
        $result['group_info'] = $webseries->pluck('genre_id')->toArray();
        $this->request->request->add(['fetched_series_ids' => $result['series_info']]);
        return $result['group_info'];
    }

/**
 * Fetch web series based on the genre
 */
    public function fetchGenreWebseries($genreArray, $section)
    {
        if ($section == 1) {
            return [];
        }

        if ($section == 2) {
            return Group::selectRaw('*, id as series_list')->whereIn('id', $genreArray)->orderBy('order', 'asc')->orderBy('name', 'asc')->get()->toArray();
        }

        return Group::selectRaw('*, id as series_list')->whereIn('id', $genreArray)->orderBy('order', 'asc')->orderBy('name', 'asc')->get()->toArray();
    }

    /**
     * Fetch more web series based in the pagination.
     */

    public function fetchMoreWebseries()
    {
        $result = [];
        $inputArray = $this->request->all();
        $genreInfo = Group::where($this->getKeySlugorId(), $inputArray['genre'])->first();
        if (!empty($genreInfo)) {
            $result = $this->fetchGenreWebseries([$genreInfo['id']], 0);
            // To convert Array into single object in response format
            $result = !empty($result[0]) ? $result[0] : [];
        }
        $videoInfo['more_webseries'] = $result;
        return $videoInfo;
    }
}
