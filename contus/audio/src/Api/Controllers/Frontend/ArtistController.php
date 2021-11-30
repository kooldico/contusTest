<?php

/**
 * Artist Controller
 *
 * To manage the Audio Artist.
 *
 * @name       Artist Controller
 * @version    1.0
 * @author     Contus Team <developers@contus.in>
 * @copyright  Copyright (C) 2016 Contus. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Api\Controllers\Frontend;

use Illuminate\Http\Request;
use Contus\Audio\Repositories\ArtistRepository;
use Contus\Base\ApiController;
use Contus\Base\Helpers\StringLiterals;
use Contus\Base\Repositories\UploadRepository;

class ArtistController extends ApiController{
    /**
     * Construct method
     */
    public function __construct(){
        parent::__construct();
        $this->repository = new ArtistRepository();
    }
    /**
     * Method to get contents for artist detail page
     * 
     * @vendor Contus
     * @package Audio
     * @return Illuminate\Http\Response
     */
    public function getartistDetailPageContents(){
        $data = $response = array();
        $response = $this->repository->artistDetails();
        $data['artist_info'] = $response['artist_info'];
        $data['related_albums'] = $response['related_albums'];
        return (!empty($data)) ? $this->getSuccessJsonResponse(['response' => $data],trans('audio::album.record_fetched_success')) 
                                : $this->getErrorJsonResponse([],trans('audio::album.record_fetched_error'));
    }
    public function browseArtist()
    {
        $artist = $this->repository->browseArtist();
        $result['browse_data'] = $artist;
        return (!empty($artist)) ? $this->getSuccessJsonResponse(['response' => $result], trans('audio::album.record_fetched_success'))
        : $this->getErrorJsonResponse([], trans('audio::album.record_fetched_error'));
    }
}
