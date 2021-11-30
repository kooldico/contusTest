<?php
/**
 * Audio customer Controller
 *
 * @name       AudioCustomerController
 * @vendor     Contus
 * @package    Audio
 * @version    1.0
 * @author     Contus<developers@contus.in>
 * @copyright  Copyright (C) 2018 Contus. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Api\Controllers\Frontend;

use Illuminate\Http\Request;
use Contus\Base\ApiController;
use Contus\Audio\Repositories\AudioCustomerRepository;

class AudioCustomerController extends ApiController{
    /**
     * Class construct method initialization
     */
    public function __construct(){
        parent::__construct();
        $this->repository = new AudioCustomerRepository();
    }
    /**
     * Method to customers favourite, playlist and recently played counts
     * 
     * @Vendor Contus
     * @package Audio
     * @return array
     */
    public function fetchprofilePageData(){
        $profileInfo = $this->repository->fetchCustomerProfileData();
        return (!empty($profileInfo)) ? $this->getSuccessJsonResponse(['response' => $profileInfo],trans('audio::album.record_fetched_success')) 
        : $this->getErrorJsonResponse([],trans('audio::album.record_fetched_error'));
    }
}