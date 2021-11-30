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
use Contus\Audio\Repositories\PlaylistRepository;

class PlaylistController extends ApiController{
    /**
     * Class construct method initialization
     */
    public function __construct(){
        parent::__construct();
        $this->repository = new PlaylistRepository();
        // $this->repository->setRequestType(static::REQUEST_TYPE);
    }
    public function getAllPlaylist(){

        $playlist = $this->repository->allPlaylists();
        $result = $playlist;
        return (!empty($playlist)) ? $this->getSuccessJsonResponse(['response' => $result], trans('audio::album.record_fetched_success'))
        : $this->getErrorJsonResponse([], trans('audio::album.record_fetched_error'));
    }

    /**
     * Method to get contents for playlist detail page
     *
     * @vendor Contus
     * @package Audio
     * @return Illuminate\Http\Response
     */
    public function getplaylistDetailPageContents()
    {
        $data = $response = array();
        $response = $this->repository->playlistDetails();
        $data['playlist_info'] = $response['playlist_info'];
        $data['related_playlist'] = $response['related_playlist'];
        return (!empty($data)) ? $this->getSuccessJsonResponse(['response' => $data], trans('audio::album.record_fetched_success'))
        : $this->getErrorJsonResponse([], trans('audio::album.record_fetched_error'));
    }
}
?>