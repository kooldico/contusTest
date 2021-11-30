<?php

/**
 * Audio Service Provider which defines all information about the audio package.
 *
 * @name AudioServiceProvider
 * @vendor Contus
 * @package Audio
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2018 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Audio;

use Illuminate\Support\ServiceProvider;
use Contus\Base\Helpers\StringLiterals;

class AudioServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @vendor Contus
     *
     * @package Audio
     * @return void
     */
    public function boot()
    {
        $audio = 'audio';
        $this->loadTranslationsFrom(__DIR__ . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'lang', $audio);
        $this->publishes([__DIR__ . DIRECTORY_SEPARATOR . 'config' => config_path('contus/' . $audio)], $audio . '_config');
    }
    /**
     * Register the application services.
     *
     * @vendor Contus
     *
     * @package User
     * @return void
     */
    public function register()
    {
        include __DIR__ . '/routes/web.php';
        include __DIR__ . '/routes/api.php';
    }
}
