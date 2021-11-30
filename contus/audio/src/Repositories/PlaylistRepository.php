<?php

/**
 * Playlist Repository
 *
 * To manage the functionalities related to the Playlist module from Playlist Controller
 *
 * @name PlaylistRepository
 * @vendor Contus
 * @package Audio
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2019 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Repositories;

use Contus\Audio\Models\Playlist;
use Contus\Audio\Models\Audios;
use Contus\Audio\Models\Albums;
use Contus\Audio\Models\Artist;
use Contus\Audio\Models\AudioGenres;
use Contus\Audio\Models\AudioLanguageCategory;
use Contus\Audio\Models\AudioAnalytics;
use Contus\Audio\Traits\AudioHelperTrait;
use Contus\Base\Repository as BaseRepository;


class PlaylistRepository extends BaseRepository
{
    use AudioHelperTrait;
    
    /**
     * Construct method
     *
     * @vendor Contus
     *
     * @package Audio
     * @param Contus\Audio\Models\Playlist $palylist
     */
    public function __construct()
    {
        parent::__construct();
        $this->playlists = new Playlist();
         $this->albums = new Albums();
        $this->audios = new Audios();
        $this->artists = new Artist();
        $this->audio_language = new AudioLanguageCategory();
        $this->audioGenre = new AudioGenres();
        $this->trendingNow = new AudioAnalytics();
        $this->records_per_page = config('contus.audio.audio.record_per_page');
        $this->album_tracks_per_page = config('contus.audio.audio.album_tracks_per_page');
    }
 

    public function allPlaylists(){
        $playlists  = $this->playlists->orderBy('order', 'ASC');
        $resultQuery =  $playlists;
            $result['playlists'] = $resultQuery->paginate($this->records_per_page)->toArray();
        return $result;
    }


    /**
     * Method to get contents for album detail page
     *
     * @vendor Contus
     * @package Audio
     * @return array
     */
    public function playlistDetails()
    {
        $result = array();

        $this->setRules(['slug' => 'required']);
        $this->validate($this->request, $this->getRules());
        $slug = $this->request->slug;
        return app('cache')->tags([getCacheTag(), 'audio_albums', 'audios', 'audio_artists', 'audio_language_category'])->remember(getCacheKey() . '_album_detail_' . $slug, getCacheTime(), function () use ($slug) {
            $playlistBuilder = $this->playlists->where($this->getKeyPlaylistSlugorId(), $slug)->first();
            $playlistData = $playlistBuilder ?$playlistBuilder->playlistAudios()->get()->toArray():null;
            $playlist = $playlistBuilder ?$playlistBuilder->toArray():null;
            $playlist_id = $playlist['id'];
            (is_null($playlist)) ? $this->throwJsonResponse(false, 404, trans('audio::album.404_slug_response')) : '';
            $playlist['playlist_tracks'] = $playlistData;
            // $albumArtistId = $albumData->album_artist_id;
            $result['playlist_info'] = $playlist;
            $result['related_playlist'] = $this->getRelatedPlaylist($this->playlists, $playlist_id,6);
            return $result;
        });

    }
 
     /**
     * Repository function to get the artist related audio list
     *
     * @param integer $id
     * @return variable
     */

    /**
     * Method to return audio tracks suggestions based on the search term
     * 
     * @vendor Contus
     * 
     * @package Audio
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function searchAudioTracks(){
        $this->setRules(['search' => 'required', 'order'=>'sometimes|in:title', 'sort'=>'sometimes|in:asc,desc']);
        $this->validate($this->request, $this->getRules());
        $searchKey = $this->request->search;
        $audio =  $this->audios->active()->where ( 'job_status', 'Complete' )->where(function($query) use ($searchKey) {
            $query->orwhere('slug', 'like', '%'.$searchKey.'%')->orwhere('audio_title', 'like', '%'.$searchKey.'%');
        });
        $fields = 'audios.id, audios.audio_title, audios.slug';
        $audio->selectRaw($fields)->groupBy('audios.id');
        $audio->orderBy('id', 'desc');
        return $audio->paginate(config('access.perpage'));
    }
}
