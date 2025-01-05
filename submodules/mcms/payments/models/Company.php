<?php

namespace mcms\payments\models;

use mcms\common\helpers\ArrayHelper;
use rgk\utils\behaviors\TimestampBehavior;
use Yii;
use yii\base\Exception;
use yii\helpers\FileHelper;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * This is the model class for table "companies".
 *
 * @property integer $id
 * @property string $name
 * @property string $address
 * @property string $country
 * @property string $city
 * @property string $post_code
 * @property string $tax_code
 * @property string $logo
 * @property integer $created_at
 * @property integer $updated_at
 */
class Company extends \yii\db\ActiveRecord
{
  const PATH = '@protectedUploadPath/companies/';
  const SCENARIO_UPLOAD = 'scenario_upload';
  /** @var UploadedFile */
  public $logoImageFile;
  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'companies';
  }

  /**
   * @return array
   */
  public static function getDropdownList()
  {
    return static::find()->select(['name'])->indexBy('id')->column();
  }

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
    $allAttributesScenario = array_keys($attributes);
    unset($attributes['logo']);
    $notUploadScenario =  array_keys($attributes);

    return  array_merge(parent::scenarios(), [
      self::SCENARIO_DEFAULT => $notUploadScenario,
      self::SCENARIO_UPLOAD => $allAttributesScenario,
    ]);
  }


  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      ['name', 'required'],
      [['logoImageFile'], 'file', 'extensions' => ['svg', 'jpg', 'jpeg', 'png'], 'mimeTypes' => ['image/jpeg', 'image/gif', 'image/jpg', 'image/png', 'image/svg+xml'], 'maxSize' => 10485760],
      [['created_at', 'updated_at'], 'integer'],
      [['name', 'address', 'city', 'post_code', 'country'], 'string', 'max' => 255],
      [['tax_code'], 'string', 'max' => 50],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'name' => Yii::_t('payments.company.attribute-name'),
      'address' => Yii::_t('payments.company.attribute-address'),
      'city' => Yii::_t('payments.company.attribute-city'),
      'post_code' => Yii::_t('payments.company.attribute-post_code'),
      'country' => Yii::_t('payments.company.attribute-country'),
      'tax_code' => Yii::_t('payments.company.attribute-tax_code'),
      'logo' => Yii::_t('payments.company.attribute-logo'),
      'created_at' => Yii::_t('payments.company.attribute-created_at'),
      'updated_at' => Yii::_t('payments.company.attribute-updated_at'),
    ];
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
   * @param array $data
   * @param null $formName
   * @return bool
   */
  public function load($data, $formName = null)
  {
    $this->logoImageFile = UploadedFile::getInstance($this, 'logo');
    if ($this->logoImageFile) {
      // Если не передали лого, не перезатираем его
      $this->setScenario(self::SCENARIO_UPLOAD);
    }
    return parent::load($data, $formName);
  }

  /**
   * Загрузка картинок
   * @throws \yii\base\Exception
   */
  protected function uploadFile()
  {
    if ($this->getScenario() !== self::SCENARIO_UPLOAD) {
      return;
    }
    FileHelper::createDirectory(static::getPath());
    $newFilename = Yii::$app->security->generateRandomString() . ".{$this->logoImageFile->getExtension()}";
    $this->logoImageFile->saveAs(static::getPath() . $newFilename);
    $this->logo = $newFilename;
  }

  /**
   * @return bool|string
   */
  public static function getPath()
  {
    return Yii::getAlias(self::PATH);
  }

  /**
   * @return string
   */
  public function getLogoPath()
  {
    return sprintf('%s%s', static::getPath(), $this->logo);
  }
}
