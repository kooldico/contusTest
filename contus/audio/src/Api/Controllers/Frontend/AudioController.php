<?php
/**
 * AudioController
 *
 * To manage the audio management such as upload, create, edit and delete
 *
 * @name AudioController
 * @version 1.0
 * @author Contus Team <developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Api\Controllers\Frontend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File as Makefile;
use Contus\Base\ApiController;
use Contus\Audio\Helpers\UploadHandler;
use Contus\Audio\Repositories\AudioRepository;

class AudioController extends ApiController{
    /**
     * Class construct method initialization
     */
    public function __construct(){
        parent::__construct();
        $this->repository = new AudioRepository();
        $this->repository->setRequestType(static::REQUEST_TYPE);
    }
    /**
     * Method to track audio play count, history and analytics
     * 
     * @vendor Contus
     * @package Audio
     * @return Illuminate\Http\Response
     */
    public function saveTracksPlayHistory(){
        $data = $this->repository->tracksPlayHistory();
        return ($data) ? $this->getSuccessJsonResponse ( [ 'message' => trans ( 'audio::audio.audio_play_history.add.success' ) ] ) : $this->getErrorJsonResponse ( [ ], trans ( 'audio::audio.audio_play_history.add.error' ) );
    }
    /**
     * Method to fetch customers played audios data
     * 
     * @vendor Contus
     * @package Audio
     * @return Illuminate\Http\Response
     */
    public function fetchAudioHistory(){
        $data = $this->repository->getCustomerPlayedAudioHistory();
        return ($data) ? $this->getSuccessJsonResponse ([ 'response' => $data, 'message' => trans ( 'audio::audio.audio_play_history.fetch.success' ) ] ) : $this->getErrorJsonResponse ( [ ], trans ( 'audio::audio.audio_play_history.fetch.error' ) );
    }
    /**
     * Method to delete the respective customer's audio history
     * 
     * @vendor Contus
     * @package Audio
     * @return Illuminate\Http\Response
     */
    public function clearHistory(){
        $data = $this->repository->clearCustomerAudioHistory();
        return ($data) ? $this->getSuccessJsonResponse ([ 'message' => trans ( 'audio::audio.audio_play_history.clear_history.success' ) ] ) : $this->getErrorJsonResponse ( [ ], trans ( 'audio::audio.audio_play_history.clear_history.error' ) );
    }
    /**
     * Method to get single track details
     * 
     * @vendor Contus
     * @package Audio
     * @return Illuminate\Http\Response
     */
    public function fetchAudioDetails(){
        $data = $this->repository->getAudioDetails();
        return ($data) ? $this->getSuccessJsonResponse ([ 'response' => $data],trans ( 'audio::album.record_fetched_success' )) : $this->getErrorJsonResponse ( [ ], trans ( 'audio::album.record_fetched_error' ) );
    }
}
?>