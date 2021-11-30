<?php

/**
 * Settings Repository
 *
 * To manage the functionalities related to the settings module
 * @name       SettingsRepository
 * @version    1.0
 * @author     Contus<developers@contus.in>
 * @copyright  Copyright (C) 2016 Contus. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\video\Repositories;

use Contus\Base\Repository as BaseRepository;
use Contus\video\Models\Setting;
use Contus\video\Models\SettingCategory;
use Illuminate\Support\Facades\Cache;

class SettingsRepository extends BaseRepository
{
    /**
     * Class property to hold the key which hold the settings object
     *
     * @var object
     */
    protected $_settings;

    /**
     * Class property to hold the key which hold the settings category object
     *
     * @var object
     */
    protected $_settingCategory;

    /**
     * Construct method
     */
    public function __construct(Setting $setting, SettingCategory $settingCategory)
    {
        parent::__construct();
        $this->_settings = $setting;
        $this->_settingCategory = $settingCategory;
    }

    /**
     * Fetch settings to display in admin block.
     *
     * @return response
     */
    public function getSettings()
    {
        return $this->_settingCategory->with([
            'category',
            'category.settings',
        ])->where('parent_id', null)->get();
    }

    /**
     * Fetch setting categories to display in admin block.
     *
     * @return response
     */
    public function getSettingCategory()
    {
        return $this->_settingCategory->where('parent_id', null)->get();
    }

    /**
     * To generate cache file after updating the setting records.
     *
     * Cache file path configured in config file. Once the setting data updated the JSON file will be generated.
     *
     * @return response
     */
    public function generateSettingsCache()
    {
        $settingDetails = $this->getSettings();
        $result = [];
        foreach ($settingDetails as $settingDetail) {
            foreach ($settingDetail['category'] as $category) {
                foreach ($category['settings'] as $setting) {
                    $result[$settingDetail->slug][$category->slug][$setting->setting_name] = $setting->setting_value;
                }
            }
        }
        Cache::forever('settings_caches', json_encode($result));
    }
}
