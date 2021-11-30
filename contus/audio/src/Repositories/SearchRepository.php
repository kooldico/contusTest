<?php

/**
 * Search Repository
 *
 * To manage the functionalities related to the search.
 *
 * @name Search Repository
 * @vendor Contus
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Repositories;

use Contus\Base\Repository as BaseRepository;
use Contus\Audio\Models\Audios;
use Contus\Audio\Models\Albums;
use Contus\Audio\Models\Artist;
use Contus\Audio\Models\Playlist;

class SearchRepository extends BaseRepository
{
    /**
     * Class property to hold the key which hold the user object
     *
     * @var object
     */
    protected $video;

    public function __construct()
    {
        parent::__construct();
        $this->audio = new Audios();
        $this->album = new Albums();
        $this->artist = new Artist();
        $this->playlist = new Playlist();
    }


    public function searchAudios()
    {
        $searchKeyword = $this->request->input('q');
        $this->setRules([
            'q' => 'required',
            'type' => 'sometimes|in:audio,album,artist,playlists',
        ]);

        $this->validate($this->request, $this->getRules());

        $inputArray = $this->request->all();
        $perPage    = 12;

        if(!empty($inputArray['type'])) {
            switch($inputArray['type']) {
                case 'album':
                    $result['album']    = $this->album->search($searchKeyword)->paginate($perPage);
                    break;
                case 'artist':
                    $result['artist']    = $this->artist->search($searchKeyword)->paginate($perPage);
                    break;
                case 'playlists':
                    $result['playlists']    = $this->playlist->search($searchKeyword)->paginate($perPage);
                    break;
                default:
                    $result['audio']    = $this->audio->search($searchKeyword)->paginate($perPage);
                    break;
            }
        } 
        else {
            $result['audio']   = $this->audio->search($searchKeyword)->paginate($perPage);
            $result['album']    = $this->album->search($searchKeyword)->paginate($perPage);
            $result['artist']   = $this->artist->search($searchKeyword)->paginate($perPage);
            $result['playlist'] = $this->playlist->search($searchKeyword)->paginate($perPage);
        }


        return $result;
    }


}
