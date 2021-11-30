<?php
/**
 * Customer Audio Playlist Repository
 *
 * To manage the functionalities related to the customer audio playlists
 *
 * @name CustomerAudioPlaylistRepository
 * @vendor Contus
 * @package Audio
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */

namespace Contus\Audio\Repositories;

use Contus\Base\Repository as BaseRepository;
use Contus\Audio\Models\AudioPlaylist\CustomerPlaylist;
use Contus\Audio\Models\AudioPlaylist\AudioPlaylist;
use Contus\Audio\Models\Audios;
use Carbon\Carbon;

class CustomerAudioPlaylistRepository extends BaseRepository{
    /**
     * Construct method
     */
    public function __construct(){
        parent::__construct();
        $this->customerAudioPlaylist = new CustomerPlaylist();
        $this->audios = new Audios();
        $this->audioPlaylist = new AudioPlaylist();
        $this->records_per_page = config('contus.audio.audio.record_per_page');
        $this->user_playlist_records_per_page = config('contus.audio.audio.user_playlist_tracks_per_page');

    }
    /**
     * Method to fetch all the playlists of the customer
     * 
     * @vendor Contus
     * @package Audio
     * @return array
     */
    public function fetchAllPlaylists(){
        $customerID = (!empty(auth()->user()->id))?auth()->user()->id:0;
        try {
            return $this->customerAudioPlaylist->where('customer_id', $customerID)->orderBy('_id', 'desc')->paginate($this->records_per_page);
        } catch(\Exception $e) {
            $this->throwJsonResponse(false, 500,  $e->getMessage());
        }
    }
    /**
     * Method to create cutomer playlist
     * 
     * @vendor Contus
     * @package Audio
     * @return boolean
     */
    public function createOrUpdatePlaylist(){
        $playlistName = '';
        if(isset($this->request->id)){
            return $this->updatePlaylist();
        }
        $this->setRules(['name' => 'required']);
        $this->validate($this->request, $this->getRules());
        $playlistName = $this->request->name;
        $customerID = (!empty(auth()->user()->id))?auth()->user()->id:0;
        $this->checkPlaylistAlreadyExists();
        $this->customerAudioPlaylist->audio_playlist_name =  $playlistName;
        $this->customerAudioPlaylist->customer_id =  $customerID;
        return ($this->customerAudioPlaylist->save()) ? 1 : 0;
    }
    /**
     * Method to check if playlist already exists
     * 
     * @vendor Contus
     * @package Audio
     * @return \Illuminate\Http\Response
     */
    public function checkPlaylistAlreadyExists(){
        $playlistName = '';
        $playlistName = $this->request->name;
        $customerID = (!empty(auth()->user()->id))?auth()->user()->id:0;
        $playlistInfo = $this->customerAudioPlaylist->where('audio_playlist_name', $playlistName)->where('customer_id',$customerID)->first();
        (!empty($playlistInfo))?$this->throwJsonResponse(false, 422, trans('audio::audio.audio_playlists.playlist_already_exists')):'';
    }
    /**
     * Method to update playlist, add audios to playlist
     * 
     * @vendor Contus
     * @package Audio
     * @return boolean
     */
    public function updatePlaylist(){
        $result = false;
        if($this->request->has('name')){
            $result = $this->updatePlaylistName();
        }
        if($this->request->has('audios')){
            $result = $this->addAudiosToPlaylist();
        }
        return $result;
    }
    /**
     * Method to update playlist name
     * 
     * @vendor Contus
     * @package Audio
     * @return boolean
     */
    public function updatePlaylistName(){
        $this->setRules(['name' => 'required']);
        $this->validate($this->request, $this->getRules());
        try{
            $this->customerAudioPlaylist = $this->customerAudioPlaylist->where('_id', $this->request->id)->first();
            $this->customerAudioPlaylist->audio_playlist_name = $this->request->name;
        } catch(\Exception $e) {
            $this->throwJsonResponse(false, 500,  trans( 'audio::audio.audio_playlists.playlist_not_exists' ));
        }
        return ($this->customerAudioPlaylist->save())?1:0;
    }
    /**
     * Method to insert audios into created playlist
     * 
     * @vendor Contus
     * @package Audio
     * @return boolean
     */
    public function addAudiosToPlaylist(){
        $this->setRules(['audios' => 'required']);
        $this->validate($this->request, $this->getRules());
        $audiosData = $this->fetchAudioData();
        (empty($audiosData))?$this->throwJsonResponse(false, 500,  trans( 'audio::audio.audio_playlists.audio_not_exists' )):'';
        $audiosPlaylistData = $this->fetchExistingPlaylistData($audiosData);
        foreach($audiosData as $audioId) {
            $isKeyExist = array_search($audioId, array_column($audiosPlaylistData, 'audio_id'));
            if(empty($isKeyExist) && $isKeyExist !== 0) {
                    $audioPlaylist              = $this->audioPlaylist;
                    $audioPlaylist->playlist_id = $this->request->id;
                    $audioPlaylist->audio_id    =   $audioId;
                    $audioPlaylist->save();
                }
        }
        return true;
    }
    /**
     * Method to fetch audio data with id or slug as input
     * 
     * @vendor Contus
     * @package Audio
     * @return array
     */
    public function fetchAudioData(){
        $audioInput = $this->request->audios;
        $audioArray     = explode(',', $audioInput);
        return $this->audios->whereIn($this->getKeySlugorId(), $audioArray)->pluck('id', 'slug')->toArray();   
    }
    /**
     * Method to fetch existing audio playlist of a customer
     * 
     * @vendor Contus
     * @package Audio
     * @param array $audiosData
     * @return array
     */
    public function fetchExistingPlaylistData($audiosData){
        $playlistId = $this->request->id;
        return $this->audioPlaylist->where('playlist_id', (string) $playlistId)->whereIn('audio_id', $audiosData)->get()->toArray();
    }
    /**
     * Method to fetch all the audio tracks in a playlist of a customer
     * 
     * @vendor Contus
     * @package Audio
     * @param array $audiosData
     * @return array
     */
    public function fetchPlaylistAudios(){
        $result = array();
        $this->setRules(['playlist_id' => 'required']);
        $this->validate($this->request, $this->getRules());
        $playlistId = $this->request->playlist_id;
        $result['data']['playlist_info'] = $this->customerAudioPlaylist->Select('_id','audio_playlist_name','created_at')->where('_id', (string) $playlistId)->first();
        if(!empty($result['data']['playlist_info'])){
            $result['data']['playlist_tracks'] = $this->audioPlaylist->with('playlistAudioTracks')
            ->where('playlist_id',$playlistId)
            ->orderBy('_id', 'desc')->paginate($this->user_playlist_records_per_page)->toArray();
        }else{
            $this->throwJsonResponse(false, 500,  trans( 'audio::audio.audio_playlists.playlist_not_exists' ));
        }
        return $result;
    }
    /**
     * Method to delete the customer playlist
     * 
     * @vendor Contus
     * @package Audio
     * @return boolean
     */
    public function deletePlaylist(){
        $this->setRules(['playlist_id' => 'required']);
        $this->validate($this->request, $this->getRules());
        try {
            $playlistId = $this->request->playlist_id;
            $this->customerAudioPlaylist->where('_id', (string) $playlistId)->delete();
            $this->audioPlaylist->where('playlist_id', $playlistId)->delete();
            return true;
        }
        catch(\Exception $e) {
            $this->throwJsonResponse(false, 500,  $e->getMessage());
        }
    }
    /**
     * Method to delete a audio from the playlist
     * 
     * @vendor Contus
     * @package Audio
     * @return boolean
     */
    public function deletePlaylistAudios(){
        $audioId = '';
        $this->setRules(['playlist_id' => 'required', 'audio_id' => 'required']);
        $this->validate($this->request, $this->getRules());
        $playlistId = $this->request->playlist_id;
        $audioInput = $this->request->audio_id;
        if(!isMobile()) {
            $audioInfo = $this->audios->select('id')->where('slug', $audioInput)->first();
            $audioId = (!empty($audioInfo)) ? $audioInfo->id : $this->throwJsonResponse(false, 500, trans( 'audio::audio.audio_playlists.audio_not_exists' ));
        }else{
            $audioId = $audioInput;
        }
        try {
            $this->audioPlaylist->where('playlist_id', (string) $playlistId)->where('audio_id', (int) $audioId)->delete();
            return true;
        } catch (\Exception $e){
            $this->throwJsonResponse(false, 500,  $e->getMessage());
        }
    }
}