<?php

namespace mcms\common\controller;

use mcms\partners\Module;
use Yii;
use yii\base\Theme;

abstract class SiteBaseController extends AbstractBaseController
{
    public $menu = [];
    public $theme = 'default'; //basic
    public $pageTitle = '';

    public function init()
    {
        // TRICKY: Если настройка "Параметры языков" установлена в eng, выставляем английский принудительно
        /** @var \mcms\user\Module $usersModule */
        $usersModule = Yii::$app->getModule('users');
        if ($usersModule->isEnglishOnly()) {
            Yii::$app->language = 'en';
        }
        parent::init();
    }

    public function beforeAction($action)
    {
        $result = parent::beforeAction($action);
        $this->view->theme = new Theme([
            'pathMap' => [
                '@app/views' => '@mcms/' . $this->module->id . '/themes/' . $this->theme,
                '@mcms/' . $this->module->id . '/views' => '@mcms/' . $this->module->id . '/themes/' . $this->theme
            ],
            'baseUrl' => '@web',
        ]);

        //отключаем bootstrap css и datepicker js/css
        if ($this->theme != 'default') {
            Yii::$app->set('assetManager', [
                'class' => 'yii\web\AssetManager',
                'hashCallback' => Yii::$app->assetManager->hashCallback,
                'bundles' => [
                    'yii\bootstrap\BootstrapAsset' => [
                        'css' => [],
                    ],
                    'kartik\date\DatePickerAsset' => [
                        'css' => [],
                        'js' => ['js/datepicker-kv.min.js'],
                        'depends' => [
                            'mcms\partners\assets\DatePickerAsset'
                        ],
                    ],
                    'kartik\grid\GridViewAsset' => [
                        'css' => [],
                    ],
                    'vova07\fileapi\SingleAsset' => [
                        'css' => [],
                    ]
                ],
            ]);
        }
        return $result;
    }

    public function render($view = NULL, $params = [])
    {
        $params['title'] = $this->controllerTitle;

        $pageTitleTemplate = $this->module->settings->getValueByKey(Module::SETTINGS_TITLE_TEMPLATE);

        if (!$this->pageTitle) $this->pageTitle = $this->controllerTitle;

        $this->pageTitle = strtr($pageTitleTemplate, [
            '{pageTitle}' => $this->pageTitle
        ]);


        return parent::render($view, $params);
    }
}