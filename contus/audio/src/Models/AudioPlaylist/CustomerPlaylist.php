<?php
/**
 * Customer Audio Playlist Model.
 *
 * @name CustomerAudioPlaylist
 * @vendor Contus
 * @package Audio
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Models\AudioPlaylist;

use Contus\Base\MongoModel;
use Contus\Base\Model;
use Contus\Audio\Models\Audios;
use Contus\Audio\Models\AudioPlaylist\AudioPlaylist;

class CustomerPlaylist extends MongoModel{
    protected $collection = 'customer_audio_playlists';
    protected $connection = 'mongodb';
    protected $appends = ['is_added','audio_count','poster_image'];
    /**
     * Method to save the created_at date for each record
     * 
     * @vendor Contus
     * @package Audio
     * @return void
     */
    public function bootSaving() {
        $this->setDynamicSlug('audio_playlist_name');
    }
    /**
     * Method to fetch audios related to a playlist
     * 
     * @vendor Contus
     * @package Audio
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function playlistAudios() {
        return $this->hasMany(AudioPlaylist::class, 'playlist_id', '_id');
    }
    /**
     * Method to attach the audio playlist flag to the result
     * 
     * @vendor Contus
     * @package Audio
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getIsAddedAttribute() {
        if(app()->request->has('audio_id') && app()->request->audio_id != '') {
            $audioInput = app()->request->audio_id;
            $audioInfo = Audios::where($this->getKeySlugorId(), $audioInput)->first();
            if(!empty($audioInfo)) {
                $audioInfo = $audioInfo->makeVisible(['id']);
                if($this->playlistAudios()->where('audio_id', $audioInfo['id'])->first()) {
                    return 1;
                }
            }
        }
        return 0;
    }
    /**
     * Method to fetch the count of audios in a playlist
     * 
     * @vendor Contus
     * @package Audio
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getAudioCountAttribute() {
        return $this->playlistAudios()->count();
    }
    /**
     * Method to fetch the poster image of the playlist
     * 
     * @vendor Contus
     * @package Audio
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getPosterImageAttribute() {
        $posterImage = '';
        if($this->playlistAudios()->count()) {
            $audioInfo = $this->playlistAudios()->first();
            if(!empty($audioInfo)) {
                $audioData = Audios::where('id', $audioInfo['audio_id'])->first();
                if(!empty($audioData)) {
                    $posterImage =  $audioData->audio_thumbnail;
                }
            }
        }
        return (!empty($posterImage)) ? $posterImage : '';
    }
}