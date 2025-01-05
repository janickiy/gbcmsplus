<?php

use console\components\Migration;
use rgk\settings\models\Setting;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m200428_101223_update_registration_settings extends Migration
{
    use PermissionTrait;

    /** @var \rgk\settings\components\SettingsBuilder $settingsBuilder */
    private $settingsBuilder;

    public function init()
    {
        parent::init();
        $this->settingsBuilder = Yii::$app->settingsBuilder;
    }

    /**
     */
    public function up()
    {
        //app.common.group_registration
        $this->settingsBuilder->createSetting(
            ['ru' => 'Включить капчу в регистрации', 'en' => 'Enable captcha on Registration'],
            [],
            'registration.enable_captcha',
            ['EditModuleSettingsUsers'],
            Setting::TYPE_BOOLEAN,
            'app.common.group_registration',
            0,
            [["integer"]]
        );

    }

    /**
     */
    public function down()
    {
        $this->settingsBuilder->removeSetting('registration.enable_captcha');
    }
}
