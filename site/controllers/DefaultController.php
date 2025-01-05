<?php

namespace site\controllers;

use mcms\common\controller\BaseRgkController;
use mcms\common\SystemLanguage;
use mcms\common\web\AjaxResponse;
use mcms\user\components\ReferralDecoder;
use mcms\user\models\SignupForm;
use Yii;
use yii\helpers\HtmlPurifier;

class DefaultController extends BaseRgkController
{
  public function actions()
  {
    return array_merge(parent::actions(), [
      'error' => [
        'class' => 'mcms\common\actions\ErrorAction',
      ],
    ]);
  }

  /**
   * Главная - ЛП
   * @throws \yii\web\NotFoundHttpException
   */
  public function actionIndex()
  {
    if (!Yii::$app->user->isGuest) {
      return $this->redirect(Yii::$app->getModule('users')->urlCabinet);
    }
    $page = $this->getPageByUrl('/');
    $this->registerSeoData($page);

    $this->setLang();

    $pagesModule = Yii::$app->getModule('pages');

    return  $this->render('index', ['pagesModule' => $pagesModule]);
  }

  /**
   * Просмотр страниц по короткому адресу
   * @param string $url
   * @return array|string
   * @throws \yii\db\Exception
   */
  public function actionViewPage($url = '/')
  {
    $this->setLang();

    $page = $this->getPageByUrl($url);
    $this->registerSeoData($page);

    if($url == 'submit') {

      $form = new SignupForm(['isRecaptchaValidator' => true]);
      $form->load(Yii::$app->request->post());
      Yii::info('Отправка формы регистрации Affshark', 'email');

      if ($form->validate(['captcha'])) {
        $refId = Yii::$app->request->cookies->getValue('refId');
        //tricky: отправка для Affhsark
        Yii::info('Отправка email', 'email');
        $message = HtmlPurifier::process(
          'Name: '. $form->username . "<br>\r\n".
          'Skype/icq: '. $form->skype . "<br>\r\n".
          'E-mail: '. $form->email . "<br>\r\n".
          'Currency: '. $form->currency . "<br>\r\n".
          'Description: '. Yii::$app->request->post('description') . "<br>\r\n".
          ($refId != NULL ? 'Reffered by ' . ReferralDecoder::decode($refId) : '')
        );
        Yii::$app->mailer->compose()
          ->setFrom('noreply@affshark.biz')
          ->setSubject('Sign up form')
          ->setHtmlBody($message)
          ->setTo('info@affshark.com')
          ->send()
        ;
        Yii::$app->db->createCommand()
          ->insert('register_log', ['created_at' => time(), 'message' => $message])
          ->execute();
      } else {
        Yii::info('Валидация капчи не пройдена', 'email');
      }
      return AjaxResponse::success(['title' => 'The form has been sent successfully.', 'message' => '<p>Thank you for reaching us! We will get in touch as soon as possible.</p><p style="text-align:center">(Mon-Fri, 10am - 7pm GMT+3)</p>']);// title нужен чтоб затриггерился $('#success-modal-button').trigger('click'); а мы на него навесили открытие модалки об успехе. См. submodules/mcms/partners/assets/landings/affshark/js/scripts.min.js
    }
    $pagesModule = Yii::$app->getModule('pages');
    return $this->render('index', ['page' => $page, 'pagesModule' => $pagesModule]);
  }

  /**
   * Добавляем SEO данные
   * @param $page
   */
  public function registerSeoData($page)
  {
    if (is_null($page)) return;

    $view = $this->getView();

    $view->title = !empty($page->seo_title) ? $page->seo_title : $page->name;

    $view->registerMetaTag(['name' => 'keywords', 'content' => $page->seo_keywords]);
    $view->registerMetaTag(['name' => 'description', 'content' => $page->seo_description]);

  }

  private function setLang()
  {
    $clientLanguage = SystemLanguage::getClientLanguage();
    $lang = new SystemLanguage();
    $lang->setLang($clientLanguage === null
      ? Yii::$app->getModule('users')->languageUser()
      : $clientLanguage);
  }

  private function getPageByUrl($url)
  {
    $page = Yii::$app->getModule('pages')->api('pages', ['conditions' => [
      'url' => $url,
      'is_disabled' => false
    ]])->setResultTypeDataProvider()->getResult();

    if (!$models = $page->getModels()) {
      return null;
    }

    return $models[0];
  }



}