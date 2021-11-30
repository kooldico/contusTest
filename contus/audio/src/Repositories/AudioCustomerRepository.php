<?php

/**
 * Audio Customer Repository
 *
 * To manage the functionalities related to the audio product customers
 *
 * @name AudioCustomerRepository
 * @vendor Contus
 * @package Artists
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2016 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Repositories;

use Contus\Base\Repository as BaseRepository;
use Contus\Audio\Models\Albums;
use Contus\Audio\Models\Audios;
use Contus\Audio\Models\FavouriteAlbum;
use Contus\Audio\Models\AudioPlaylist\CustomerPlaylist;
use Contus\Audio\Models\AudioPlayHistory;

class AudioCustomerRepository extends BaseRepository{
    /**
     * Construct method
     */
    public function __construct(){
        parent::__construct();
        $this->favouriteAlbum = new FavouriteAlbum();
        $this->albums = new Albums();
        $this->audios = new Audios();
        $this->customerAudioPlaylist = new CustomerPlaylist();
        $this->customerPlayedAudioHistory = new AudioPlayHistory();
        $this->records_per_page = config('contus.audio.audio.record_per_page');
    }
    /**
     * Method to customers favourite, playlist and recently played counts
     * 
     * @Vendor Contus
     * @package Audio
     * @return array
     */
    public function fetchCustomerProfileData(){
        $data = array();
        $customerId = authUser()->id;
        $data['favourite_album_count'] = $this->albums->whereHas('customerFavouriteAlbums' , function($query) use($customerId) {
                                            $query->where('customer_id',$customerId)->orderBy('_id','desc');
                                        })->count();
        $data['favourite_audio_count'] = $this->audios->whereHas('customerFavouriteTracks' , function($query) use($customerId) {
                                            $query->where('customer_id',$customerId)->orderBy('_id','desc');
                                        })->count();
        $data['playlist_count'] = $this->customerAudioPlaylist->where('customer_id', $customerId)->count();
        $data['recently_played_count'] = $this->audios->whereHas('customerPlayedAudioHistory',function( $query ) use( $customerId ){
                                                $query->where('customer_id', $customerId)->where('is_active',1);
                                        })->count();
       return $data;
    }
}
