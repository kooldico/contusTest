<?php

/**
 * Customer Audio Playlist Controller
 *
 * @name  CustomerAudioPlaylistController
 * @vendor Contus
 * @package Audio
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Api\Controllers\Frontend;

use Contus\Base\ApiController;
use Illuminate\Http\Request;
use Contus\Audio\Repositories\CustomerAudioPlaylistRepository;

class CustomerAudioPlaylistController extends ApiController {
    /**
     * Construct method
     */
    public function __construct() {
        parent::__construct ();
        $this->repository = new CustomerAudioPlaylistRepository();
        $this->repository->setRequestType ( static::REQUEST_TYPE );
    }
    /**
     * Display a listing of the resource.
     *
     * @vendor Contus
     * @package Audio
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $data = array();
        $data['my_playlist'] = $this->repository->fetchAllPlaylists();
        return ($data) ? $this->getSuccessJsonResponse ( [ 'response' => $data,'message'=>trans('audio::audio.audio_playlists.fetch_record.success') ] ) : $this->getErrorJsonResponse ( [ ], trans ( 'audio::audio.audio_playlists.fetch_record.error' ) );
    }
    /**
     * Store a newly created resource in storage.
     *
     * @vendor Contus
     * @package Audio
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $data = $this->repository->createOrUpdatePlaylist();
        $successMsg = (isset($this->request->id)) 
                    ? trans ( 'audio::audio.audio_playlists.update.success' )
                    : trans ( 'audio::audio.audio_playlists.add.success' );
        $errMsg = (isset($this->request->id)) 
                    ? trans ( 'audio::audio.audio_playlists.update.error' )
                    : trans ( 'audio::audio.audio_playlists.add.error' );
        return ($data) ? $this->getSuccessJsonResponse ( [ 'message' => $successMsg ] ) : $this->getErrorJsonResponse ( [ ], $errMsg );
    }
    /**
     * Remove the specified resource from storage.
     *
     * @vendor Contus
     * @package Audio
     * @return \Illuminate\Http\Response
     */
    public function destroy() {
        $data = $this->repository->deletePlaylist();
        return ($data) ? $this->getSuccessJsonResponse ( [ 'message' => trans ( 'audio::audio.audio_playlists.delete.success' ) ] ) : $this->getErrorJsonResponse ( [ ], trans ( 'audio::audio.audio_playlists.delete.error' ) );
    }
    /**
     * Method to fetch a playlists related audios
     *
     * @vendor Contus
     * @package Audio
     * @return \Illuminate\Http\Response
     */
    public function fetchPlaylistAudios(){
        $result = $this->repository->fetchPlaylistAudios();
        return ( $result ) ? $this->getSuccessJsonResponse ( [ 'response' => $result['data'],'message'=>trans('audio::audio.audio_playlists.fetch_record.success') ] ) : $this->getErrorJsonResponse ( [ ], trans ( 'audio::audio.audio_playlists.fetch_record.error' ) );
    }
    /**
     * Method to delete a audio from a playlist
     *
     * @vendor Contus
     * @package Audio
     * @return \Illuminate\Http\Response
     */
    public function deletePlaylistAudios(){
        $data = $this->repository->deletePlaylistAudios();
        return ($data) ? $this->getSuccessJsonResponse ( ['message'=>trans('audio::audio.audio_playlists.delete_audios.success') ] ) : $this->getErrorJsonResponse ( [ ], trans ( 'audio::audio.audio_playlists.delete_audios.error' ) );
    }
}
