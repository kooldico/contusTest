<?php

/**
 * SiteLanguage
 *
 * To manage the functionalities related to SiteLanguage
 * @name       SiteLanguage
 * @version    1.0
 * @author     Contus<developers@contus.in>
 * @copyright  Copyright (C) 2018 Contus. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Video\Models;

use Contus\Video\Scopes\ActiveRecordScope;
use Contus\Base\Model;

class SiteLanguage extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'site_languages';

    /**
     * Constructor method
     * sets hidden for customers
     */
    public function __construct()
    {
        parent::__construct();
        $this->setHiddenCustomer(['created_at', 'updated_at', 'is_active']);
    }
    /**
     * The "booting" method of the model.
     *
     * @vendor Contus
     * @package Audio
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new ActiveRecordScope);
    }
}
