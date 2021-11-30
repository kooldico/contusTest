<?php
/**
 * AudioBaseController
 *
 * @name       AudioBaseController
 * @vendor     Contus
 * @package    Audio
 * @version    1.0
 * @author     Contus<developers@contus.in>
 * @copyright  Copyright (C) 2018 Contus. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Http\Controllers\Frontend;

use Contus\Base\Controller as BaseController;
use Illuminate\Http\Request;

class AudioBaseController extends BaseController {
   /**
     * Method to load the index of corresponding module
     * @vendor     Contus
     * @package    Audio
     * @return \Illuminate\Http\View
     */
    public function getIndex($route){
        $response = '';
        switch ($route) {
            case 'album':
                $response =  view ( 'audio::admin.albums.index' );      
            break;
            default:
            break;
        }
        return $response;
    }
    /**
     * Method to load the grid page of corresponding module
     * @vendor     Contus
     * @package    Audio
     * @return \Illuminate\Http\View
     */
    public function getGridlist($route){
        $response = '';
        switch ($route) {
            case 'albums':
                $response =  view ( 'audio::admin.albums.gridView' );      
            break;
            default:
            break;
        }
        return $response;
    }
    /**
     * Method to load the add form page of corresponding module
     * @vendor     Contus
     * @package    Audio
     * @return \Illuminate\Http\View
     */
    public function getAdd($route){
        $response = '';
        switch ($route) {
            case 'album':
                $response =  view ( 'audio::admin.albums.add' );      
            break;
            default:
            break;
        }
        return $response;
    }
    /**
     * Method to load the edit form page of corresponding module
     * @vendor     Contus
     * @package    Audio
     * @return \Illuminate\Http\View
     */
    public function getEdit($route){
        $response = '';
        switch ($route) {
            case 'albums':
                $response =  view ( 'audio::admin.albums.edit' );      
            break;
            default:
            break;
        }
        return $response;
    }

    public function getRadio(){
        return view('base::radio');
    }
}