<?php

/**
 * Playlist Trait
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
use Carbon\Carbon;
use Contus\Video\Models\Video;

trait PlaylistTrait {
    
    /**
     * Fetch all the playlist records using pagination
     *
     * @return array
     */
    public function getAllPlaylist() {
        return $this->_playlist->paginate ( 10 )->toArray ();
    }
    /**
     * Fetch all the playlist records using lists
     *
     * @return array
     */
    public function getAllPlaylistList() {
        return $this->_playlist->pluck ( 'name', 'id' );
    }

    /**
     * This function used to store the playlists
     *
     * @return boolean
     */
    public function savepreferenceListPlaylist() {
        $arraykey = $this->request->category_id;
        $arrayvalue = $this->request->type;
        $finalarray = $this->array_combine_function ( $arraykey, $arrayvalue );
        if ($finalarray) {
            return true;
        }
    }

    /**
     * Add videos to playlist
     *
     * @return boolean
     */
    public function addPlaylistVideos() {
        if ($this->request->has ( 'name' )) {
            $this->request->request->add ( [ 'is_active' => 1 ] );
            $this->addOrUpdatePlaylist ();
            $playlist = $this->_playlist;
        } else {
            $playlist = $this->_playlist->find ( $this->request->id );
        }
        if (is_object ( $playlist ) && (! empty ( $playlist->id ))) {
            $playlist->updated_at = Carbon::now ();
            $playlist->save ();
            $selectedId = $this->request->selectedVideos;
            $selectedId = explode ( ',', $selectedId );
            $selectedId = array_map ( 'intval', $selectedId );
            $existingVideos = $playlist->videos ()->pluck ( 'video_id' )->toArray ();
            $filteredArray = array_diff ( $selectedId, $existingVideos );
            if (! empty ( $filteredArray )) {
                $selectedVideos = Video::whereIn ( 'id', $filteredArray )->pluck ( 'id' )->toArray ();
                $attach = $playlist->videos ();
                if (isset ( $attach )) {
                    $attach->attach ( $selectedVideos );
                    $attach->touch ();
                }
                $playlist->clearCache ( [ 'playlistList' . $playlist->slug ] );
            }
            return true;
        }
        return false;
    }

}