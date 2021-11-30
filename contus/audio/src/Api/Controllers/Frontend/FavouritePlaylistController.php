<?php

/**
 * Customer Favourite playlist Controller
 *
 * @name Customer FavouritePlaylistController
 * @vendor Contus
 * @package Audio
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Api\Controllers\Frontend;

use Contus\Base\ApiController;
use Contus\Audio\Repositories\FavouritePlaylistRepository;
use Contus\Customer\Models\Customer;
use Illuminate\Http\Request;

class FavouritePlaylistController extends ApiController {

    /**
     * Construct method
     */
    public function __construct() {
        parent::__construct ();
        $this->repository = new FavouritePlaylistRepository();
        $this->repository->setRequestType ( static::REQUEST_TYPE );
        $this->repoArray = ['repository'];
    }
    /**
     * Display a listing of the resource.
     *
     * @vendor Contus
     * @package Cusomer
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $data = $this->repository->getAllFavouritePlaylist ();
        return ($data) ? $this->getSuccessJsonResponse ( [ 'response' => $data,'message'=>trans('audio::playlist.favourite_playlist.fetch_record.success') ] ) : $this->getErrorJsonResponse ( [ ], trans ( 'audio::playlist.favourite_playlist.fetch_record.success' ) );
    }
    /**
     * Store a newly created resource in storage.
     *
     * @vendor Contus
     * @package Cusomer
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $data = $this->repository->addOrDeleteFavouritePlaylist();
        return ($data) ? $this->getSuccessJsonResponse ( [ 'message' => trans ( 'audio::playlist.favourite_playlist.add.success' ) ] ) : $this->getErrorJsonResponse ( [ ], trans ( 'audio::playlist.favourite_playlist.add.error' ) );
    }
    /**
     * Remove the specified resource from storage.
     *
     * @vendor Contus
     * @package Cusomer
     * @return \Illuminate\Http\Response
     */
    public function destroy() {
        $data = $this->repository->addOrDeleteFavouritePlaylist();
        return ($data) ? $this->getSuccessJsonResponse ( [ 'message' => trans ( 'audio::playlist.favourite_playlist.delete.success' ) ] ) : $this->getErrorJsonResponse ( [ ], trans ( 'audio::playlist.favourite_playlist.delete.error' ) );
    }
}
