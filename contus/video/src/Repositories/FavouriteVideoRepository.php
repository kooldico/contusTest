<?php

/**
 * Favourite Video Repository
 *
 * To manage the functionalities related to the Customer module from Latest News Resource Controller
 *
 * @name LatestNewsRepository
 * @vendor Contus
 * @package Cms
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2016 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */

namespace Contus\Video\Repositories;

use Contus\Base\Repository as BaseRepository;
use Contus\Video\Models\Customer;
use Contus\Video\Models\Video;
use Contus\Video\Models\FavouriteVideo;
class FavouriteVideoRepository extends BaseRepository
{
/**
     * Class property to hold the key which hold the Favourite Video object
     *
     * @var object
     */
    protected $_favouriteVideo;

    /**
     * Construct method
     *
     * @vendor Contus
     *
     * @package FavouriteVideo
     *
     * @param Contus\FavouriteVideo\Models\FavouriteVideo $favouriteVideos
     */
    public function __construct(FavouriteVideo $favouriteVideos)
    {
        parent::__construct();
        $this->_favouriteVideo = $favouriteVideos;
    }
    /**
     * Get Total count for Favourite Videos of a customer
     *
     * @return array
     */
    public function getFavouriteVideosCount()
    {
        return (!empty(authUser()->id))? authUser()->favourites()->get()->count() : 0;
    }
}