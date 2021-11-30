<?php
/**
 * AudioAdsRepository
 *
 * To manage the audio ad management such as create, edit and delete
 *
 * @name AudioAdsRepository
 * @version 1.0
 * @author Contus Team <developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Repositories;

use Contus\Base\Repository as BaseRepository;
use Contus\Audio\Models\AudioAds;
use Contus\Customer\Models\Subscribers;

class AudioAdsRepository extends BaseRepository{
    /**
     * Class construct method initialization
     */
    public function __construct(){
        parent::__construct();
        $this->audioAds = new AudioAds();
    }

    /**
     * Method to get audio ads details
     * 
     * @vendor Contus
     * @package Audio
     * @return array
     */
    public function getAudioAdsDetails(){        
        $customerId     = (!empty(authUser()->id)) ? authUser()->id : 0;        
        if($customerId === 0){            
            $result = $this->audioAds::select('id','ad_slug','audio_ad_audio_url AS hls_playlist_url','ad_image AS audio_thumbnail','ad_url','ad_audio_duration AS audio_duration')->inRandomOrder()->first();
        }else{
            $checkSubscriber = Subscribers::where('customer_id', $customerId)->where('is_active', 1)->first();
            if (!empty($checkSubscriber) && count($checkSubscriber->toArray()) > 0) {
                $result = null;
            }
            else{
                $result = $this->audioAds::select('id','ad_slug','audio_ad_audio_url AS hls_playlist_url','ad_image AS audio_thumbnail','ad_url','ad_audio_duration AS audio_duration')->inRandomOrder()->first();    
            }            
        }
        return $result;
    }
}