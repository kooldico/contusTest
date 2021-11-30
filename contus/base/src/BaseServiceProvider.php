<?php

/**
 * Service Provider for Base
 *
 * @name       BaseServiceProvider
 * @vendor     Contus
 * @package    Base
 * @version    1.0
 * @author     Contus<developers@contus.in>
 * @copyright  Copyright (C) 2016 Contus. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */

namespace Contus\Base;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Contus\Base\Helpers\StringLiterals;
use Illuminate\Support\Facades\View;


class BaseServiceProvider extends ServiceProvider{
    /**
     * Bootstrap the application services.
     *
     * @vendor Contus
     * @package Base
     * @return void
     */
    public function boot(){
        $this->loadTranslationsFrom(__DIR__.DIRECTORY_SEPARATOR.StringLiterals::RESOURCES.DIRECTORY_SEPARATOR.'lang', 'base');
        $this->publishes([__DIR__.DIRECTORY_SEPARATOR.'config' => config_path('contus/base'),], 'base_config');
    }
    /**
     * Register the application services.
     *
     * @vendor Contus
     * @package Base
     * @return void
     */
    public function register(){
    }
}
