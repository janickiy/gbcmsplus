<?php

namespace mcms\partners\controllers;

use mcms\partners\components\widgets\FileApiWidget;
use mcms\partners\models\LinkForm;
use mcms\partners\models\TestPostbackUrlForm;
use mcms\promo\models\Source;
use mcms\promo\models\UserPromoSetting;
use Yii;
use mcms\common\controller\SiteBaseController as BaseController;
use mcms\partners\models\ProfileForm;
use mcms\partners\models\NotificationForm;
use mcms\common\SystemLanguage;
use mcms\common\web\AjaxResponse;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use yii\web\Response;

use mcms\partners\actions\UploadAction as FileAPIUpload;
use mcms\common\helpers\ArrayHelper;
use mcms\payments\models\UserPaymentSetting;


/**
 * Class ProfileController
 * @package mcms\partners\controllers
 */
class ProfileController extends BaseController
{
  public $controllerTitle;

   /**
   * @inheritDoc
   */
  public function beforeAction($action)
  {
    $this->theme = 'basic';

    $this->controllerTitle = Yii::_t('partners.main.profile');

    $this->menu = [
      [
        'label' => Yii::_t('partners.profile.main'),
        'active' => $this->action->id == 'index',
        'url' => Url::to(['profile/index']),
      ],
      [
        'label' =>  Yii::_t('partners.main.notification'),
        'active' => $this->action->id == 'notifications',
        'url' => Url::to(['profile/notifications']),
      ],
    ];

    $userApi = Yii::$app->getModule('users')
      ->api('userParams', ['userId' => Yii::$app->user->id]);

    $userParams = $userApi->getResult();
    $partnerType = ArrayHelper::getValue($userParams, 'partner_type');

    if ($this->action->id == 'postback-settings') {
      $this->menu = [
        ($partnerType == $userApi->getArbitraryType()
          ? [
            'label' => Yii::_t('main.links'),
            'url' => '/partners/links/index/',
          ]
          : [
            'label' => Yii::_t('main.sources'),
            'url' => '/partners/sources/index/',
          ]),
        [
          'label' => Yii::_t('domains.parking'),
          'url' => '/partners/domains/index/',
        ],
        [
          'label' => Yii::_t('partners.profile.postbackSettings'),
          'active' => true,
          'url' => '/partners/profile/postback-settings/',
        ],
      ];
    }

    return parent::beforeAction($action);
  }

  private function handleForm(ProfileForm $form)
  {
    if (
      Yii::$app->request->isPost &&
      $form->load(Yii::$app->request->post()) &&
      $form->validate()
    ) {

      $result = Yii::$app->getModule('users')->api('editUser',[
        'post_data' => $form->getAttributes(),
        'user_id' => Yii::$app->user->id,
      ])->getResult();

      if(is_array($result)) {
        return $result;
      } else {
        $systemLanguage = new SystemLanguage();
        $systemLanguage->setLang($form->language);
        return $this->redirect('/partners/profile/index/');
      }
    }

    return false;
  }

  public function actionIndex()
  {
    $this->pageTitle = $this->controllerTitle . ' - ' . Yii::_t('partners.profile.main');

    $user = Yii::$app->getModule('users')->api('getOneUser',[
      'user_id' => Yii::$app->user->id,
    ])->getResult();

    $form = new ProfileForm($user);

    $result = $this->handleForm($form);

    if(is_array($result)) $form->addErrors($result);

    return $this->render('index',[
      'user' => $user,
      'model' => $form,
      'languagesArray' => SystemLanguage::getLanguangesDropDownArray(),
    ]);
  }

  /**
   * @return \mcms\user\models\ProfileNotificationForm
   */
  private function getProfileNotificationForm()
  {
    return Yii::$app->getModule('users')
      ->api('userParams', ['userId' => Yii::$app->user->id])
      ->getProfileNotificationForm()
      ;
  }

  public function actionNotifications()
  {
    return $this->render('notifications', [
      'submodules' => (new NotificationForm)->getCategories(),
      'userParams' => $this->getProfileNotificationForm(),
    ]);
  }

  public function actionSaveNotificationsSettings()
  {
    if (!Yii::$app->getRequest()->isPost || !Yii::$app->getRequest()->isAjax) {
      Yii::$app->response->setStatusCode(400);
      return AjaxResponse::error();
    }

    Yii::$app->response->format = Response::FORMAT_JSON;
    $postData = Yii::$app->getRequest()->post();

    $formModel = $this->getProfileNotificationForm();
    $formModel->load($postData);

    return ArrayHelper::getValue($postData, 'submit', false)
      ? AjaxResponse::set($formModel->save())
      : ActiveForm::validate($formModel)
      ;
  }

  /**
   * Экшен настройки постбеков в профиле партнера (отображение и сохранение формы)
   * @return string
   */
  public function actionPostbackSettings()
  {
    $this->pageTitle = $this->controllerTitle . ' - ' . Yii::_t('partners.profile.postbackSettings');

    $model = UserPromoSetting::findOne(['user_id' => Yii::$app->user->id]);
    if (!$model) {
      $model = new UserPromoSetting();
      $model->user_id = Yii::$app->user->id;
    }

    if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post()) && $model->validate()) {
      $model->save(false) && $this->flashSuccess('app.common.Saved successfully');
    }

    return $this->render('postback_settings', [
      'model' => $model,
      'testPostbackUrlForm' => new TestPostbackUrlForm([
        'on' => 1,
        'off' => 1,
        'rebill' => 1,
        'cpa' => 1,
      ]),
      'linkForm' => new LinkForm(),
    ]);
  }

  /**
   * Экшен который проставляет всем ссылкам чтобы они использовали глобальный постбек
   * @return Response
   */
  public function actionPostbackUrlSpread()
  {
    $postbackUrl = UserPromoSetting::getGlobalPostbackUrl();
    if ($postbackUrl) {
      // TODO: Добавить в условие по source_type Source::SOURCE_TYPE_SMART_LINK, когда вернем смарт линки
      Source::updateAll(['use_global_postback_url' => 1], ['user_id' => Yii::$app->user->id, 'source_type' => Source::SOURCE_TYPE_LINK]);
      $this->flashSuccess('app.common.Saved successfully');
    } else {
      $this->flashFail('app.common.Save failed');
    }
    return $this->redirect(['postback-settings']);
  }

  /**
   * Экшен который проставляет всем ссылкам чтобы они использовали глобальный постбек для жалоб
   * @return Response
   */
  public function actionComplainsPostbackUrlSpread()
  {
    $postbackUrl = UserPromoSetting::getGlobalComplainsPostbackUrl();
    if ($postbackUrl) {
      // TODO: Добавить в условие по source_type Source::SOURCE_TYPE_SMART_LINK, когда вернем смарт линки
      Source::updateAll(['use_complains_global_postback_url' => 1], ['user_id' => Yii::$app->user->id, 'source_type' => Source::SOURCE_TYPE_LINK]);
      $this->flashSuccess('app.common.Saved successfully');
    } else {
      $this->flashFail('app.common.Save failed');
    }
    return $this->redirect(['postback-settings']);
  }

}
