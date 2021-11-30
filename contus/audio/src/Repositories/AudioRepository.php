<?php
/**
 * AudioRepository
 *
 * To manage the audio management such as create, edit and delete
 *
 * @name AudioRepository
 * @version 1.0
 * @author Contus Team <developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Repositories;

use Contus\Base\Repository as BaseRepository;
use Contus\Audio\Models\Audios;
use Contus\Audio\Models\AudioPlayHistory;
use Contus\Audio\Models\AudioAnalytics;
use Contus\Audio\Traits\AudioHelperTrait;
use Location;
use Carbon\Carbon;

class AudioRepository extends BaseRepository{
    use AudioHelperTrait;
    /**
     * Class construct method initialization
     */
    public function __construct(){
        parent::__construct();
        $this->audios = new Audios();
        $this->audiosPlayHistory = new AudioPlayHistory();
        $this->audiosAnalytics = new AudioAnalytics();
        $this->IP_address = getIPAddress();
        $this->records_per_page = config('contus.audio.audio.record_per_page');
    }
    /**
     * Method to track audio play count, history and analytics
     * 
     * @vendor Contus
     * @package Audio
     * @return boolean
     */
    public function tracksPlayHistory(){
        $this->setRules(['audio' => 'required']);
        $this->validate($this->request, $this->getRules());
        $audioInput = $this->request->audio;
        $audioId = $this->getAudioIds($audioInput,$this->audios);
        if(!empty($audioId)){
            try{
                /** Increment play count of the audio */
                $this->audios->where('id',$audioId)->increment('play_count');
                /** Insert tracks played history */
                $this->audioPlayHistory($audioId);
                /** Track audio analytics */
                $this->audioAnalytics($audioId);
                return true;
            } catch (\Exception $e) {
                $this->throwJsonResponse(false, 500,  $e->getMessage());
            }
        } else {
            $this->throwJsonResponse(false, 500,  trans( 'audio::audio.audio_playlists.audio_not_exists'));
        }
    }
    /**
     * Method to store the audio playes history based on customer and IP
     * 
     * @vendor Contus
     * @package Audio
     * @param int $audioId
     * @return boolean
     */
    public function audioPlayHistory($audioId){
        $customerId = (!empty(authUser()->id)) ? authUser()->id : 0;
        $IPAddress = $this->IP_address ?: 'unknown';
        $this->audiosPlayHistory = $this->audiosPlayHistory->where('audio_id',$audioId);
        $this->audiosPlayHistory = (!empty(authUser()->id)) ? $this->audiosPlayHistory->where('customer_id',  authUser()->id)->first()
                                    :$this->audiosPlayHistory->where('ip_address',  $this->IP_address)->first();
        if (is_object($this->audiosPlayHistory) && !empty($this->audiosPlayHistory->id)) { 
            $this->audiosPlayHistory->updated_at = Carbon::now()->toDateTimeString();
            $this->audiosPlayHistory->is_active = 1;
            $this->audiosPlayHistory->save();
        } else {
            $albumId = $this->getAudioDataFromID($this->audios, $audioId, 'album_id');
            $playHistory = new AudioPlayHistory();
            $playHistory->audio_id = $audioId;
            $playHistory->album_id = $albumId->album_id;
            $playHistory->customer_id = $customerId;
            $playHistory->ip_address = $IPAddress;
            $playHistory->is_active = 1;
            $playHistory->save();
        }
    }
    /**
     * Method to store audio analytics
     * 
     * @vendor Contus
     * @package Audio
     * @param int $audioId
     * @return boolean
     */
    public function audioAnalytics($audioId){
        $audioAnalyticsData = array();
        $customerId = (!empty(authUser()->id)) ? authUser()->id : 0;
        $IPAddress = $this->IP_address ?: 'unknown';
        /** This is call to a method to get the current logged in user country based on the IP */
        // $getcurrentIPLocation = Location::get($this->IP_address);
        // $getcurrentIPLocationFlag = (isset($getcurrentIPLocation->countryName))?$getcurrentIPLocation->countryName:'unknown';
         /** Call to method to get the platform (Web, ios or android) of the request */
         $platform = getPlatform();
         $audioTitle = $this->getAudioDataFromID($this->audios, $audioId, 'audio_title');
         $albumId = $this->getAudioDataFromID($this->audios, $audioId, 'album_id');
         $todayDate = \Carbon\Carbon::now()->toDateString(); 
         $audioAnalyticsData = [
            'audio_id'=>$audioId,
            'audio_title'=>$audioTitle->audio_title,
            'album_id'=> $albumId->album_id,
            'customer_id' => $customerId,
            // 'country' => $getcurrentIPLocationFlag,
            'platform' => $platform,
            'ip_address' => $IPAddress,
            'listened_date' => $todayDate,
        ];
        try{
            $audioAnalytics = new AudioAnalytics();
            $audioAnalytics->fill($audioAnalyticsData);
            return ($audioAnalytics->save())?1:0;
        } catch(Exception $e) {
            app('log')->error(' ###File : $e->getFile() ##Message : The audio analytics insertion failed  ' .' #Error : ' . $e->getMessage());
        }
    }
    /**
     * Method to fetch customer audio history
     * 
     * @vendor Contus
     * @package Audio
     * @return array
     */
    public function getCustomerPlayedAudioHistory(){
        $customerId     = (!empty(authUser()->id)) ? authUser()->id : 0;
        return $this->audios->whereHas('customerPlayedAudioHistory',function( $query ) use( $customerId ){
                    $query->where('customer_id', $customerId)->where('is_active',1);
                })
                ->orderBy('updated_at', 'desc')
                ->paginate($this->records_per_page)->toArray();
    }
    /**
     * Method to delete the respective customer's audio history
     * 
     * @vendor Contus
     * @package Audio
     * @return boolean
     */
    public function clearCustomerAudioHistory(){
        $responseFlag = false;
        $customerId     = (!empty(authUser()->id)) ? authUser()->id : 0;
        if($this->request->has('audio_id')){
            $audioInput = $this->request->audio_id;
            $audioId = (!empty($audioInput))?$this->getAudioIds($audioInput,$this->audios)
                        : $this->throwJsonResponse(false, 500,  trans( 'audio::audio.empty_request_varaible'));
            (!empty($audioId))?$this->audiosPlayHistory->whereIn('audio_id',[$audioId])->where('customer_id', $customerId)->update(['is_active' => 0])
                            : $this->throwJsonResponse(false, 500,  trans( 'audio::audio.audio_playlists.audio_not_exists'));
            $responseFlag = true;
        } else {
            $this->audiosPlayHistory->where('customer_id', $customerId)->update(['is_active' => 0]); 
            $responseFlag = true;
        }
        return $responseFlag;
    }
    /**
     * Method to get single track details
     * 
     * @vendor Contus
     * @package Audio
     * @return array
     */
    public function getAudioDetails(){
        $this->setRules(['slug' => 'required']);
        $this->validate($this->request, $this->getRules());
        $audioInput = $this->request->slug;
        return app('cache')->tags([getCacheTag(), 'audio_albums', 'audios', 'audio_artists', 'audio_language_category'])->remember(getCacheKey().'_audio_detail_'.$audioInput, getCacheTime(), function () use($audioInput) {
            $result = array();
            $audioId = $this->getAudioIds($audioInput,$this->audios);
            $albumId = $this->getAudioDataFromID($this->audios,$audioId, 'album_id');
            $result['track_details'] = $this->audios->select('*','id as passphrase')->where('id',$audioId)->get()->toArray();
            $result['related_tracks'] = $this->audios->where('album_id','=',$albumId->album_id)->where('id','!=',$audioId)->paginate($this->records_per_page)->toArray();
            return $result;
        
        });
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
        return $customerId;
    }
}