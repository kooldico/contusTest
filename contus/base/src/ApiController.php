<?php

/**
 * Api Controller
 *
 * @vendor Contus
 * @package Base
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2016 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 *
 */
namespace Contus\Base;

use Contus\Base\Controller;

abstract class ApiController extends Controller {

    /**
     * Class constants for holding request type handled by child controllers
     *
     * @vendor Contus
     *
     * @package Base
     * @var const
     */
    const REQUEST_TYPE = 'API';
    /**
     * The request registered on Base Controller.
     *
     * @vendor Contus
     *
     * @package Base
     * @var object
     */
    protected $request;
    /**
     * The auth registered on Base Controller.
     *
     * @vendor Contus
     *
     * @package Base
     * @var object
     */
    protected $auth;
    /**
     * The class property to hold the logger object
     *
     * @vendor Contus
     *
     * @package Base
     * @var object
     */
    protected $logger;
    /**
     * Class property to hold the upload repository object
     *
     * @vendor Contus
     *
     * @package Base
     * @var Contus\Base\Repository
     */
    protected $repository = null;
    /**
     * class property to hold the setting cache data
     *
     * @vendor Contus
     *
     * @package Base
     * @var array
     */
    public function __construct() {
        $this->request = app ()->make ( 'request' );
        $this->auth = app ()->make ( 'auth' );
        $this->logger = app ()->make ( 'log' );

        $this->middleware(function ($request, $next) {
            if(!empty($this->repoArray) && (isMobile() || isWebsite())) {
                foreach($this->repoArray as $rpName) {
                    $this->$rpName->setAuthUser();
                }
            }
           return $next($request);
        });
    }
}