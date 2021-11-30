<?php

/**
 * Customer Favourite AUdio Controller
 *
 * @name Customer FavouriteAudiosController
 * @vendor Contus
 * @package Audio
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Api\Controllers\Frontend;

use Contus\Base\ApiController;
use Contus\Audio\Repositories\FavouriteAudioRepository;
use Contus\Customer\Models\Customer;
use Illuminate\Http\Request;

class FavouriteAudiosController extends ApiController {

    /**
     * Construct method
     */
    public function __construct() {
        parent::__construct ();
        $this->repository = new FavouriteAudioRepository();
        $this->repository->setRequestType ( static::REQUEST_TYPE );
        $this->repoArray = ['repository'];
    }
    /**
     * Display a listing of the resource.
     *
     * @vendor Contus
     * @package Audio
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $data = $this->repository->getAllFavouriteAudios ();
        return ($data) ? $this->getSuccessJsonResponse ( [ 'response' => $data,'message'=>trans('audio::audio.favourite_audios.fetch_record.success') ] ) : $this->getErrorJsonResponse ( [ ], trans ( 'audio::audio.favourite_audios.fetch_record.success' ) );
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
        $data = $this->repository->addOrDeleteFavouriteAudio();
        return ($data) ? $this->getSuccessJsonResponse ( [ 'message' => trans ( 'audio::audio.favourite_audios.add.success' ) ] ) : $this->getErrorJsonResponse ( [ ], trans ( 'audio::audio.favourite_audios.add.error' ) );
    }
    /**
     * Remove the specified resource from storage.
     *
     * @vendor Contus
     * @package Audio
     * @return \Illuminate\Http\Response
     */
    public function destroy() {
        $data = $this->repository->addOrDeleteFavouriteAudio();
        return ($data) ? $this->getSuccessJsonResponse ( [ 'message' => trans ( 'audio::audio.favourite_audios.delete.success' ) ] ) : $this->getErrorJsonResponse ( [ ], trans ( 'audio::audio.favourite_audios.delete.error' ) );
    }
}
