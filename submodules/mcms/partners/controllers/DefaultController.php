<?php

namespace mcms\partners\controllers;

use mcms\common\web\AjaxResponse;
use Yii;
use mcms\common\controller\SiteBaseController as BaseController;
use mcms\partners\models\ProfileForm;
use mcms\partners\models\ProfilePasswordForm;
use mcms\common\SystemLanguage;
use yii\filters\VerbFilter;

/**
 * Class DefaultController
 * @package mcms\partners\controllers
 * @RoleAnnotation({"root", "admin", "partner"})
 */
class DefaultController extends BaseController
{
  public $controllerTitle;

  public function behaviors()
  {
    return [
      'verbs' => [
        'class' => VerbFilter::class,
        'actions' => [
          'hide-promo-modal' => ['post'],
        ],
      ],
    ];
  }

   /**
   * @inheritDoc
   */
  public function beforeAction($action)
  {
    $this->menu = [
      [
        'label' => Yii::_t('partners.profile.main'),
        'active' => $this->action->id == 'profile',
        'url' => '/partners/default/profile/',
      ],
      [
        'label' =>  Yii::_t('partners.profile.change_password_page'),
        'active' => $this->action->id == 'password',
        'url' => '/partners/default/password/',
      ]
    ];

    return parent::beforeAction($action);
  }

  /**
   * @param string $id the ID of this controller.
   * @param \yii\base\Module $module the module that this controller belongs to.
   * @param array $config name-value pairs that will be used to initialize the object properties.
   */
  public function __construct($id, $module, $config = [])
  {
    parent::__construct($id, $module, $config);
    $this->controllerTitle = Yii::_t('partners.main.profile');
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

      if($result) {
        $systemLanguage = new SystemLanguage();
        $systemLanguage->setLang($form->language);
        return $this->redirect('/partners/default/profile/');
      }
    }

    return false;
  }

  public function actionProfile()
  {
    $user = Yii::$app->getModule('users')->api('getOneUser',[
      'user_id' => Yii::$app->user->id,
    ])->getResult();


    $form = new ProfileForm($user);

    $result = $this->handleForm($form);

    if(is_array($result)) $form->addErrors($result);

    $paymentsSettingsForm = Yii::$app->getModule('payments')->api('partnerSettings', ['userId' => Yii::$app->user->id])->getResult();

    $referralLink = Yii::$app->getModule('users')->api('userLink')->buildReferralLink(Yii::$app->user->id);

    return $this->render('profile', [
      'user' => $user,
      'model' => $form,
      'paymentsSettingsForm' => $paymentsSettingsForm,
      'referralLink' => $referralLink,
    ]);
  }

  public function actionPassword()
  {
    $form = new ProfilePasswordForm();

    if (
      Yii::$app->request->isPost &&
      $form->load(Yii::$app->request->post()) &&
      $form->validate()
    ) {
      $result = Yii::$app->getModule('users')->api('changeUserPassword',[
        'post_data' => Yii::$app->request->post('ProfilePasswordForm'),
        'user_id' => Yii::$app->user->id,
      ])->getResult();

      if($result) {
        $this->flashSuccess('partners.profile.password_changed');
        $form = new ProfilePasswordForm();
      }
    }

    return $this->render('password', [
      'model' => $form
    ]);

  }

  public function actionHidePromoModal()
  {
    if (Yii::$app->getModule('users')->api('userParams', ['userId' => Yii::$app->user->id])->hidePromoModal()) {
      return AjaxResponse::success();
    }
    return AjaxResponse::error();
  }
}
