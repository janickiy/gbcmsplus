<?php

namespace mcms\user\components\api;

use mcms\common\module\api\ApiResult;
use mcms\user\components\events\EventActivationCodeSended;
use mcms\user\components\events\EventAuthLoggedIn;
use mcms\user\components\events\EventAuthLoggedOut;
use mcms\user\components\events\EventAuthRegistered;
use mcms\user\components\events\EventNewPasswordSent;
use mcms\user\components\events\EventPasswordChanged;
use mcms\user\components\events\EventPasswordGenerateLinkSended;
use mcms\user\components\events\EventReferralRegistered;
use mcms\user\components\events\EventRegistered;
use mcms\user\components\events\EventRegisteredHandActivation;
use mcms\user\components\events\EventUserApproved;
use mcms\user\components\events\EventUserApprovedWithoutReferrals;
use mcms\user\components\events\EventUserInvited;
use mcms\user\models\LoginForm;
use mcms\user\models\PasswordResetRequestForm;
use mcms\user\models\ResetPasswordForm;
use mcms\user\models\SignupForm;
use mcms\user\models\SignupLog;
use mcms\user\models\User as UserModel;
use mcms\user\models\UserContact;
use mcms\user\models\UserInvitation;
use mcms\user\Module;
use Yii;

class Auth extends ApiResult
{
    function init($params = [])
    {
    }

    public function renewAuthTokenById($userId)
    {
        /** @var \mcms\user\models\User $user */
        $user = \mcms\user\models\User::findOne($userId);
        if ($user === null) return;
        $user->renewAuthTokenAndSave();
    }

    /**
     * Логин пользователя
     * @param LoginForm $form
     * @param array $requestData пост данные
     * @param null $formName название формы
     * @return \mcms\user\models\User|null
     */
    public function login(LoginForm &$form, array $requestData = [], $formName = null)
    {
        if ($form->load($requestData, $formName) && $form->login()) {
            (new EventAuthLoggedIn($form->getUser()))->trigger();

            return $form->getUser();
        }

        // если юзера нет, пробуем найти приглашение
        $invitation = $form->findInvitation();
        if ($invitation) {
            return $this->doRegister($invitation, true, UserInvitation::STATUS_SIGNUP_BY_LOGIN);
        }

        return null;
    }

    /**
     * Логаут пользователя
     * @return bool
     */
    public function logout()
    {
        $userIdentity = Yii::$app->getUser()->getIdentity();
        $logoutResult = Yii::$app->user->logout();
        !$logoutResult or (new EventAuthLoggedOut($userIdentity))->trigger();

        return $logoutResult;
    }

    /**
     * Возвращает null если форма не прошла валидацю
     * Возврафает bool если не удалось сохранить модель юзеров и при успехе
     * @param SignupForm $form
     * @param array $requestData
     * @param null $formName
     * @return bool|null
     */
    public function signUp(SignupForm &$form, array $requestData = [], $formName = null)
    {
        /** @var Module $userModule */
        $userModule = Yii::$app->getModule('users');
        if ($userModule->isRegistrationTypeClosed()) {
            return null;
        }
        $signUpResult = $form->load($requestData, $formName) && $form->validate();
        if (!$signUpResult) return null;

        /* @var \mcms\user\models\User $user */
        $user = $form->signup();

        if ($userModule->isRegistrationTypeEmailConfirm()) {
            $user->setAttribute('status', UserModel::STATUS_ACTIVATION_WAIT_EMAIL);
            $user->generateEmailActivationCode();
        }

        if ($userModule->isRegistrationWithoutConfirm()) {
            $user->setAttribute('status', UserModel::STATUS_ACTIVE);
        }

        if ($userModule->isRegistrationTypeByHand()) {
            $user->setAttribute('status', UserModel::STATUS_ACTIVATION_WAIT_HAND);
        }

        $userSaveResult = $user->save();
        if (!$userSaveResult) return false;

        // если зарегался сам, но было приглашение - обновляем его
        $invitation = $form->findInvitation();
        if ($invitation) {
            $invitation->setUser($user, UserInvitation::STATUS_SIGNUP_BY_MANUAL);
            $invitation->save();
        }

        $contactModel = $form->getContactModel();
        if ($contactModel) {
            $contactModel->user_id = $user->id;
            $contactModel->save();
        }

        $authManager = Yii::$app->authManager;
        $authRole = $authManager->getRole($userModule::PARTNER_ROLE);
        $authManager->assign($authRole, $user->id);

        /** нужно чтоб можно было проверить пользователя на пермишены */
        Yii::$app->user->setIdentity($user);
        Yii::$app->getModule('payments')->api('setUserCurrency', [
            'userId' => $user->id,
            'currency' => $form->currency,
        ])->getResult();

        if ($userModule->isRegistrationWithReferrals()
            && ($refId = Yii::$app->request->cookies->getValue('refId')) !== null
        ) {
            $user->setReferrer($refId);
        }

        if ($referrer = $user->referrer) {
            (new EventReferralRegistered($referrer, $user))->trigger();
        }

        if ($userModule->isRegistrationWithoutConfirm()) {
            $userModule->isRegistrationWithReferrals()
                ? (new EventUserApproved($user, $user->getReferralLink()))->trigger()
                : (new EventUserApprovedWithoutReferrals($user))->trigger();
            //отправляется только admin reseller root
            (new EventRegistered($user))->trigger();
        } else {
            if ($userModule->isRegistrationTypeEmailConfirm()) {
                (new EventActivationCodeSended($user, $user->email_activation_code))->trigger();
            }
            if ($userModule->isRegistrationTypeByHand()) {
                (new EventRegisteredHandActivation($user))->trigger();
            }
        }

        SignupLog::getInstance($user->id)->create();

        return true;
    }

    /**
     * Возвращается null если форма не прошла валидацию
     *   - false если ничего не произошло
     *   - string в случае успеха, возвращается сообщение
     *
     * @param PasswordResetRequestForm $form
     * @param array $requestData
     * @param null $formName
     * @return bool|null|string
     */
    public function requestPasswordReset(
        PasswordResetRequestForm &$form,
        array                    $requestData = [],
                                 $formName = null
    )
    {
        $formLoadResult = $form->load($requestData, $formName) && $form->validate();
        if ($formLoadResult === false) {
            return null;
        }

        $form->setPasswordResetToken();

        /** @var Module $userModule */
        $userModule = Yii::$app->getModule('users');

        if ($userModule->isRestorePasswordByLink()) {
            (new EventPasswordGenerateLinkSended($form->getUser()))->trigger();
            return Yii::_t(
                'forms.request_password_reset_form-restore-password-by-link',
                ['email' => $form->email]
            );
        }

        if ($userModule->isRestorePasswordSendNewPassword() && $form->setNewPassword()) {
            (new EventNewPasswordSent($form->getUser(), $form->password))->trigger();
            return Yii::_t(
                'forms.request_password_reset_form-restore-password-new-pasword',
                ['email' => $form->email]
            );
        }

        return false;
    }

    /**
     * Возвращает
     *   - null если форма не прошла валидацию
     *   - string если успешно, возвращает переведенную строку успеха
     *   - false если не успешно
     * @param ResetPasswordForm $form
     * @param array $requestData
     * @param null $formName
     * @return bool|null|string
     */
    public function resetPassword(ResetPasswordForm &$form, array $requestData = [], $formName = null)
    {
        $resetPasswordValidateResult = $form->load($requestData, $formName) && $form->validate();
        if (!$resetPasswordValidateResult) return null;

        if ($form->resetPassword()) {
            (new EventPasswordChanged($form->getUser(), $form->password))->trigger();
            return Yii::_t('forms.reset-password-success');
        }

        return false;
    }

    /**
     * * Возвращает
     *   - string если успешно, возвращает переведенную строку успеха
     *   - false если не успешно
     * @param $activationCode
     * @return bool|string
     */
    public function activate($activationCode)
    {
        $activationResult = UserModel::activate($activationCode);
        if ($activationResult) {
            return Yii::_t('forms.activation_success');
        }

        return false;
    }

    /**
     * @param UserInvitation $invitation
     * @param bool $login
     * @param int $status
     * @return UserModel
     */
    public function doRegister($invitation, $login = true, $status = UserInvitation::STATUS_SIGNUP_BY_LINK)
    {
        /** @var Module $userModule */
        $userModule = Yii::$app->getModule('users');
        if (!$userModule->isInvitationsEnabled()) {
            return null;
        }

        if (!$invitation->validate() || $invitation->user_id) {
            return null;
        }

        $form = new SignupForm();

        if (!$form->loadFromInvitation($invitation) || !$form->validate()) {
            return null;
        }

        /* @var \mcms\user\models\User $user */
        $user = $form->signup();
        if (!$user) {
            return null;
        }

        $user->setAttribute('status', UserModel::STATUS_ACTIVE);
        $user->save();

        if ($invitation->contact) {
            $contactModel = new UserContact();
            $contactModel->user_id = $user->id;
            $contactModel->type = UserContact::TYPE_DEFAULT;
            $contactModel->data = $invitation->contact;

            $contactModel->save();
        }

        $authManager = Yii::$app->authManager;
        $authRole = $authManager->getRole($userModule::PARTNER_ROLE);
        $authManager->assign($authRole, $user->id);

        /** нужно чтоб можно было проверить пользователя на пермишены */
        Yii::$app->user->setIdentity($user);
        Yii::$app->getModule('payments')->api('setUserCurrency', [
            'userId' => $user->id,
            'currency' => $form->currency,
        ])->getResult();

        (new EventUserInvited($user))->trigger();

        SignupLog::getInstance($user->id)->create();

        if ($login) {
            $loginForm = new LoginForm(['shouldUseCaptcha' => false]);
            $user = $this->login(
                $loginForm,
                ['username' => $invitation->username, 'password' => $invitation->password],
                ''
            );
        }

        $invitation->setUser($user, $status);
        $invitation->save();

        return $user;
    }
}