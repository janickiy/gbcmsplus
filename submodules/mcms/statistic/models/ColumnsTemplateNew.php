<?php

namespace mcms\statistic\models;

use mcms\common\helpers\ArrayHelper;
use mcms\statistic\components\newStat\Grid;
use mcms\user\models\User;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\web\Cookie;

/**
 * @property integer $id
 * @property integer $user_id
 * @property string $name название шаблона
 * @property string $columns JSON-массив отображаемых столбцов шаблона
 *
 * TRICKY Системные шаблоны имеют отрицательный ID
 * TRICKY Системные шаблоны не хранятся в БД, они генерируются на лету @see getSystemTemplates()
 */
class ColumnsTemplateNew extends ActiveRecord
{
  /**
   * @const integer Системный шаблон с названием "По умолчанию".
   * TRICKY Это системный шаблон, а не просто ID-шник шаблона по умолчанию. Просто пока что у нас один системный шаблон.
   * TRICKY Значение используется в JS ColumnTemplates.DEFAULT_TEMPLATE как шаблон по умолчанию
   * @see getSystemTemplates()
   */
  const SYS_TEMPLATE_DEFAULT = -1;
  /**
   * @const integer Системный шаблон со всеми колонками.
   */
  const SYS_TEMPLATE_ALL = -2;
  /**
   * @const integer Системный шаблон со всеми колонками.
   */
  const SYS_TEMPLATE_TOTAL = -3;
  /**
   * @const integer Системный шаблон со всеми колонками.
   */
  const SYS_TEMPLATE_CPA = -4;
  /**
   * @const integer Системный шаблон со всеми колонками.
   */
  const SYS_TEMPLATE_REVSHARE = -5;
  /**
   * @const integer Системный шаблон со всеми колонками.
   */
  const SYS_TEMPLATE_ONETIME = -6;

  // Куки сохраняем на год (в секундах)
  const COOKIE_DURATION = 31536000;

  /**
   * @var string Кэш ключа выбранного шаблона в куках
   * @see getSelectedTemplateCookieKey()
   */
  private static $selectedTemplateCookieKey;

  private static $_systemTemplates;

  /**
   * @inheritdoc
   */
  public static function tableName()
  {
    return 'columns_templates';
  }

  /**
   * Получить шаблон.
   * Может получить не только пользовательский шаблон, но и системный
   * @param int $templateId
   * @return ColumnsTemplateNew|ActiveRecord|null
   */
  public static function getTemplate($templateId)
  {
    return $templateId > 0
      ? static::findUserTemplates()->andWhere(['id' => $templateId])->one()
      : ArrayHelper::getValue(static::getSystemTemplates(), $templateId);
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['user_id', 'name', 'columns'], 'required'],
      ['columns', 'columnsRequiredValidator'],
      [['user_id'], 'integer'],
      [['name', 'columns'], 'string'],
      [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'user_id' => Yii::_t('statistic.columns_templates_user_id'),
      'name' => Yii::_t('statistic.columns_templates_name'),
      'columns' => Yii::_t('statistic.columns_templates_columns'),
    ];
  }

  /**
   * @inheritdoc
   */
  public function beforeValidate()
  {
    if ($this->isNewRecord) {
      $this->user_id = Yii::$app->user->id;
    }
    return parent::beforeValidate();
  }

  /**
   * Шаблоны пользователя
   * @return ActiveQuery
   */
  public static function findUserTemplates()
  {
    return static::find()->andWhere(['user_id' => Yii::$app->user->id]);
  }

  /**
   * Пользовательские шаблоны
   * @return static[]
   */
  public static function getAllTemplates()
  {
    return static::findUserTemplates()->all();
  }

  /**
   * Системные шаблоны
   * @param bool $showStatic показать также статические шаблоны, которые выводятся отдельной страницей
   * @return static[]
   */
  public static function getSystemTemplates($showStatic = true)
  {
    if (isset(self::$_systemTemplates[$showStatic])) {
      return self::$_systemTemplates[$showStatic];
    }
    // Стандартные шаблоны
    $templatesArray = [
      [
        'id' => static::SYS_TEMPLATE_DEFAULT,
        'name' => Yii::_t('statistic.statistic.template_default'),
      ],
      [
        'id' => static::SYS_TEMPLATE_ALL,
        'name' => Yii::_t('statistic.statistic.template_all'),
      ],
    ];

    if ($showStatic === true) {
      $templatesArray[] = ['id' => static::SYS_TEMPLATE_TOTAL, 'name' => Yii::_t('statistic.new_statistic_refactored.traffic_type-total')];
      $templatesArray[] = ['id' => static::SYS_TEMPLATE_CPA, 'name' => Yii::_t('statistic.new_statistic_refactored.traffic_type-cpa')];
      $templatesArray[] = ['id' => static::SYS_TEMPLATE_REVSHARE, 'name' => Yii::_t('statistic.new_statistic_refactored.traffic_type-revshare')];
      $templatesArray[] = ['id' => static::SYS_TEMPLATE_ONETIME, 'name' => Yii::_t('statistic.new_statistic_refactored.traffic_type-otp')];
    }

    // Определение колонок и преобразование данных в модели
    self::$_systemTemplates[$showStatic] = [];
    foreach ($templatesArray as $templateArray) {
      $template = new ColumnsTemplateNew;
      $template->id = $templateArray['id'];
      $template->name = $templateArray['name'];
      $templateColumns = Grid::getSystemTemplateColumns($templateArray['id']);
      $template->columns = Json::encode(ArrayHelper::getColumn($templateColumns, 'attribute'));
      self::$_systemTemplates[$showStatic][$template->id] = $template;
    }

    return self::$_systemTemplates[$showStatic];
  }

  /**
   * Получить выбранный текущим пользователем шаблон
   * @return static|ActiveRecord
   */
  public static function getSelected()
  {
    $responseTemplateId = Yii::$app->response->cookies->getValue(static::getSelectedTemplateCookieKey());
    if (is_numeric($responseTemplateId)) {
      // Если в респонсе есть значение, значит мы сменили шаблон. Возвращаем его
      return static::getTemplate($responseTemplateId);
    }

    $requestTemplateId = Yii::$app->request->cookies->getValue(static::getSelectedTemplateCookieKey());
    if (!is_numeric($requestTemplateId)) {
      // Значение шаблона пустое. Сбрасываем шаблон на дефолтный
      $requestTemplateId = self::setTemplate();
    }
    $result = static::getTemplate($requestTemplateId);

    // Если не получили шаблон, сбрасываем на дефолтный
    // Может произойти, если в куке записан несуществующий шаблон (удаленный)
    if ($result === null) {
      $result = static::getTemplate(self::setTemplate());
    }

    return $result;
  }

  /**
   * Обязательность колонок
   * @return bool
   */
  public function columnsRequiredValidator()
  {
    if (empty(Json::decode($this->columns))) {
      $this->addError('columns', Yii::t('yii', '{attribute} cannot be blank.', ['attribute' => Yii::_t('statistic.columns_templates_columns')]));
      return false;
    }

    return true;
  }

  /**
   * Ключ в куки для хранения выбранного шаблона.
   * TRICKY На JS есть аналогичный метод ColumnTemplates.getSelectedTemplateCookieKey()
   * @return string
   */
  public static function getSelectedTemplateCookieKey()
  {
    if (static::$selectedTemplateCookieKey) return static::$selectedTemplateCookieKey;

    $userRole = Yii::$app->user->identity->getRole()->one();
    static::$selectedTemplateCookieKey = 'statistic_columns_template_refactored_new_' . $userRole->name;

    return static::$selectedTemplateCookieKey;
  }

  /**
   * Фильтрация полей
   * @see \rgk\utils\helpers\FilterHelper
   */
  public function filterRules()
  {
    return [
      'columns' => false,
    ];
  }

  /**
   * Запись выбранного шаблона в куку. Возвращает id установленного шаблона
   * TRICKY: если не передать $templateId, установится шаблон по умолчанию
   * @param int|null $templateId
   * @return int
   */
  public static function setTemplate($templateId = null)
  {
    $templateId = (int)$templateId;
    if (!$templateId) {
      $templateId = self::SYS_TEMPLATE_DEFAULT;
    }
    Yii::$app->response->cookies->add(new Cookie([
      'name' => static::getSelectedTemplateCookieKey(),
      'value' => $templateId,
      'expire' => time() + self::COOKIE_DURATION
    ]));

    return $templateId;
  }
}