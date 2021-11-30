<?php
/**
 * Favourite Playlist Repository
 *
 * To manage the functionalities related to the Customer favorite Playlist
 *
 * @name FavouritePlaylistRepository
 * @vendor Contus
 * @package Audio
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */

namespace Contus\Audio\Repositories;

use Contus\Base\Repository as BaseRepository;
use Contus\Customer\Models\Customer;
use Contus\Audio\Models\Playlist;
use Contus\Audio\Models\FavouritePlaylist;

class FavouritePlaylistRepository extends BaseRepository{
    /**
     * Class property to hold the key which hold the Favourite playlist object
     *
     * @var object
     */
    protected $customer;
    /**
     * Construct method
     */
    public function __construct(){
        parent::__construct();
        $this->customer = new Customer();
        $this->playlist = new Playlist();
        $this->favouritePlaylist = new FavouritePlaylist();
        $this->date = $this->customer->freshTimestamp();
        $this->records_per_page = config('contus.audio.audio.record_per_page');
    }
    /**
     * Method to handle the favourite audio of a customer
     *
     * @vendor Contus
     * @package Audio
     * @return boolean
     */
    public function addOrDeleteFavouritePlaylist(){
        $this->setRules(['slug' => 'required']);
        if ($this->_validate()) {
            $slugs = $this->request->slug;
            $explodeSlug = explode(',', $slugs);
            $playlistIds = $this->playlist->whereIn($this->getKeyPlaylistSlugorId(), $explodeSlug)->pluck('id')->toArray();
            if (count($playlistIds) > 0) {
                return ($this->request->isMethod('post'))?$this->addFavourite($playlistIds):$this->deleteFavourite($playlistIds);
            } else {
                return false;
            }
        }
    }
    /**
     * Method to add a playlist to customer favourites list
     * 
     * @vendor Contus
     * @package Audio
     * @param array $playlistIds
     * @return boolean
     */
    public function addFavourite($playlistIds){
        $customerId = authUser()->id;
        if(!empty($customerId)){
            authUser()->playlistFavourites()->attach($playlistIds, ['customer_id' => $customerId ,'created_at' => $this->date]);
            $this->playlist->whereIn('id',$playlistIds)->update(['updated_at' => date('Y-m-d H:i:s')]);
            app('cache')->tags('audio_playlists')->flush();
            return true;
        }else{
            return false;
        }
    }
    /**
     * Method to delete a playlist from customer favourites list
     * 
     * @vendor Contus
     * @package Audio
     * @param array $playlistIds
     * @return boolean
     */
    public function deleteFavourite($playlistIds){
        $customerId = authUser()->id;
        if(!empty($customerId)){
            authUser()->playlistFavourites()->detach($playlistIds);
            app('cache')->tags('audio_playlists')->flush();
            return true;
         }else{
            return false;
        }
     }
    /**
     * Get all Favourite playlist of a customer
     * 
     * @vendor Contus
     * @package Audio
     * @return array
     */
    public function getAllFavouritePlaylist(){
        $customerId = authUser()->id;
        if(!empty($customerId)) {
         return $this->playlist->whereHas('customerFavouritePlaylist' , function($query) use($customerId) {
            $query->where('customer_id',$customerId)->orderBy('_id','desc');
            })->orderBy('updated_at','desc')->paginate($this->records_per_page)->toArray();
        }
        return [];
    }
}