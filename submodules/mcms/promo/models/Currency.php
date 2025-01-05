<?php

namespace mcms\promo\models;

use mcms\common\traits\model\FormAttributes;
use mcms\common\traits\model\MultiLang;
use mcms\common\validators\AlphanumericalValidator;
use kartik\builder\Form;
use mcms\promo\components\api\MainCurrencies;
use mcms\user\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;
use mcms\common\multilang\MultiLangModel;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "currencies".
 *
 * @deprecated Будем юзать новую модель в модуле currency
 * @property integer $id
 * @property string $name
 * @property string $code
 * @property string $symbol
 */
class Currency extends MultiLangModel
{

  const DATA = [ // todo можно дописать сюда необходимые данные, имя например или символ. И перенести логику в mcms\commmon\Currency
    ['id' => 1, 'code' => 'rub'],
    ['id' => 2, 'code' => 'usd'],
    ['id' => 3, 'code' => 'eur'],
  ];

  public function behaviors()
  {
    return [
      TimestampBehavior::class,
    ];
  }

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'currencies';
  }

  /**
   * @return array - список мультиязычных аттрибутов
   */
  public function getMultilangAttributes()
  {
    return [
      'name'
    ];
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['code', 'symbol'], 'required'],
      [['name'], 'filter', 'filter' => 'mcms\common\multilang\MultiLangModel::filterArrayPurifier'],
      [['name'], 'validateArrayRequired'],
      [['name'], 'validateArrayString'],
      [['code'], 'string', 'max' => 10],
      [['code'], AlphanumericalValidator::class],
      [['symbol'], 'string', 'max' => 20],
    ];
  }

  public function beforeDelete()
  {
    if (in_array($this->code, [MainCurrencies::RUB, MainCurrencies::USD, MainCurrencies::EUR], true)) {
      return false;
    }
    return parent::beforeDelete();
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => "ID",
      'name' => Yii::_t('promo.currencies.attribute-name'),
      'code' => Yii::_t('promo.currencies.attribute-code'),
      'symbol' => Yii::_t('promo.currencies.attribute-symbol'),
      'created_at' => Yii::_t('promo.currencies.attribute-created_at'),
      'updated_at' => Yii::_t('promo.currencies.attribute-updated_at'),
    ];
  }

  public function getViewLink()
  {
    return \mcms\common\helpers\Link::get(
      '/promo/currencies/view',
      ['id' => $this->id], ['data-pjax' => 0], $this->name, false
    );
  }

  public function afterSave($insert, $changedAttributes)
  {
    $this->invalidateCache();
    parent::afterSave($insert, $changedAttributes);
  }

  public function afterDelete()
  {
    $this->invalidateCache();
    parent::afterDelete();
  }

  protected function invalidateCache()
  {
    TagDependency::invalidate(Yii::$app->cache, ['currency']);
  }

  /**
   * @deprecated используй mcms\common\Currency
   * @param $code
   * @return int|null
   */
  public static function getIdByCode($code)
  {
    $mapped = ArrayHelper::map(self::DATA, 'code', 'id');
    return ArrayHelper::getValue($mapped, $code);
  }

  /**
   * @deprecated используй mcms\common\Currency
   * @param $id
   * @return string|null
   */
  public static function getCodeById($id)
  {
    $mapped = ArrayHelper::map(self::DATA, 'id', 'code');
    return ArrayHelper::getValue($mapped, $id);
  }
}
