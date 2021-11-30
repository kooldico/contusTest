<?php

/**
 * Artist Repository
 *
 * To manage the functionalities related to the Artists module from Artists Controller
 *
 * @name ArtistRepository
 * @vendor Contus
 * @package Artists
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2016 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Repositories;

use Contus\Base\Repository as BaseRepository;
use Contus\Audio\Models\Artist;
use Contus\Audio\Models\Albums;
use Contus\Audio\Traits\AudioHelperTrait;
use Contus\Audio\Models\AudioLanguageCategory;

class ArtistRepository extends BaseRepository{
    use AudioHelperTrait;

    /**
     * Class property to hold the key which hold the user object
     *
     * @var object
     */
    protected $_artist;
    /**
     * Construct method
     *
     * @vendor Contus
     *
     * @package Audio
     * @param Contus\Audio\Models\Artist $artist
     */
    public function __construct(){
        parent::__construct();
        $this->_artist = new Artist();
        $this->albums = new Albums();
        $this->records_per_page = config('contus.audio.audio.record_per_page');
        $this->audio_language = new AudioLanguageCategory();
    }
    /**
     * Method to get contents for artist detail page
     * 
     * @vendor Contus
     * @package Audio
     * @return array
     */
    public function artistDetails(){
        $result = array();
        $this->setRules(['slug' => 'required']);
        $this->validate($this->request, $this->getRules());
        $slug = $this->request->slug;
        return app('cache')->tags([getCacheTag(), 'audio_albums', 'audios', 'audio_artists', 'audio_language_category'])->remember(getCacheKey().'_artist_detail_'.$slug, getCacheTime(), function () use($slug) {
            $artistData = $this->_artist->selectRaw('*, id as artist_tracks')->where($this->getKeySlugorId(),$slug)->first();
            (is_null($artistData))?$this->throwJsonResponse(false, 404, trans('audio::album.404_slug_response')):'';
            $result['artist_info'] =  $artistData;
            /** To fetch related albums of the artist */
            $albumBuilder = $this->albums->with('albumTracks');
            $albumArtistId = $artistData->id;
            $result['related_albums'] = $this->getRelatedAlbums($albumBuilder,$albumArtistId);
            return $result;
        });
    }
    public function browseArtist()
    {
        $result = array();
        if ($this->request->has('language_id')) {
            $languageBuilder = $this->audio_language->selectRaw('*, id as artist_list');
            $languageBuilder = $languageBuilder->where('id', $this->request->language_id);
            $languageBuilder = $languageBuilder->get()->toArray();
            $result['artist_data'] = $languageBuilder[0]['artist_list'];
        } else {
            $result['languages'] = $this->audio_language->select('id', 'language_name')
                ->orderBy('order', 'ASC')
                ->orderBy('id', 'DESC')->get()->toArray();
            $albumBuilder = $this->_artist;
            $resultQuery = ($this->request->has('search'))
            ? $this->browseArtistAlphanumericWise($albumBuilder, $this->request->search)
            : $albumBuilder;
            $result['artist_data'] = $resultQuery->paginate($this->records_per_page)->toArray();
        }

        return $result;
    }
}
