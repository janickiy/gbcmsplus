<?php

namespace mcms\user\components\widgets;

use mcms\common\helpers\ArrayHelper;
use mcms\user\models\User;
use yii\base\Exception;
use yii\base\Widget;
use yii\bootstrap\Html;
use yii\bootstrap\Nav;
use Yii;

class UserBackWidget extends Widget
{
    public $url;

    public $options;

    public function init()
    {
        if (!$this->url = ArrayHelper::getValue($this->options, 'url')) {
            throw new Exception('Url not defined');
        }

        parent::init();
    }

    public function run()
    {
        if (Yii::$app->session->get(User::SESSION_BACK_IDENTITY_ID)) {
            return Nav::widget([
                'options' => ['class' => 'navbar-nav navbar-right'],
                'items' => [
                    [
                        'url' => $this->url,
                        'label' => Html::icon('log-out') . ' ' . Yii::_t('users.menu.sign_switch'),
                        'encode' => false,
                    ]
                ],
            ]);
        }
        return '';
    }
}