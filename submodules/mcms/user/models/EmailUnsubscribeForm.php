<?php

namespace mcms\user\models;

use yii\base\Model;

/**
 * Contact form
 */
class EmailUnsubscribeForm extends Model
{
    /**
     * @var string
     */
    public $token;

    /**
     * ContactForm constructor.
     * @param string $token
     * @param array $config
     */
    public function __construct($token, array $config = [])
    {
        $this->token = $token;

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['token'], 'required'],
        ];
    }

    /**
     * @param bool $runValidation
     * @return bool
     */
    public function unsubscribe($runValidation = true)
    {
        if ($runValidation && !$this->validate()) {
            return false;
        }

        $user = User::findByEmailUnnsubscribeToken($this->token);
        if (!$user) {
            return false;
        }

        /** @var UserParam $params */
        $params = $user->params;
        $params->notify_email_news = 0;
        $params->notify_email_categories = [];
        $params->save();

        $user->removeEmailUnsubscribeToken();

        return $user->save();
    }
}
