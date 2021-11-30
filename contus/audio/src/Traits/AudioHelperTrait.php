<?php
/**
 * Audio Helper trait
 *
 * To manage the common methods among the audio feature
 *
 * @name AudioHelperTrait
 * @vendor Contus
 * @package Audio
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Traits;

use DB;
use Carbon\Carbon;
use DateTime;
use MongoDB\BSON\UTCDateTime;

trait AudioHelperTrait{
    /**
     * Method to get audio ids when request from web is based on slug
     * 
     * @vendor Contus
     * @package Audio
     * @param int $audioInput
     * @param \Illuminate\Database\Eloquent\Builder $builder 
     * @return int
     */
    public function getAudioIds($audioInput, $builder){
        if(!empty($audioInput)){
            if(!isMobile()) {
                $audioInfo = $builder->select('id')->where('slug', $audioInput)->first();
                $audioId = (!empty($audioInfo)) ? $audioInfo->id : $this->throwJsonResponse(false, 500, trans( 'audio::audio.audio_playlists.audio_not_exists' ));
            }else{
                $audioId = $audioInput;
            }
        }else{
            $this->throwJsonResponse(false, 500, trans( 'audio::audio.audio_playlists.audio_not_exists' ));
        }
        return $audioId;
    }
    /**
     * Method to get the related albums for artist and album detail page
     * 
     * @vendor Contus
     * @package Audio
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param int $albumArtistId
     * @param int $excludeID
     * @return array
     */
    public function getRelatedAlbums($builder,$albumArtistId, $excludeID = null){
        $excId = !empty($excludeID) ? $excludeID : 0;
        return app('cache')->tags([getCacheTag(), 'audio_albums', 'audios', 'audio_artists', 'audio_language_category'])->remember(getCacheKey().'_related_albums_'.$albumArtistId.'_'.$excId, getCacheTime(), function () use($builder,$albumArtistId, $excludeID) {
            $result = $builder->where('album_artist_id',$albumArtistId);
            $result = (!empty($excludeID))?$result->where('id','!=',$excludeID):$result;
            return $result->paginate($this->records_per_page)->toArray();
        });
    }
    /**
     * Method to get the related albums for artist and album detail page
     * 
     * @vendor Contus
     * @package Audio
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param int $albumArtistId
     * @param int $excludeID
     * @return array
     */
    public function getRelatedPlaylist($builder,$playlistId,$paginate){
        $excId = !empty($playlistId) ? $playlistId : 0;
        return app('cache')->tags([getCacheTag(), 'audio_albums', 'audios', 'audio_artists', 'audio_language_category'])->remember(getCacheKey().'_related_playlists_'.$playlistId.'_'.$excId, getCacheTime(), function () use($builder,$playlistId,$excId,$paginate) {
            $result = $builder->where('id',$playlistId);
            $result = (!empty($excId))?$builder->whereNotIn('id',[$playlistId]):$result;
            return $result->inRandomOrder()->paginate($paginate)->toArray();
        });
    }
    /**
     * Method to get the audio data based on its id
     * 
     * @vendor Contus
     * @package Audio
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param int $audioId
     * @param string $columnName
     * @return array
     */
    public function getAudioDataFromID($builder,$audioId, $columnName){
        return $builder->select($columnName)->where('id',$audioId)->first();
    }
    /**
     * Method to get popular album and artist ids from audios play count
     * 
     * @vendor Contus
     * @package Audio
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string $columnName
     * @return array
     */
    public function getPopularIdFromAudioPlayCount($builder, $columnName){
        return app('cache')->tags([getCacheTag(), 'audios', 'audio_artists', 'audio_language_category'])->remember(getCacheKey().'_popular_audio_with_play_count_'.$columnName, getCacheTime(), function () use($builder, $columnName) {
            $result = array();
            $popularIds = $builder->select(DB::raw("MAX(play_count) as maxaudiocount"),$columnName)
            ->groupBy($columnName)
            ->orderBy(DB::raw("maxaudiocount"),'desc')->pluck($columnName)->toArray();
            $placeholders = implode(',',array_fill(0, count($popularIds), '?'));
            $result['trending_id'] = $popularIds;
            $result['placeholders'] = $placeholders;
            return $result;
        });
    }
    /**
     * Method to form the query builder based on alphabets and number to browse albums
     * 
     * @vendor Contus
     * @package Audio
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string $searchValue
     * @return \Illuminate\Database\Eloquent\Builder $builder
     */
    public function browseAlbumsAlphanumericWise($builder, $searchValue){
        if(!$searchValue && !$this->request->genre && !$this->request->artist && !$this->request->album){
            $resultQuery = $builder;
        } else {
            $resultQuery = $builder->select('album_name','album_name_hindi','album_thumbnail','artist_name','artist_name_hindi','id','slug')->where(function($q)  use ($searchValue) {
                if($searchValue){
                    $q->orWhere('album_name','like','%'.$searchValue.'%')
                    ->orWhere('artist_name','like','%'.$searchValue.'%')
                    ->orWhere('genre_name','like','%'.$searchValue.'%');
                }
            })->where(function($q)  use ($searchValue) {
                if($this->request->genre && !$this->request->artist && !$this->request->album){
                    $q->whereIn('genre_name',$this->request->genre);
                }
                if($this->request->artist && !$this->request->genre && !$this->request->album){
                    $q->whereIn('artist_name',$this->request->artist);
                }
                if(!$this->request->genre && !$this->request->artist && $this->request->album){
                    $q->whereIn('album_name',$this->request->album);
                }
                if($this->request->artist && $this->request->genre && !$this->request->album){
                    $q->whereIn('artist_name',$this->request->artist)
                    ->whereIn('genre_name',$this->request->genre);
                }
                if(!$this->request->artist && $this->request->genre && $this->request->album){
                    $q->whereIn('album_name',$this->request->album)
                    ->whereIn('genre_name',$this->request->genre);
                }
                if($this->request->artist && !$this->request->genre && $this->request->album){
                    $q->whereIn('artist_name',$this->request->artist)
                    ->whereIn('album_name',$this->request->album);
                }

                if($this->request->artist && $this->request->genre && $this->request->album){
                    $q->whereIn('artist_name',$this->request->artist)
                    ->whereIn('genre_name',$this->request->genre)
                    ->whereIn('album_name',$this->request->album);
                }
            })->orderBy('album_order')->groupBy('id')->paginate(50);
        }
        return $resultQuery;
    }
    public function browseArtistAlphanumericWise($builder, $searchValue){
        switch( $searchValue ){
            case 'all':
                $resultQuery = $builder;
            break;
            case 'number':
                $resultQuery  = $builder->whereRaw("artist_name REGEXP '^[0-9]+'");
            break;
            default:
                $resultQuery  = $builder->where('artist_name','LIKE',$searchValue.'%');
            break;
        }
        return $resultQuery;
    }
    /**
     * Method to get popular albums based on the most favourited album count
     * 
     * @vendor Contus
     * @package Audio
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string $columnName
     * @return array
     */
    public function getMostFavouritedAlbum($builder, $columnName){
        $aggregate = [
            [ 
                '$group'=> [
                '_id'=> '$album_id', 
                'mostFavAlbum' => [ '$max'=> '$album_id' ]
                ]
            ],
            [ '$sort' => ['_id'=> -1] ],
            [ '$project'=> [ 'mostFavAlbum'=> 1]]
        ];
        $mostFavAlbums = $builder::raw(function ($collection) use ($aggregate) {
            return $collection->aggregate($aggregate, ["allowDiskUse" => true]);
        });
        $mostFavAlbums = $mostFavAlbums->pluck('mostFavAlbum')->toArray();
        $placeholders = implode(',',array_fill(0, count($mostFavAlbums), '?'));
        $result['popular_id']   =  $mostFavAlbums;
        $result['placeholders'] = $placeholders;
        return $result;
    }

    public function getTrendingNowSongId($builder, $columnName){
        $date = \Carbon\Carbon::today()->subDays(1)->toDateString();
        $aggregate = [
            ['$match' => [
                'listened_date' => [
                        '$gte' =>$date, //have to pass  date
                    ],
                ]
            ],
            [ 
                '$group'=> [
                    '_id' => '$audio_id',
                    'trendNowSong' => [ '$sum'=> 1 ],
                ]
            ],
            [ '$sort'=> ['trendNowSong'=> -1] ],
            [ '$project'=> [ 'trendNowSong'=> 1]],
        ];
        $trendingNowIds = $builder::raw(function ($collection) use ($aggregate) {
            return $collection->aggregate($aggregate, ["allowDiskUse" => true]);
        });
        $trendingNowIds =  $trendingNowIds->pluck('_id')->toArray();
        $placeholders = implode(',',array_fill(0, count($trendingNowIds), '?'));
        $result['trending_id'] = $trendingNowIds;
        $result['placeholders'] = $placeholders;
        return $result;
    }

    public function getTrendingWeekSongId($builder, $columnName){
        $date = \Carbon\Carbon::today()->subDays(7)->toDateString();
        $aggregate = [
            ['$match' => [
                'listened_date' => [
                        '$gte' =>$date, //have to pass  date
                    ],
                ]
            ],
            [ 
                '$group'=> [
                    '_id' => '$audio_id',
                    'trendNowSong' => [ '$sum'=> 1 ],
                ]
            ],
            [ '$sort'=> ['trendNowSong'=> 1] ],
            [ '$project'=> [ 'trendNowSong'=> 1]],
        ];
        $trendingNowIds = $builder::raw(function ($collection) use ($aggregate) {
            return $collection->aggregate($aggregate, ["allowDiskUse" => true]);
        });
        $trendingNowIds =  $trendingNowIds->pluck('_id')->toArray();
        $placeholders = implode(',',array_fill(0, count($trendingNowIds), '?'));
        $result['trending_id'] = $trendingNowIds;
        $result['placeholders'] = $placeholders;
        return $result;
    }

    public function getWeeklyTopFifteenId($builder, $columnName){
        $date = \Carbon\Carbon::today()->subDays(7)->toDateString();        
        $aggregate = [
            ['$match' => [
                'listened_date' => [
                        '$gte' =>$date, //have to pass  date
                    ],
                ]
            ],
            [ 
                '$group'=> [
                    '_id' => '$audio_id',
                    'weeklyTopSong' => [ '$sum'=> 1 ],
                ]
            ],
            [ '$sort'=> ['weeklyTopSong'=> -1] ],
            [ '$project'=> [ 'weeklyTopSong'=> 1]],
        ];
        $weeklyTopFifteenIds = $builder::raw(function ($collection) use ($aggregate) {
            return $collection->aggregate($aggregate, ["allowDiskUse" => true]);
        });
        $weeklyTopFifteenIds =  $weeklyTopFifteenIds->pluck('_id')->toArray();
        $placeholders = implode(',',array_fill(0, count($weeklyTopFifteenIds), '?'));
        $result['weekly_top_id'] = $weeklyTopFifteenIds;
        $result['placeholders'] = $placeholders;
        return $result;
    }
}