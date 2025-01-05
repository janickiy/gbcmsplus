<?php

namespace mcms\user\models;

use yii\base\Model;
use Yii;

/**
 * Contact form
 */
class ContactForm extends Model
{
    public $username;
    public $email;
    public $subject;
    public $message;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'email', 'subject', 'message'], 'required'],
            [['username', 'subject', 'message'], 'string'],
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'email', 'checkDNS' => true],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => Yii::_t('users.contact.username'),
            'email' => Yii::_t('users.contact.email'),
            'subject' => Yii::_t('users.contact.subject'),
            'message' => Yii::_t('users.contact.message'),
        ];
    }


    /**
     * Send email
     *
     * @return bool
     */
    public function send()
    {
        /** @var \mcms\notifications\Module $notificationModule */
        $notificationModule = Yii::$app->getModule('notifications');

        return Yii::$app->mailer->compose()
            ->setFrom($notificationModule->noreplyEmail())
            ->setTo($notificationModule->adminEmail())
            ->setSubject($this->subject)
            ->setTextBody($this->username . "\n" . $this->email . "\n" . $this->message)
            ->send();
    }

}
