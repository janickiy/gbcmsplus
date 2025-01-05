<?php

namespace mcms\user\controllers;

use mcms\user\components\controllers\BaseSiteApiController;
use mcms\user\models\ContactForm;
use mcms\user\models\EmailUnsubscribeForm;
use mcms\user\models\SignupFormSecond;
use mcms\user\models\UserInvitation;
use rgk\utils\widgets\alert\Alert;
use Yii;
use mcms\common\web\AjaxResponse;
use mcms\common\exceptions\InvalidLanguageException;
use yii\base\InvalidArgumentException;
use mcms\common\SystemLanguage;
use mcms\user\models\User;
use mcms\user\models\LoginForm;
use mcms\user\models\SignupForm;
use mcms\user\models\PasswordResetRequestForm;
use mcms\user\models\ResetPasswordForm;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;


/**
 * @property \mcms\User\Module module
 * Api controller
 */
class ApiController extends BaseSiteApiController
{
    /**
     * Login user
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return AjaxResponse::error();
        }

        $model = new LoginForm();
        /** @var User $loggedInUser */
        $loggedInUser = Yii::$app
            ->getModule('users')
            ->api('auth')
            ->login($model, Yii::$app->request->post());

        if ($loggedInUser !== null) {
            try {
                $systemLanguage = new SystemLanguage();
                $systemLanguage->setLang($loggedInUser->language);
            } catch (InvalidLanguageException $e) {
            }

            $redirectUrl = Yii::$app->getModule('users')->urlCabinet;
            return AjaxResponse::success(['redirectUrl' => $redirectUrl]);
        }

        return AjaxResponse::error(ArrayHelper::merge(
            $model->getErrors(),
            ['useCaptcha' => $model->shouldUseCaptcha()]
        ));
    }

    /**
     * Signs user up
     */
    public function actionSignup()
    {
        if (!Yii::$app->user->isGuest) {
            return AjaxResponse::error();
        }

        if ($this->module->isRegistrationTypeClosed()) {
            return AjaxResponse::error();
        }

        /** @var SignupForm $model */
        $model = Yii::createObject(SignupForm::class);
        /** @var SignupFormSecond $modelSecond */
        $modelSecond = Yii::createObject(SignupFormSecond::class);

        /** @var \mcms\user\components\api\Auth $authApi */
        $authApi = Yii::$app->getModule('users')->api('auth');

        $post = Yii::$app->request->post();

        // TRICKY: SignupFormSecond используется для того, чтобы поместить 2 формы регистрации на страницу
        $signUpResult = $authApi->signUp($model, $post) || $authApi->signUp($modelSecond, $post);

        if ($signUpResult) {
            return AjaxResponse::success(
                [
                    'title' => Yii::_t('users.registration.registration_completed'),
                    'subtitle' => Yii::_t('users.registration.congratulations'),
                    'action' => Yii::_t('users.registration.registration_successfull'),
                    'message' => Yii::_t('users.registration.registration_message')
                ]
            );
        }

        return AjaxResponse::error(array_merge($model->getErrors(), $modelSecond->getErrors()));
    }

    /**
     * Requests password reset
     */
    public function actionRequestPasswordReset()
    {
        if ($this->module->isRestorePasswordSupport()) {
            return AjaxResponse::error();
        }

        $model = new PasswordResetRequestForm();

        if (Yii::$app->request->isPost) {
            /** @var \mcms\user\components\api\Auth $authApi */
            $authApi = Yii::$app->getModule('users')->api('auth');
            $passwordRequestResult = $authApi->requestPasswordReset($model, Yii::$app->request->post());

            if (is_string($passwordRequestResult)) {
                return AjaxResponse::success(
                    [
                        'title' => Yii::_t('forms.request_password_title'),
                        'subtitle' => '',
                        'action' => '',
                        'message' => $passwordRequestResult
                    ]
                );
            }
        }

        return AjaxResponse::error(ArrayHelper::merge(
            $model->getErrors(),
            ['useCaptcha' => $model->shouldUseCaptcha()]
        ));
    }

    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            return AjaxResponse::error();
        }

        if (Yii::$app->request->isPost) {
            /** @var \mcms\user\components\api\Auth $authApi */
            $authApi = Yii::$app->getModule('users')->api('auth');
            $passwordResetResult = $authApi->resetPassword($model, Yii::$app->request->post());
            if (is_string($passwordResetResult)) {
                return AjaxResponse::success(

                    [
                        'title' => Yii::_t('forms.reset_password_title'),
                        'subtitle' => '',
                        'action' => '',
                        'message' => $passwordResetResult
                    ]
                );
            }
        }

        return AjaxResponse::error($model->getErrors());
    }

    public function actionValidToken($token)
    {
        try {
            new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            return AjaxResponse::error([
                'title' => Yii::_t('contact.error_title'),
                'subtitle' => Yii::_t('contact.invalid_link'),
                'message' => Yii::_t('contact.invalid_reset_password_link'),
                'error' => true,
            ]);
        }
        return AjaxResponse::success();
    }

    public function actionContact()
    {
        $form = new ContactForm;
        $form->load(Yii::$app->request->post());

        if ($form->validate()) {
            return $form->send()
                ? AjaxResponse::success(
                    [
                        'title' => Yii::_t('contact.success_title'),
                        'subtitle' => '',
                        'action' => '',
                        'message' => Yii::_t('contact.success_message'),
                    ]
                )
                : AjaxResponse::success(
                    [
                        'title' => Yii::_t('contact.error_title'),
                        'subtitle' => '',
                        'action' => '',
                        'error' => true,
                        'message' => Yii::_t('contact.error_message'),
                    ]
                );
        }

        return AjaxResponse::error($form->getErrors());
    }


    public function actionActivate()
    {
        /** @var \mcms\user\components\api\Auth $authApi */
        $authApi = Yii::$app->getModule('users')->api('auth');

        $activationResult = $authApi->activate(Yii::$app->request->get('code'));
        if (is_string($activationResult)) {
            return AjaxResponse::success([
                'title' => '',
                'subtitle' => '',
                'action' => '',
                'message' => $activationResult
            ]);
        }

        return AjaxResponse::error();
    }

    /**
     * @param $token
     * @return \yii\web\Response
     */
    public function actionEmailUnsubscribe($token)
    {
        $form = new EmailUnsubscribeForm($token);

        if ($form->unsubscribe()) {
            Yii::$app->session->setFlash(Alert::TYPE_SUCCESS, Yii::_t('app.common.success_email_unsubscribe'));

            return $this->goHome();
        }

        Yii::$app->session->setFlash(Alert::TYPE_DANGER, Yii::_t('app.common.fail_email_unsubscribe'));

        return $this->goHome();
    }

    /**
     * @param $hash
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionInvite($hash)
    {
        $invitation = UserInvitation::findByHash($hash);
        if (!$invitation) {
            throw new NotFoundHttpException();
        }

        /** @var \mcms\user\components\api\Auth $authApi */
        $authApi = Yii::$app->getModule('users')->api('auth');

        $user = $authApi->doRegister($invitation);
        if (!$user) {
            throw new NotFoundHttpException();
        }

        try {
            $systemLanguage = new SystemLanguage();
            $systemLanguage->setLang($user->language);
        } catch (InvalidLanguageException $e) {
        }

        $redirectUrl = Yii::$app->getModule('users')->urlCabinet;

        return $this->redirect($redirectUrl);
    }
}