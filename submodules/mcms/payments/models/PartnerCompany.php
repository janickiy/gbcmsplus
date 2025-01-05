<?php

namespace mcms\payments\models;

use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Link;
use mcms\payments\Module;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\helpers\FileHelper;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * This is the model class for table "partner_companies".
 *
 * @property integer $id
 * @property integer $reseller_company_id
 * @property string $name
 * @property string $address
 * @property string $country
 * @property string $city
 * @property string $post_code
 * @property string $tax_code
 * @property string $bank_entity
 * @property string $bank_account
 * @property string $swift_code
 * @property string $currency
 * @property integer $due_date_days_amount
 * @property integer $vat
 * @property string $agreement
 * @property string $invoicing_cycle
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Company $resellerCompany
 */
class PartnerCompany extends \yii\db\ActiveRecord
{
  const PATH = '@protectedUploadPath/partner_companies/';
  const SCENARIO_UPLOAD = 'scenario_upload';
  /** @var UploadedFile */
  public $agreementFile;

  /**
   * @inheritdoc
   */
  public function behaviors()
  {
    return [
      TimestampBehavior::class,
    ];
  }

  /**
   * @inheritDoc
   */
  public function scenarios()
  {
    $attributes = $this->getAttributes();
    $allScenarioAttributes = array_keys($attributes);
    unset($attributes['agreement']);
    $notUploadScenarioAttributes =  array_keys($attributes);

    return  array_merge(parent::scenarios(), [
      self::SCENARIO_DEFAULT => $notUploadScenarioAttributes,
      self::SCENARIO_UPLOAD => $allScenarioAttributes,
    ]);
  }

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'partner_companies';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      ['name', 'required'],
      [['bank_account'], 'string'],
      [['reseller_company_id', 'created_at', 'updated_at'], 'integer'],
      [['due_date_days_amount', 'vat',], 'default', 'value' => 0],
      [['due_date_days_amount'], 'integer', 'min' => 0],
      [['vat'], 'integer', 'min' => 0, 'max' => 100],
      [['name', 'address', 'city', 'post_code', 'country', 'bank_entity'], 'string', 'max' => 255],
      [['tax_code', 'swift_code', 'currency'], 'string', 'max' => 50],
      [['agreementFile'], 'file', 'extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
        'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'], 'maxSize' => 10485760],
      ['invoicing_cycle', 'in', 'range' => array_keys(self::getInvoicingCycleDropdown())],
    ];
  }

  /**
   * @return ActiveQuery
   */
  public function getUserPaymentSettings()
  {
    return $this->hasMany(UserPaymentSetting::class, ['partner_company_id' => 'id']);
  }

  /**
   * @return array
   */
  public function getUserIds()
  {
    $userId = Yii::$app->request->get('user_id');
    if ($userId) {
      return [$userId];
    }
    return array_map(function ($item) {
      return $item->user_id;
    }, $this->userPaymentSettings);
  }


  /**
   * @param bool $insert
   * @return bool
   * @throws \yii\base\Exception
   */
  public function beforeSave($insert)
  {
    $this->uploadFile();
    return parent::beforeSave($insert);
  }

  /**
   * @inheritdoc
   */
  public function afterSave($insert, $changedAttributes)
  {
    $newUserIds = ArrayHelper::getValue(Yii::$app->request->post($this->formName()), 'userIds', []);
    if (!is_array($newUserIds)) {
      $newUserIds = [];
    }
    $deactivateUserIds = array_diff($this->getUserIds(), $newUserIds);

    foreach ($newUserIds as $newUserId) {
      $userPaymentSetting =  UserPaymentSetting::findOne(['user_id' => $newUserId]);
      $userPaymentSetting->partner_company_id = $this->id;
      if (!$userPaymentSetting->save()) {
        Yii::error('Partner company not save ' . print_r($userPaymentSetting->getErrors(), true), __METHOD__);
      }
    }

    foreach ($deactivateUserIds as $deactivateUserId) {
      $userPaymentSetting =  UserPaymentSetting::findOne(['user_id' => $deactivateUserId]);
      $userPaymentSetting->partner_company_id = null;
      if (!$userPaymentSetting->save()) {
        Yii::error('Partner company not save ' . print_r($userPaymentSetting->getErrors(), true), __METHOD__);
      }
    }

    parent::afterSave($insert, $changedAttributes);
  }

  /**
   * @inheritdoc
   */
  public function afterDelete()
  {
    UserPaymentSetting::updateAll(['partner_company_id' => null], ['partner_company_id' => $this->id]);
    parent::afterDelete();
  }

  /**
   * @param array $data
   * @param null $formName
   * @return bool
   */
  public function load($data, $formName = null)
  {
    $this->agreementFile = UploadedFile::getInstance($this, 'agreement');
    if ($this->agreementFile) {
      // Если не передали файл, не перезатираем его
      $this->setScenario(self::SCENARIO_UPLOAD);
    }
    return parent::load($data, $formName);
  }

  /**
   * @return ActiveQuery
   */
  public function getResellerCompany()
  {
    return $this->hasOne(Company::class, ['id' => 'reseller_company_id']);
  }

  /**
   * Загрузка соглашения
   * @throws \yii\base\Exception
   */
  protected function uploadFile()
  {
    if ($this->getScenario() !== self::SCENARIO_UPLOAD) {
      return;
    }
    FileHelper::createDirectory(static::getPath());
    $newFilename = Yii::$app->security->generateRandomString() . ".{$this->agreementFile->getExtension()}";
    $this->agreementFile->saveAs(static::getPath() . $newFilename);
    $this->agreement = $newFilename;
  }

  /**
   * @return bool|string
   */
  public static function getPath()
  {
    return Yii::getAlias(self::PATH);
  }

  /**
   * @return \yii\console\Response|\yii\web\Response
   * @throws NotFoundHttpException
   */
  public function getAgreementFile()
  {
    if (!$this->agreement) {
      throw new NotFoundHttpException();
    }
    return Yii::$app->response->sendFile($fileName = static::getPath() . $this->agreement);
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => Yii::_t('payments.partner-companies.id'),
      'reseller_company_id' => Yii::_t('payments.partner-companies.reseller_company'),
      'name' => Yii::_t('payments.partner-companies.name'),
      'userIds' => Yii::_t('payments.partner-companies.userIds'),
      'userLink' => Yii::_t('payments.partner-companies.userIds'),
      'address' => Yii::_t('payments.partner-companies.address'),
      'city' => Yii::_t('payments.company.attribute-city'),
      'post_code' => Yii::_t('payments.company.attribute-post_code'),
      'country' => Yii::_t('payments.partner-companies.country'),
      'tax_code' => Yii::_t('payments.partner-companies.tax_code'),
      'bank_entity' => Yii::_t('payments.partner-companies.bank_entity'),
      'bank_account' => Yii::_t('payments.partner-companies.bank_account'),
      'swift_code' => Yii::_t('payments.partner-companies.swift_code'),
      'currency' => Yii::_t('payments.partner-companies.currency'),
      'due_date_days_amount' => Yii::_t('payments.partner-companies.due_date_days_amount'),
      'vat' => Yii::_t('payments.partner-companies.vat'),
      'agreement' => Yii::_t('payments.partner-companies.agreement'),
    ];
  }

  /**
   * @param int $startTime
   * @return int
   */
  public function getDueDate($startTime = 0)
  {
    return $startTime + $this->due_date_days_amount * 86400;
  }

  /**
   * @param string $glue
   * @return string
   */
  public function getUserLink($glue = ', ')
  {
    $links = [];
    foreach ($this->getUserPaymentSettings()->each() as $userPaymentSetting) {
      /* @var $userPaymentSetting UserPaymentSetting */

      $links[] = Link::get(
        '/users/users/view',
        ['id' => $userPaymentSetting->user_id],
        ['data-pjax' => 0],
        $userPaymentSetting->user->getStringInfo(),
        false
      );
    }
    return implode($glue, $links);
  }

  /**
   * @return bool
   */
  public static function isCanView()
  {
    return Yii::$app->user->can('PaymentsPartnerCompaniesViewModal');
  }

  /**
   * @return bool
   */
  public static function isCanManage()
  {
    return Yii::$app->user->can('PaymentsPartnerCompaniesUpdateModal')
      && Yii::$app->user->can('PaymentsPartnerCompaniesCreate');
  }

  /**
   * @param int $key
   * @return array
   */
  public static function getInvoicingCycleDropdown($key = null)
  {
    $list = [
      Module::SETTING_DEFAULT_INVOICING_CYCLE_OFF => Yii::_t('payments.partner-companies.invoicing_cycle-off'),
      Module::SETTING_DEFAULT_INVOICING_CYCLE_MONTHLY => Yii::_t('payments.partner-companies.invoicing_cycle-monthly'),
      Module::SETTING_DEFAULT_INVOICING_CYCLE_BIWEEKLY => Yii::_t('payments.partner-companies.invoicing_cycle-biweekly'),
      Module::SETTING_DEFAULT_INVOICING_CYCLE_WEEKLY => Yii::_t('payments.partner-companies.invoicing_cycle-weekly'),
    ];

    if ($key === null) {
      return $list;
    }

    return ArrayHelper::getValue($list, $key);
  }

  /**
   * периодичность выплат партнера
   * @return int
   */
  public function getInvoicingCycle()
  {
    return $this->invoicing_cycle === null
      ? Yii::$app->getModule('payments')->getDefaultInvoicingCycle()
      : $this->invoicing_cycle;
  }
}
