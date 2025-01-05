<?php

namespace mcms\user\components\api;

use mcms\common\helpers\ArrayHelper;
use mcms\common\module\api\ApiResult;
use mcms\user\components\events\EventPasswordSended;
use mcms\user\models\User;

class UserChangePassword extends ApiResult
{
    protected $userId;
    /**
     * @var array Массив содержащий значение с ключом password
     */
    protected $postData;
    /**
     * @var string Пароль.
     * TRICKY Если указать данный параметр, пароль из postData использован не будет
     */
    protected $password;

    public function init($params = [])
    {
        $this->userId = ArrayHelper::getValue($params, 'user_id', null);
        $this->postData = ArrayHelper::getValue($params, 'post_data', null);
        $this->password = ArrayHelper::getValue($params, 'password', null);

        // Если пароль не указан, то пароль извлекается из POST данных
        if (!$this->password) {
            $this->password = ArrayHelper::getValue($this->postData, 'password');
        }

        if (!$this->userId) $this->addError('user_id is not set');
    }

    public function getResult()
    {
        if (!$this->password) {
            $this->addError('Password is not set');

            return false;
        }

        /* @var $user User */
        $user = User::findOne(['id' => $this->userId]);

        if (!$user) {
            $this->addError('User not found');

            return false;
        }

        $user->setPassword($this->password);
        (new EventPasswordSended($user, $this->password))->trigger();

        return $user->save();
    }
}