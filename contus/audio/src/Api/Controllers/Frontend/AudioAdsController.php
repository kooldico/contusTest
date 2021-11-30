<?php
/**
 * AudioAdsController
 *
 * To manage the audio ad management such as upload, create, edit and delete
 *
 * @name AudioAdsController
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
use Contus\Audio\Repositories\AudioAdsRepository;

class AudioAdsController extends ApiController{
    /**
     * Class construct method initialization
     */
    public function __construct(){
        parent::__construct();
        $this->repository = new AudioAdsRepository();
        $this->repository->setRequestType(static::REQUEST_TYPE);
    }
    /**
     * Method to get random audio ads details
     * 
     * @vendor Contus
     * @package Audio
     * @return Illuminate\Http\Response
     */
    public function getRandomAudioAds(){
        $data['jingles'] = $this->repository->getAudioAdsDetails();
        return ($data) ? $this->getSuccessJsonResponse ([ 'response' => $data]) : $this->getErrorJsonResponse ( [ ], trans ( 'audio::album.record_fetched_error' ) );        
    }
}
?>