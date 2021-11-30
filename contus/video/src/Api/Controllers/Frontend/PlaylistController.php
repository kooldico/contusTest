<?php

/**
 * Playlist Controller
 *
 * To manage the Playlist such as create, edit and delete
 *
 * @version 1.0
 * @author Contus Team <developers@contus.in>
 * @copyright Copyright (C) 2016 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Video\Api\Controllers\Frontend;

use Contus\Video\Repositories\PlaylistRepository;
use Contus\Base\ApiController;
use Contus\Video\Repositories\CategoryRepository;

class PlaylistController extends ApiController {
    /**
     * Constructer method which defines the objects of the classes used.
     *
     * @param object $playlistRepository
     */
    public function __construct(PlaylistRepository $playlistRepository, CategoryRepository $catreposity) {
        parent::__construct ();
        $this->repository = $playlistRepository;
        $this->category = $catreposity;
        $this->repository->setRequestType ( static::REQUEST_TYPE );

        $this->repoArray = ['repository', 'category'];
    }
    /**
     * Function to fetch all playlist based on category
     *
     * @return \Contus\Base\response
     */
    public function browseCategoryPlaylist() {
        $data = $this->repository->browseSortPlaylist($this->request->sortby);
        return ($data) ? $this->getSuccessJsonResponse ( [ 'message' => trans ( 'video::playlist.successfetchall' ),'response' => $data ] ) : $this->getErrorJsonResponse ( [ ], trans ( 'video::playlist.errorfetchall' ) );
    }
    
}
