<?php

namespace mcms\pages\models;

use mcms\common\multilang\MultiLangModel;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "partner_cabinet_style_categories".
 *
 * @property integer $id
 * @property string $code
 * @property string $name
 * @property integer $sort
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property PartnerCabinetStyleField[] $fields
 */
class PartnerCabinetStyleCategory extends MultiLangModel
{

  /**
   * @inheritdoc
   */
  public function behaviors()
  {
    return [
      TimestampBehavior::class,
      BlameableBehavior::class,
    ];

  }

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'partner_cabinet_style_categories';
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['code', 'name', 'sort'], 'required'],
      ['sort', 'default', 'value' => 100],
      [['sort', 'created_by', 'updated_by', 'created_at', 'updated_at'], 'integer'],
      [['sort'], 'number', 'max' => 65535, 'min' => 0],
      [['code'], 'string', 'max' => 255],
      [['code'], 'unique'],
      [['code'], 'match', 'pattern' => '/^[a-z0-9_]*$/'],
      [['name'], 'filter', 'filter' => 'mcms\common\multilang\MultiLangModel::filterArrayPurifier'],
      [['name'], 'validateArrayRequired'],
      [['name'], 'validateArrayString'],
    ];
  }

  /**
   * @return array - список мультиязычных аттрибутов
   */
  public function getMultilangAttributes()
  {
    return ['name'];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => Yii::_t('pages.partner_cabinet_style_categories.id'),
      'code' => Yii::_t('pages.partner_cabinet_style_categories.code'),
      'name' => Yii::_t('pages.partner_cabinet_style_categories.name'),
      'sort' => Yii::_t('pages.partner_cabinet_style_categories.sort'),
    ];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getFields()
  {
    return $this->hasMany(PartnerCabinetStyleField::class, ['category_id' => 'id']);
  }

  /**
   * Получаем поля со значениями, если значения пустые то релейшн заполняется пустым объектом
   * @param int $id
   * @return array
   */
  public static function getFieldsWithValues($id)
  {
    if (!$id) return [];

    $query = static::find()
      ->alias('c')
      ->joinWith(['fields f' => function($query) use ($id) {
        /** @var ActiveQuery $query */
        $query->orderBy('sort');
        $query->joinWith(['value v' => function($query) use ($id) {
          $query->on = 'v.style_id = ' . (int)$id;
        }]);
    }]);
    
    $result = [];
    foreach ($query->each() as $item) {
      for ($i = 0; $i < count($item->fields); $i++) {
        /** @var PartnerCabinetStyleField $field */
        $field = $item->fields[$i];
        if (!$field->value) {
          $field->populateRelation('value', new PartnerCabinetStyleValue);
        }
      }
      $result[] = $item;
    }
    return $result;
  }
}
