<?php

/**
 * Playlist Repository
 *
 * To manage the functionalities related to videos
 *
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 *
 */
namespace Contus\Video\Repositories;

use Contus\Base\Repository as BaseRepository;
use Contus\Video\Models\Playlist;
use Contus\Video\Models\UserPlaylist;
use Contus\Video\Models\PlaylistVideos;
use Contus\Video\Models\Video;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Contus\Video\Traits\PlaylistTrait as PlaylistTrait;
use Illuminate\Support\Facades\Cache;

class PlaylistRepository extends BaseRepository {
    use PlaylistTrait;
    /**
     * construct function
     *
     * @param Playlist $play
     */
    public function __construct(Playlist $plays) {
        parent::__construct ();
        $this->_playlist = $plays;
    }
    /**
     * function to add or update playlist details
     *
     * @param int $id
     * @return boolean
     */
    public function addOrUpdatePlaylist($id = null) {
        if (! empty ( $id )) {
            $playlist = $this->_playlist->find ( $id );
            if (! is_object ( $playlist )) {
                return false;
            }
            $this->setRules ( [ 'name' => 'sometimes|required|max:255','is_active' => 'sometimes|required|boolean' ] );
            $playlist->updator_id = $this->authUser->id;
        } else {
            $this->setRules ( [ 'name' => 'required','is_active' => 'required|boolean' ] );
            $playlist = new Playlist ();
            $playlist->is_active = 1;
            $playlist->creator_id = $this->authUser->id;
        }
        $this->_validate ();
        $playlist->fill ( $this->request->except ( '_token' ) );
        $this->_playlist = $playlist;
        $playlist->playlist_order = $this->request->playlist_order;
        $playlist = $playlist->save ();
        if ($playlist) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Fetch one playlist record
     *
     * @param int $playlistId
     * @return object
     */
    public function getPlaylist($playlistId) {
        $this->_playlist = $this->_playlist->find ( $playlistId );
        if (is_object ( $this->_playlist ) && ! empty ( $this->_playlist->id )) {
            return [ 'playlist' => $this->_playlist,'followers' => $this->_playlist->with ( 'followers' ),'videos' => $this->_playlist->videos ()->with ( 'videocategory.category', 'recent' )->paginate ( 10 )->toArray () ];
        }
        return false;
    }
   
    /**
     * Delete one playlist record
     *
     * @param id $playlistId
     * @return boolean
     */
    public function deletePlaylist($playlistId) {
        $data = $this->_playlist->find ( $playlistId );
        if ($data) {
            $data->delete ();
            return true;
        } else {
            return false;
        }
    }
    /**
     * Function used to combine two arrays with same key
     *
     * @param array $arraykey
     * @param array $arrayvalue
     * @return bool|array
     */
    function array_combine_function($arraykey, $arrayvalue,$checkuserId='') {
        $result = array ();
        $checkuserId = ($checkuserId)?$checkuserId:$this->authUser->id;
        if ($this->request->method () == 'PUT') {
            $deletepref = new $this->_preference ();
            $deletepref->where ( 'user_id', $checkuserId )->delete ();
        }
        if (! empty ( $arraykey )) {
            foreach ( $arraykey as $i => $k ) {
                $result [$k] [] = $arrayvalue [$i];
                $preferencearray [] = [ 'category_id' => $k,'type' => $arrayvalue [$i],'user_id' => $checkuserId ];
            }
            array_walk ( $preferencearray, create_function ( '&$v', '$v = (count($v) == 1)? array_pop($v): $v;' ) );
            if (isset ( $preferencearray )) {
                foreach ( $preferencearray as $value ) {
                    $savepreference = new $this->_preference ();
                    $savepreference->category_id = $value ['category_id'];
                    $savepreference->type = $value ['type'];
                    $savepreference->user_id = $value ['user_id'];
                    $savepreference->save ();
                }
            }
            return $value;
        }
        return true;
    }
    /**
     * Delete videos from playlist
     *
     * @return boolean
     */
    public function deletePlaylistVideos() {
        $playlist = $this->_playlist->find ( $this->request->id );
        if (is_object ( $playlist ) && ! empty ( $playlist->id )) {
            $selectedId = $this->request->selectedVideos;
            $selectedId = explode ( ',', $selectedId );
            $selectedId = array_map ( 'intval', $selectedId );
            $existingVideos = $playlist->videos ()->lists ( 'video_id' )->toArray ();
            $filteredArray = array_intersect ( $existingVideos, $selectedId );
            if (! empty ( $filteredArray )) {
                $selectedVideos = Video::whereIn ( 'id', $filteredArray )->lists ( 'id' )->toArray ();
                $playlist->videos ()->detach ( $selectedVideos );
            }
            return true;
        }
        return false;
    }

  

    /**
     * Repository function to get the selected playlist category
     *
     * @return variable
     */
    public function getmypreferenceCategoryList() {
        $customer_preferences = $this->_preference->where ( 'user_id', $this->authUser->id )->get ();
        if (is_null ( $this->_preference )) {
            return [ 'preference-category' => [ ] ];
        }
        $customer_preference = [ ];
        foreach ( $customer_preferences as $k => $peference ) {
            if ($peference->type == 'exam') {
                $checkPref = $peference->preference_exams ()->first ();
                $customer_preferences [$k] ['preference_category'] = $checkPref;
                if (! $checkPref) {
                    unset ( $customer_preferences [$k] );
                    continue;
                }
                $customer_preference [] = $customer_preferences [$k];
            } else {
                $checkPref = $peference->preference_category ()->first ();
                $customer_preferences [$k] ['preference_category'] = $checkPref;
                if (! $checkPref) {
                    unset ( $customer_preferences [$k] );
                    continue;
                }
                $customer_preference [] = $customer_preferences [$k];
            }
        }
        if (count ( $customer_preference ) > 0) {
            return [ 'preference-category' => $customer_preference ];
        } else {
            return [ 'preference-category' => [ ] ];
        }
    }
    /**
     * Function to apply filter for search of Playlists grid
     *
     * @param mixed $builderPlaylists
     * @return \Illuminate\Database\Eloquent\Builder $builderPlaylists The builder object of collections grid.
     */
    protected function searchFilter($builderPlaylists) {
        $searchRecordPlaylists = $this->request->has ( 'searchRecord' ) && is_array ( $this->request->input ( 'searchRecord' ) ) ? $this->request->input ( 'searchRecord' ) : [ ];
        $title = $is_active = null;
        extract ( $searchRecordPlaylists );
        if ($title) {
            $builderPlaylists = $builderPlaylists->where ( 'name', 'like', '%' . $title . '%' );
        }
        if (is_numeric ( $is_active )) {
            $builderPlaylists = $builderPlaylists->where ( 'is_active', $is_active );
        }
        return $builderPlaylists;
    }
    /**
     * Repository function to get the collection related videos list
     *
     * @param integer $id
     * @return variable
     */
    public function getPlaylistByType() {
        $playlist = $this->_playlist->where ( 'is_active', 1 )->has ( 'videos' )->orderBy('id','desc')->take ( 4 )->get ();
        foreach ( $playlist as $k => $video ) {
            $playlist [$k] ['videos'] = $video->videos ()->count ();
            $playlist [$k] ['followers'] = $video->followers ()->count ();
            $playlist [$k] ['following'] = (auth ()->user ()) ? $video->authFollower ()->count () : 0;
            unset ( $playlist [$k]->authFollower );
        }
        return $playlist->toArray ();
    }

    /**
     * Repository function to get the playlist related videos list
     *
     * @param integer $playlist_id
     * @return variable
     */
    public function getPlaylistByVideos($playlist_id) {
        $playlist = $this->_playlist->where ( $this->getKeySlugorId (), $playlist_id )->first ();
        if (!empty($playlist) && count ( $playlist->toArray() ) > 0) {
            $playlist ['followers'] = $playlist->followers ()->count ();
            $playlist ['following'] = (auth ()->user ()) ? $playlist->authFollower ()->count () : 0;
        }
        return $playlist->toArray ();
    }

    /**
     * Repository function to get the playlist related videos list
     *
     * @param integer $playlist_id
     * @return variable
     */
    public function getPlaylistByVideosRelated($playlist_id, $video_id) {
        $fields = 'videos.id,videos.title, videos.slug, videos.thumbnail_image, videos.id as is_favourite, videos.id as video_category_name, videos.id as is_like, videos.id as is_dislike, videos.id as like_count, videos.id as dislike_count, videos.id as auto_play, videos.id as season_name, videos.id as season_id, videos.price,videos.trailer_hls_url,videos.trailer_status';
        if(!isMobile()) {
            $actualVideo = Video::Select('id')->where('is_active',1)->where($this->getKeySlugorId (), $video_id)->first();
            $video_id    = (!empty($actualVideo)) ? $actualVideo['id'] : 0;
        }
        
        $videoArray = PlaylistVideos::where('playlist_id', (string) $playlist_id)->where('video_id', '!=',$video_id)->pluck('video_id');
        return Video::SelectRaw($fields)->whereIn('id', $videoArray)->where('id', '!=', $video_id)->groupBy('videos.id')->paginate(10);
    }
    /**
     * function to get playlist based on followers videos and following of the current user
     *
     * @param string $type
     * @return array
     */
    public function browseSortPlaylist($type = '') {
        if ($type && $type == 'mostpopular') {
            $getPlaylist = $this->_playlist->SelectRaw('*, playlists.id as video_count, playlists.id as followers_count, playlists.id as following, playlists.id as video_info')->where ( 'playlists.is_active', 1 )->has ( 'videos' )->leftJoin ( 'follow_playlists as follow', function ($follow) {
                $follow->on ( 'playlists.id', '=', 'follow.playlist_id' );
            } )->selectRaw ( 'playlists.*,count(follow.playlist_id) as followers' )->groupBy ( 'playlists.id' )->orderBy ( 'followers', 'desc' );
        } elseif ($type && $type == 'latest') {
            $getPlaylist = $this->_playlist->SelectRaw('*, playlists.id as video_count, playlists.id as followers_count, playlists.id as following, playlists.id as video_info')->where ( 'playlists.is_active', 1 )->has ( 'videos' )->leftJoin ( 'video_playlists as follow', function ($follow) {
                $follow->on ( 'playlists.id', '=', 'follow.playlist_id' );
            } )->selectRaw ( 'playlists.*' )->groupBy ( 'playlists.id' )->orderBy ( 'updated_at', 'desc' );
        } else {
            $getPlaylist = $this->_playlist->SelectRaw('*, playlists.id as video_count, playlists.id as followers_count, playlists.id as following, playlists.id as video_info')->where ( 'playlists.is_active', 1 )->has ( 'videos' )->orderBy ( 'playlist_order', 'desc' );
        }
        return $getPlaylist->paginate ( 12 );
    }
}