<?php
/**
 * Audio Playlist Model.
 *
 * @name AudioPlaylist
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
use Contus\Customer\Models\Customer;

class AudioPlaylist extends MongoModel{
    protected $collection = 'audio_playlists';
    protected $connection = 'mongodb';
    /**
     * Method to fetch audios tracks related to a playlist
     * 
     * @vendor Contus
     * @package Audio
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function playlistAudioTracks(){
        return $this->belongsTo(Audios::class, 'audio_id', 'id');
    }
}

