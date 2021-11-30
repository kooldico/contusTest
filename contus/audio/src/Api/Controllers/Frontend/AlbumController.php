<?php

/**
 * AlbumController
 *
 * To manage the audio album management such as create, edit and delete
 *
 * @name AlbumController
 * @version 1.0
 * @author Contus Team <developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Api\Controllers\Frontend;

use Contus\Audio\Repositories\AlbumRepository;
use Contus\Base\ApiController;
use Illuminate\Http\Request;

class AlbumController extends ApiController
{
    /**
     * Class construct method initialization
     */
    public function __construct()
    {
        parent::__construct();
        $this->repository = new AlbumRepository();
    }
    /**
     * Method to get homepage banner section contents
     *
     * @vendor contus
     * @return Illuminate\Http\Response
     */
    public function getHomepageBanner()
    {
        $bannerContent = array();
        $bannerContent['banner'] = $this->repository->getAlbumsByType('banner');
        $bannerContent['new'] = $this->repository->getAlbumsByType('new');
        return (!empty($bannerContent)) ? $this->getSuccessJsonResponse(['response' => $bannerContent], trans('audio::album.record_fetched_success'))
        : $this->getErrorJsonResponse([], trans('audio::album.record_fetched_error'));
    }
    /**
     * Method to get homepage section
     *
     * @vendor contus
     * @return Illuminate\Http\Response
     */
    public function getHomepageContents()
    {
        $homepageContent = $result = array();
        $homepageContent['trending_now'] = $this->repository->getAlbumsByType('trending_now');
        $homepageContent['weekly_top'] = $this->repository->getAlbumsByType('weekly_top');
        $homepageContent['new'] = $this->repository->getAlbumsByType('new');
        $homepageContent['featured_artists'] = $this->repository->getFeaturedArtists();
        $result['home_content'] = array_values($homepageContent);
        return (!empty($homepageContent)) ? $this->getSuccessJsonResponse(['response' => $result], trans('audio::album.record_fetched_success'))
        : $this->getErrorJsonResponse([], trans('audio::album.record_fetched_error'));
    }
    /**
     * Method to get homepage new created section
     *
     * @vendor contus
     * @return Illuminate\Http\Response
     */
    public function getHomepageContentsNew()
    {
        $homepageContent = $result = array();
        $homepageContent['new'] = $this->repository->getAlbumsByType('new');
        $result['home_content'] = array_values($homepageContent);
        return (!empty($homepageContent)) ? $this->getSuccessJsonResponse(['response' => $result], trans('audio::album.record_fetched_success'))
        : $this->getErrorJsonResponse([], trans('audio::album.record_fetched_error'));
    }
    /**
     * Method to get homepage section
     *
     * @vendor contus
     * @return Illuminate\Http\Response
     */
    public function getHomepageMoreContents()
    {
        $homepageContent = $result = array();
        $homepageContent['trending_now'] = $this->repository->getAlbumsByType('trending_now');
        $homepageContent['weekly_top'] = $this->repository->getAlbumsByType('weekly_top');
        if($homepageContent['trending_now']['total'] === 0){            
            $homepageContent['trending_now'] = $homepageContent['weekly_top'];
            $homepageContent['trending_now']['data'] = array_reverse($homepageContent['trending_now']['data']);
            $homepageContent['trending_now']['category_name'] = 'Trending Now';
            $homepageContent['trending_now']['type'] = 'trending_now';
        }
        $homepageContent['featured_artists'] = $this->repository->getFeaturedArtists();
        $result['home_content'] = array_values($homepageContent);
        return (!empty($homepageContent)) ? $this->getSuccessJsonResponse(['response' => $result], trans('audio::album.record_fetched_success'))
        : $this->getErrorJsonResponse([], trans('audio::album.record_fetched_error'));
    }
    /**
     * Method to get homepage section
     *
     * @vendor contus
     * @return Illuminate\Http\Response
     */
    public function getSeeAllHomepage()
    {
        $type = $this->request->type;
        switch ($type) {
            case 'featured_artists':
                $data = $this->repository->getFeaturedArtists();
                break;
            case 'browse':
                $data = $this->repository->browseInfo();
                break;
            default:
                $data = $this->repository->getAlbumsByType($type);
                if($data['type'] === 'trending_now' && $data['total'] === 0) {
                    $type = 'weekly_top';
                    $data = $this->repository->getAlbumsByType($type);
                    $data['category_name'] = 'Trending Now';
                    $data['type'] = 'trending_now';
                }
                break;
        }
        return (!empty($data)) ? $this->getSuccessJsonResponse(['response' => $data], trans('audio::album.record_fetched_success'))
        : $this->getErrorJsonResponse([], trans('audio::album.record_fetched_error'));

    }
    /**
     * Method to get contents for album detail page
     *
     * @vendor Contus
     * @package Audio
     * @return Illuminate\Http\Response
     */
    public function getalbumDetailPageContents()
    {
        $data = $response = array();
        $response = $this->repository->albumDetails();
        $data['album_info'] = $response['album_info'];
        $data['related_albums'] = $response['related_albums'];
        return (!empty($data)) ? $this->getSuccessJsonResponse(['response' => $data], trans('audio::album.record_fetched_success'))
        : $this->getErrorJsonResponse([], trans('audio::album.record_fetched_error'));
    }

    public function getalbumDetailPageContentsNew(){
        $data = $response = array();
        $response = $this->repository->albumDetailsNew();
        $data['album_info'] = $response['album_info'];
        $data['related_albums'] = $response['related_albums'];
        return (!empty($data)) ? $this->getSuccessJsonResponse(['response' => $data], trans('audio::album.record_fetched_success'))
        : $this->getErrorJsonResponse([], trans('audio::album.record_fetched_error'));
    }

    public function getAlbumAudio()
    {
        $data = $this->repository->getAlbumAudio();
        return $this->getSuccessJsonResponse(['response' => $data], trans('audio::album.record_fetched_success'));
    }
    /**
     * Method to get the list of albums for browse menu
     *
     * @vendor Contus
     * @package Audio
     * @return Illuminate\Http\Response
     */
    public function browseAlbums()
    {
        $albums = $this->repository->browseInfo();
        $result['browse_data'] = $albums;
        return (!empty($albums)) ? $this->getSuccessJsonResponse(['response' => $result], trans('audio::album.record_fetched_success'))
        : $this->getErrorJsonResponse([], trans('audio::album.record_fetched_error'));
    }
    /**
     * Method to get the list of albums audio tracks
     *
     * @vendor Contus
     * @package Audio
     * @return Illuminate\Http\Response
     */
    public function getAlbumTracks()
    {
        $audios = $this->repository->albumAudioTracks();
        $result['audio_info'] = $audios;
        return (!empty($audios)) ? $this->getSuccessJsonResponse(['response' => $result], trans('audio::album.record_fetched_success'))
        : $this->getErrorJsonResponse([], trans('audio::album.record_fetched_error'));
    }
    /**
     * Method to get the related albums more content on slider
     *
     * @vendor Contus
     * @package Audio
     * @return Illuminate\Http\Response
     */
    public function getmoreRelatedAlbums()
    {
        $data = $this->repository->getMoreRelatedAlbums();
        return (!empty($data)) ? $this->getSuccessJsonResponse(['response' => $data], trans('audio::album.record_fetched_success'))
        : $this->getErrorJsonResponse([], trans('audio::album.record_fetched_error'));
    }

    public function getSearchData() {
        $album = $this->repository->getAlbum();
        $genre = $this->repository->getGenre();
        $artist = $this->repository->getArtist();
        $data = ['album' => $album, 'genre' => $genre, 'artist' => $artist];
        return $this->getSuccessJsonResponse(['response' => $data], trans('audio::album.record_fetched_success'));
    }

}
