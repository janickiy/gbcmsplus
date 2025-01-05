<?php

namespace mcms\support\models;

use mcms\common\traits\model\Disabled;
use mcms\user\models\Role;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use mcms\common\multilang\MultiLangModel;

/**
 * Категории тикетов
 */
class SupportCategory extends MultiLangModel
{
  use Disabled;

  const SCENARIO_CREATE = 'create';
  const SCENARIO_EDIT = 'edit';
  const SCENARIO_DISABLE = 'is_disabled-disable';
  const SCENARIO_ENABLE = 'is_disabled-enabl';

  public $roles;

  /**
   * @return array
   */
  public function getMultilangAttributes()
  {
    return [
      'name'
    ];
  }

  /**
   * @return array
   */
  public function behaviors()
  {
    return [
      TimestampBehavior::class,
    ];
  }

  /**
   * @return array
   */
  public function scenarios()
  {
    return array_merge(parent::scenarios(), [
      self::SCENARIO_CREATE => ['name', 'roles', 'is_disabled'],
      self::SCENARIO_EDIT => ['name', 'roles', 'is_disabled'],
      self::SCENARIO_DISABLE => ['is_disabled'],
      self::SCENARIO_ENABLE => ['is_disabled'],
    ]);
  }


  /**
   * @return array
   */
  public function rules()
  {
    return[
      [['name'], 'filter', 'filter' => 'mcms\common\multilang\MultiLangModel::filterArrayPurifier'],
      [['name'], 'validateArrayRequired'],
      [['name'], 'validateArrayString'],
      [['is_disabled'], 'required'],
    ];
  }


  /**
   * @return ActiveQuery
   */
  public function getRoles()
  {
    return $this
      ->hasMany(Role::class, ['name' => 'role'])
      ->viaTable('support_categories_roles', ['support_category_id' => 'id'])
      ;
  }


  /**
   * @return ActiveQuery
   */
  public function getSupport()
  {
    return $this
      ->hasMany(Support::class, ['id' => 'support_category_id'])
      ;
  }

  /**
   * @param bool $insert
   * @param array $changedAttributes
   */
  public function afterSave($insert, $changedAttributes)
  {
    if ($this->roles !== NULL) {
      if (!$insert) {
        $this->unlinkAll('roles', true);
      }
      $roleList = Role::findAll($this->roles);
      foreach ($roleList as $role) {
        $this->link('roles', $role);
      }
    }
    parent::afterSave($insert, $changedAttributes);
  }

  /**
   * @return string
   */
  public static function tableName()
  {
    return 'support_categories';
  }

  /**
   * Роли на которые можно назначить тикет
   * @param bool $allAllowed все разрешенные или для текущей категории
   * @return array
   */
  public function getRolesIds($allAllowed = false)
  {
    $roles = (new Query())
      ->select(['role'])
      ->from('support_categories_roles scr');

    if (!$allAllowed) {
      $roles = $roles->andWhere(['scr.support_category_id' => $this->id]);
    }

    return ArrayHelper::map($roles->all(), function($item) {
      return $item['role'];
    }, function($item) {
      return $item['role'];
    });
  }

  public function getRolesList()
  {
    return implode(', ', ArrayHelper::getColumn($this->getRoles()->all(), 'name'));
  }

  public static function findEnabled()
  {
    return static::findAll(['is_disabled' => 0]);
  }

  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'rolesList' => Yii::_t('labels.categories_roles'),
      'roles' => Yii::_t('labels.categories_roles'),
      'name' => Yii::_t('labels.categories_name'),
      'is_disabled' => Yii::_t('labels.categories_isDisabled'),

    ];
  }

  public function getReplacements()
  {
    return [
      'name' => [
        'value' => $this->isNewRecord ? null : $this->getText('name'),
        'help' => [
          'label' => 'support.replacements.category_name'
        ]
      ]
    ];
  }
}