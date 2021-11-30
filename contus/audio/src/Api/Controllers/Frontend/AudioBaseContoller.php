<?php
/**
 * AudioBaseController
 *
 * @name       AudioBaseController
 * @vendor     Contus
 * @package    Audio
 * @version    1.0
 * @author     Contus<developers@contus.in>
 * @copyright  Copyright (C) 2018 Contus. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio\Api\Controllers\Frontend;

use Illuminate\Http\Request;
use Contus\Base\ApiController;
use Contus\Base\Repositories\UploadRepository;

class AudioBaseController extends ApiController{
    /**
     * Class construct method initialization
     */
    public function __construct(){
        parent::__construct();
    }
}