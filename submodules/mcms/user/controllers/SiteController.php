<?php

namespace mcms\user\controllers;

use mcms\user\components\controllers\BaseSiteApiController;
use Yii;
use mcms\common\exceptions\InvalidLanguageException;
use mcms\common\SystemLanguage;
use mcms\common\traits\Flash;
use mcms\user\models\User;
use yii\base\InvalidArgumentException;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use mcms\user\models\LoginForm;
use mcms\user\models\SignupForm;
use mcms\user\models\PasswordResetRequestForm;
use mcms\user\models\ResetPasswordForm;
use yii\web\NotFoundHttpException;


/**
 * @property \mcms\User\Module module
 * Site controller
 */
class SiteController extends BaseSiteApiController
{
    //TODO почему не отнаследовались от базового контроллера там уже трейт подключен?
    use Flash;

    public $layout = '@app/views/layouts/main';

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
            ],
        ];
    }

    /**
     * @Description("Экшен для реферральной ссылки, редирект на страницу регистрации.")
     * @return \yii\web\Response
     */
    public function actionRefid()
    {
        if ($this->view->theme) {
            return $this->redirect(Yii::$app->getRequest()->getBaseUrl() . "/?refId=" . Yii::$app->request->get('refId'));
        }
        return $this->redirect(Url::to(['signup', 'refId' => Yii::$app->request->get('refId')]));
    }

    /**
     * @Description("User login form")
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        if ($this->view->theme) {
            return $this->redirect('/?action=' . $this->action->id);
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
            return $this->redirect($redirectUrl);
        } else {

            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * @Description("User logout handler")
     */
    public function actionLogout()
    {
        /** @var \mcms\user\components\api\Auth $authApi */
        $authApi = Yii::$app->getModule('users')->api('auth');
        $authApi->logout();
        return $this->goHome();
    }

    /**
     * Signs user up.
     * @Description("User signup form and handler")
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionSignup()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        if ($this->view->theme) {
            return $this->redirect('/?action=' . $this->action->id);
        }

        if ($this->module->isRegistrationTypeClosed()) {
            return $this->render('signupClosed');
        }

        $model = new SignupForm();

        /** @var \mcms\user\components\api\Auth $authApi */
        $authApi = Yii::$app->getModule('users')->api('auth');

        $signUpResult = $authApi->signUp($model, Yii::$app->request->post());
        $currencyList = Yii::$app->getModule('promo')->api('mainCurrencies', ['availablesOnly' => true])->setMapParams(['code', 'name'])->getMap();

        if ($signUpResult) {
            return $this->goHome();
        }

        return $this->render('signup', [
            'model' => $model,
            'premoderate' => $this->module->isRegistrationTypeByHand(),
            'currencyList' => $currencyList
        ]);
    }

    /**
     * Requests password reset.
     * @Description("User request for reset password form and handler")
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        if ($this->view->theme) {
            return $this->redirect('/?action=' . $this->action->id);
        }

        if ($this->module->isRestorePasswordSupport()) {
            return $this->render('restorePasswordBySupport');
        }

        $model = new PasswordResetRequestForm();

        if (Yii::$app->request->isPost) {
            /** @var \mcms\user\components\api\Auth $authApi */
            $authApi = Yii::$app->getModule('users')->api('auth');
            $passwordRequestResult = $authApi->requestPasswordReset($model, Yii::$app->request->post());

            if (is_string($passwordRequestResult)) {
                $this->flashRawSuccess($passwordRequestResult);
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     * @Description("User reset password form and handler")
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
//      throw new BadRequestHttpException($e->getMessage());
//      return $this->redirect('/?invalid-token');
        }

        if ($this->view->theme) {
            return $this->redirect('/?token=' . $token);
        }

        if (Yii::$app->request->isPost) {
            /** @var \mcms\user\components\api\Auth $authApi */
            $authApi = Yii::$app->getModule('users')->api('auth');
            $passwordResetResult = $authApi->resetPassword($model, Yii::$app->request->post());
            if (is_string($passwordResetResult)) {
                $this->flashRawSuccess($passwordResetResult);
                return $this->goHome();
            }
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    public function actionActivate($code)
    {
        if ($this->view->theme) {
            return $this->redirect('/?activationCode=' . $code);
        }

        /** @var \mcms\user\components\api\Auth $authApi */
        $authApi = $this->module->api('auth');
        $activationResult = $authApi->activate($code);
        if ($activationResult) {
            if (is_string($activationResult)) {
                $this->flashRawSuccess($activationResult);
                return $this->goHome();
            }
        }

        return $this->goHome();
    }

    public function actionLang($language)
    {
        try {
            $systemLanguage = new SystemLanguage();
            $systemLanguage->setLang($language);
        } catch (InvalidLanguageException $e) {
        }

        Yii::$app->getModule('promo')->api('mainCurrencies')->invalidateCache();

        return $this->redirect(Yii::$app->request->getReferrer());
    }
}