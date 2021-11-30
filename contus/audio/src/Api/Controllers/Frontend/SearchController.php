<?php

/**
 * Search Controller
 *
 * To manage the search functionality.
 *
 * @name Search Controller
 * @version 1.0
 * @author Contus Team <developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 *
 *
 */
namespace Contus\Audio\Api\Controllers\Frontend;

use Contus\Audio\Repositories\SearchRepository;
use Contus\Base\ApiController;

class SearchController extends ApiController
{
    /**
     * class property to hold the instance of SearchRepository
     *
     * @var \Contus\Base\Repositories\SearchRepository
     */
    public $repository;

    /**
     * Construct method
     *
     * @param SearchRepository $searchRepository
     */
    public function __construct(SearchRepository $searchRepository)
    {
        parent::__construct();
        $this->repository = $searchRepository;
    }

    /**
     * Fetch the video results from elastic search
     *
     * @return json
     */
    public function searchVideos()
    {
        $records = $this->repository->searchVideos();
        return $this->getSuccessJsonResponse(['response' => $records]);
    }

    /**
     * Fetch the video results from elastic search
     *
     * @return json
     */
    public function searchAudios()
    {
        $records = $this->repository->searchAudios();
        return $this->getSuccessJsonResponse(['response' => $records]);
    }
}
