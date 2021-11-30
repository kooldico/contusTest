<?php

/**
 * Artists Controller
 *
 * To manage the Artists such as create, edit and delete
 *
 * @name       Artists Controller
 * @version    1.0
 * @author     Contus Team <developers@contus.in>
 * @copyright  Copyright (C) 2016 Contus. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Http\Controllers\Frontend;

use Contus\Audio\Repositories\ArtistRepository;
use Contus\Base\Controller as BaseController;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;

class ArtistController extends BaseController{
  /**
   * Construct method
   */
  public function __construct(ArtistRepository $artistRepository){
    parent::__construct();
    $this->_artistRepository = $artistRepository;
    $this->_artistRepository->setRequestType(static::REQUEST_TYPE);
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\View
   */
  public function getIndex(){
    return view('audio::admin.artists.index');
  }

  /**
   * get Grid template
   *
   * @return \Illuminate\Http\View
   */
  public function getGridlist(){
    return view('audio::admin.artists.gridView');
  }
  /**
   * Function to get list of artists with their hierarchy.
   */
  public function getArtistList(){
    return $this->_artistRepository->getAllArtistList();
  }

}
