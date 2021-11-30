<?php

/**
 * Categories Models.
 *
 * @name Categories
 * @vendor Contus
 * @package Video
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Video\Models;

use Contus\Base\Helpers\StringLiterals;
use Contus\Base\Model;
use Contus\Video\Models\CategoryTranslation;
use Contus\Video\Models\Video;
use Contus\Video\Models\VideoCategory;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\File\File;


class Genre extends Model {
  
    /**
     * The database table used by the model.
     *
     * @vendor     Contus
     * @package    Video
     * @var string
     */
    protected $table = 'genres';
  }