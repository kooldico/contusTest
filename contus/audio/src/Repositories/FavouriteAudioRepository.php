<?php
/**
 * Favourite Audio Repository
 *
 * To manage the functionalities related to the Customer favorite audios and albums
 *
 * @name FavouriteAudioRepository
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
use Contus\Audio\Models\Audios;
use Contus\Audio\Models\FavouriteAudio;

class FavouriteAudioRepository extends BaseRepository{

    /**
     * Class property to hold the key which hold the Favourite audio object
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
        $this->audios = new Audios();
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
    public function addOrDeleteFavouriteAudio(){
        $this->setRules(['slug' => 'required']);
        if ($this->_validate()) {
            $slugs = $this->request->slug;
            $explodeSlug = explode(',', $slugs);
            $audioIds = $this->audios->whereIn($this->getKeySlugorId(), $explodeSlug)->pluck('id')->toArray();
            if (count($audioIds) > 0) {
                return ($this->request->isMethod('post'))?$this->addFavourite($audioIds):$this->deleteFavourite($audioIds);
            } else {
                return false;
            }
        }
    }
    /**
     * Method to add a audio to customer favourites list
     * 
     * @vendor Contus
     * @package Audio
     * @param array $audioIds
     * @return boolean
     */
    public function addFavourite($audioIds){
        $customerId = authUser()->id;
        if(!empty($customerId)){
            authUser()->audioFavourites()->attach($audioIds, ['customer_id' => $customerId ,'created_at' => $this->date]);
            $this->audios->whereIn('id',$audioIds)->update(['updated_at' => date('Y-m-d H:i:s')]);
            app('cache')->tags('audios')->flush();
            return true;
        }else{
            return false;
        }
    }
    /**
     * Method to delete a audio from customer favourites list
     * 
     * @vendor Contus
     * @package Audio
     * @param array $audioIds
     * @return boolean
     */
    public function deleteFavourite($audioIds){
        $customerId = authUser()->id;
        if(!empty($customerId)){
            authUser()->audioFavourites()->detach($audioIds);
            app('cache')->tags('audios')->flush();
            return true;
         }else{
            return false;
        }
     }
    /**
     * Get all Favourite Audios of a customer
     * 
     * @vendor Contus
     * @package Audio
     * @return array
     */
    public function getAllFavouriteAudios(){
        $customerId = authUser()->id;
        if(!empty($customerId)) {
         return $this->audios->whereHas('customerFavouriteTracks' , function($query) use($customerId) {
            $query->where('customer_id',$customerId)->orderBy('_id','desc');
            })->orderBy('updated_at','desc')->paginate($this->records_per_page)->toArray();
        }
        return [];
    }
}