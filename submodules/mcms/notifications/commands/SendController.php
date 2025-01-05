<?php

namespace mcms\notifications\commands;

use mcms\common\helpers\ArrayHelper;
use mcms\notifications\models\EmailNotification;
use mcms\notifications\models\PushNotification;
use mcms\notifications\models\TelegramNotification;
use mcms\notifications\models\UserInvitationEmailSent;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;


class SendController extends Controller
{
  public function actionAll()
  {
    $this->email();
    $this->telegram();
    $this->push();
  }

  /**
   * Отправка email-уведомлений
   */
  public function email()
  {
    $this->stdout('Sending emails started' . "!\n");

    $emails = EmailNotification::find()->where(['is_send' => 0]);
    $countQuery = clone $emails;
    $this->stdout("Total unprocessed emails: " . $countQuery->count() . PHP_EOL);
    $processed = $unprocessed = 0;
    /** @var \mcms\notifications\models\EmailNotification $notification */
    foreach ($emails->each() as $notification) {
      if ($notification->send()) {
        $processed++;
      } else {
        $unprocessed++;
      }
    }
    $this->stdout('Emails sent: ' . $processed . "\n", Console::FG_GREEN);
    $this->stdout('Emails with errors: ' . $unprocessed . "\n", Console::FG_RED);
  }

  /**
   * Отправка push-уведомлений
   */
  public function push()
  {
    $this->stdout('Sending push notifications started' . "!\n");

    $messagesQuery = PushNotification::find()->where(['is_send' => 0]);
    $countQuery = clone $messagesQuery;
    $this->stdout("Total unprocessed messages: " . $countQuery->count() . PHP_EOL);
    $processed = $unprocessed = 0;
    /** @var \mcms\notifications\models\PushNotification $notification */
    foreach ($messagesQuery->each() as $notification) {
      if ($notification->send()) {
        $processed++;
      } else {
        $unprocessed++;
      }
    }
    $this->stdout('Messages sent: ' . $processed . "\n", Console::FG_GREEN);
    $this->stdout('Messages with errors: ' . $unprocessed . "\n", Console::FG_RED);
  }

  /**
   * Отправка telegram-уведомлений
   */
  public function telegram()
  {
    $this->stdout('Sending telegram messages started' . "!\n");

    $messagesQuery = TelegramNotification::find()->where(['is_send' => 0]);
    $countQuery = clone $messagesQuery;
    $this->stdout("Total unprocessed messages: " . $countQuery->count() . PHP_EOL);
    $processed = $unprocessed = 0;
    /** @var \mcms\notifications\models\TelegramNotification $notification */
    foreach ($messagesQuery->each() as $notification) {
      if ($notification->send()) {
        $processed++;
      } else {
        $unprocessed++;
      }
    }
    $this->stdout('Messages sent: ' . $processed . "\n", Console::FG_GREEN);
    $this->stdout('Messages with errors: ' . $unprocessed . "\n", Console::FG_RED);
  }

  /**
   * Отправка тестового email
   * @param string $toEmail
   * @return int
   */
  public function actionTestEmail($toEmail)
  {
    /** @var \mcms\partners\Module $partnersModule */
    $partnersModule = Yii::$app->getModule('partners');
    $fromEmailCopyright = $partnersModule->getProjectName();

    $this->stdout('Send test email started' . "!\n", Console::FG_BLUE);

    if (!$toEmail = ArrayHelper::getValue(Yii::$app->params, 'testEmail', $toEmail)) {
      $this->stdout('testEmail not provided' . "\n", Console::FG_RED);
      return Controller::EXIT_CODE_ERROR;
    }

    /** @var \mcms\notifications\Module $module */
    $module = Yii::$app->getModule('notifications');
    $from = $module->noreplyEmail();

    if (Yii::$app->mailer->compose()
      ->setFrom([$from => $fromEmailCopyright])
      ->setTo($toEmail)
      ->setSubject('Новые адалт-платники на МТС. Снимаем сливки!')
      ->setHtmlBody($partnersModule->api('getEmailTemplate', [
        'email' => $toEmail,
        'subject' => 'Новые адалт-платники на МТС. Снимаем сливки!',
        'body' => '<div style="color: #323232; font-family: Arial, Helvetica, sans-serif; font-size: 22px; line-height: 30px; margin-bottom: 20px;">Фраза-призыв схожая с заголовком письма</div>
					<p style="margin: 0 0 20px; color: #323232; font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 24px;">Основная проблема сингулярности — в ней происходит натуральное деление на ноль, причем в самом прямом смысле. Все формулы превращаются в чепуху, 3 становится равно 5, и одна бесконечность начинает наползать на другую. А это конец физики, конец науки, дальше живут лишь драконы–ЕГГОГи, и где–то из складок пространства ехидно подмигивает сам Всевышний.</p>
					<strong style="color: #323232; font-family: Arial, Helvetica, sans-serif; font-size: 18px; line-height: 20px; margin-bottom: 20px; display: block;">Подзаголовок:</strong>
					<ul style="padding-left: 30px; margin: 0 0 20px;">
						<li style="margin-bottom: 10px; color: #323232; font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 24px;">Много разных способов, подходов и хитростей предлагалось на замену сингулярности</li>
						<li style="margin-bottom: 10px; color: #323232; font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 24px;">Лучше всех покуда получилось у американского физика Алана Гута в 1981–м году</li>
						<li style="margin-bottom: 10px; color: #323232; font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 24px;">Давайте мысленно(!) достанем из всех текстов слово "сингулярность" и положим вместо него фразу "скалярное поле".</li>
					</ul>
					<div style="margin-bottom: 20px; text-align: center;">
						<a target="_blank" href="#" style="margin: 20px 0 0; display: inline-block; vertical-align: middle; text-align: center; color: #474b00; font-family: Arial, Helvetica, sans-serif; font-size: 18px; line-height: 18px; background-color: #fffbcf; border: 1px solid #e5ed68; border-radius: 10px; padding: 15px 10px;  min-width: 240px; text-decoration: none;">Кнопка с действием</a>
					</div>',
      ])->getResult())
      ->send()
    ) {
      $this->stdout('Email sent successfully' . "\n" . Console::FG_GREEN);
      return Controller::EXIT_CODE_ERROR;
    }
    $this->stdout('Email sent failed' . "\n" . Console::FG_RED);
  }

  /**
   *
   */
  public function actionInvitations()
  {
    $this->stdout('Sending emails for invitations started' . "!\n");

    $emails = UserInvitationEmailSent::find()->where(['is_sent' => 0]);
    $countQuery = clone $emails;

    $this->stdout("Total unprocessed emails: " . $countQuery->count() . PHP_EOL);

    $processed = $unprocessed = 0;

    /** @var \mcms\notifications\models\UserInvitationEmailSent $invitations */
    foreach ($emails->each() as $invitations) {
      if ($invitations->send()) {
        $processed++;
      } else {
        $unprocessed++;
      }
    }

    $this->stdout('Emails sent: ' . $processed . "\n", Console::FG_GREEN);
    $this->stdout('Emails with errors: ' . $unprocessed . "\n", Console::FG_RED);
  }

  /**
   *
   */
  public function actionInvite()
  {
    $emails = UserInvitationEmailSent::find()
      ->andWhere(['is_sent' => 0])
      ->andWhere(['<', 'attempts', 3]);

    $invitation = $emails->orderBy('id')->limit(1)->one();
    if (!$invitation) {
      $this->stdout('done');
      return;
    }

    if ($invitation->send()) {
      $this->stdout("{$invitation->to}\n");
      return;
    }

    $this->stdout("fail\n");
  }
}