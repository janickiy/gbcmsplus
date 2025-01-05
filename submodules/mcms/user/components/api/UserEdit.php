<?php

namespace mcms\user\components\api;

use mcms\user\models\UserContact;
use Yii;
use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\user\models\User;
use mcms\user\models\UserParam;

class UserEdit extends ApiResult
{
    protected $userId;
    protected $postData;


    public function init($params = [])
    {
        $this->userId = ArrayHelper::getValue($params, 'user_id');
        $this->postData = ArrayHelper::getValue($params, 'post_data');
        if (!$this->userId) $this->addError('user_id is not set');
    }
    public function getResult()
    {
        $userErrors = [];
        $userParamsErrors = [];

        /* @var $user User */
        $user = User::findOne(['id' => $this->userId]);

        if (!$user) {
            $this->addError('User not found');
            return false;
        }

        $user->scenario = 'edit';
        $user->setAttributes($this->postData, false);
        if ($user->validate()) {
            $user->save();
        } else {
            $userErrors = $user->getErrors();
        }

        /* @var $userParams UserParam */
        $userParams = UserParam::getByUserId($this->userId);

        $userParams->scenario = 'edit';
        $userParams->setAttributes($this->postData, false);


        if ($userParams->validate()) {
            $userParams->save();
        } else {
            $userParamsErrors = $userParams->getErrors();
        }

        $oldPassword = ArrayHelper::getValue($this->postData, 'oldPassword');
        $newPassword = ArrayHelper::getValue($this->postData, 'newPassword');

        if ($oldPassword && $newPassword) {
            if ($user->validatePassword($oldPassword)) {
                $user->setPassword($newPassword);
                $user->save();
            } else {
                $userErrors['oldPassword'] = Yii::_t('users.forms.wrong_password');
            }
        }

        $this->saveContacts();

        return $errors = ArrayHelper::merge($userErrors, $userParamsErrors) ?: true;
    }

    protected function saveContacts()
    {
        /** @var UserContact[] $contacts */
        $contacts = ArrayHelper::getValue($this->postData, 'contactModels', []);
        if ($contacts === false) {
            return;
        }
        $excludedIds = [];

        foreach ($contacts as $contact) {
            if (!$contact->validate()) {
                continue;
            }

            if (UserContact::existIdentical($contact)) {
                // чтобы не менял updated_at, если ничего не изменилось
                $excludedIds[] = $contact->id;
                continue;
            }

            $contact->save() && $excludedIds[] = $contact->id;
        }

        UserContact::markAsDeleted($this->userId, $excludedIds);
    }
}