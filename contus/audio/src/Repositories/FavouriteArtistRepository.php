<?php
/**
 * Favourite Artist Repository
 *
 * To manage the functionalities related to the Customer favorite artists
 *
 * @name FavouriteArtistRepository
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
use Contus\Audio\Models\Artist;
use Contus\Audio\Models\FavouriteArtist;

class FavouriteArtistRepository extends BaseRepository{
    /**
     * Class property to hold the key which hold the Favourite artist object
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
        $this->artist = new Artist();
        $this->favouriteAlbum = new FavouriteArtist();
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
    public function addOrDeleteFavouriteArtist(){
        $this->setRules(['slug' => 'required']);
        if ($this->_validate()) {
            $slugs = $this->request->slug;
            $explodeSlug = explode(',', $slugs);
            $artistIds = $this->artist->whereIn($this->getKeySlugorId(), $explodeSlug)->pluck('id')->toArray();
            if (count($artistIds) > 0) {
                return ($this->request->isMethod('post'))?$this->addFavourite($artistIds):$this->deleteFavourite($artistIds);
            } else {
                return false;
            }
        }
    }
    /**
     * Method to add a album to customer favourites list
     * 
     * @vendor Contus
     * @package Audio
     * @param array $albumIds
     * @return boolean
     */
    public function addFavourite($artistIds){
        $customerId = authUser()->id;
        if(!empty($customerId)){
            authUser()->artistFavourites()->attach($artistIds, ['customer_id' => $customerId ,'created_at' => $this->date]);
            $this->albums->whereIn('id',$artistIds)->update(['updated_at' => date('Y-m-d H:i:s')]);
            app('cache')->tags('audio_albums')->flush();
            return true;
        }else{
            return false;
        }
    }
    /**
     * Method to delete a album from customer favourites list
     * 
     * @vendor Contus
     * @package Audio
     * @param array $albumIds
     * @return boolean
     */
    public function deleteFavourite($artistIds){
        $customerId = authUser()->id;
        if(!empty($customerId)){
            authUser()->artistFavourites()->detach($artistIds);
            app('cache')->tags('audio_artist')->flush();
            return true;
         }else{
            return false;
        }
     }
    /**
     * Get all Favourite albums of a customer
     * 
     * @vendor Contus
     * @package Audio
     * @return array
     */
    public function getAllFavouriteArtist(){
        $customerId = authUser()->id;
        if(!empty($customerId)) {
         return $this->artist->whereHas('customerFavouriteArtists' , function($query) use($customerId) {
            $query->where('customer_id',$customerId)->orderBy('_id','desc');
            })->orderBy('updated_at','desc')->paginate($this->records_per_page)->toArray();
        }
        return [];
    }
}