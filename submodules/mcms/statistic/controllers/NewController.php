<?php

namespace mcms\statistic\controllers;

use mcms\statistic\components\newStat\BaseFetch;
use mcms\statistic\components\newStat\FormModel;
use mcms\statistic\components\newStat\Grid;
use mcms\statistic\components\newStat\Group;
use mcms\statistic\components\DatePeriod;
use mcms\statistic\components\newStat\mysql\Fetch as Fetch;
use mcms\statistic\components\newStat\subid\Fetch as SubidFetch;
use mcms\statistic\models\ColumnsTemplateNew as ColumnsTemplate;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Новая стата
 */
class NewController extends AbstractStatisticController
{

  const COOKIE_REVSHARE_OR_CPA = 'revshareOrCPA';
  const COOKIE_DURATION = 864000;

  /**
   * @param $startDate
   * @param $endDate
   * @return string
   */
  private function exportFileName($startDate, $endDate)
  {
    return sprintf(
      'statistic_%s-%s',
      preg_replace('/\D/', '', $startDate),
      preg_replace('/\D/', '', $endDate)
    );
  }

  /**
   * @return string
   * @throws \yii\base\InvalidConfigException
   */
  public function actionIndex()
  {
    $this->getView()->title = Yii::_t('statistic.new_statistic_refactored.title');

    // Если передан шаблон - устанавливаем
    if (Yii::$app->request->get('template') !== null) {
      ColumnsTemplate::setTemplate(Yii::$app->request->get('template'));
    }

    $formModel = new FormModel();

    $filters = Yii::$app->request->get('FormModel') ?: [];

    if (empty(ArrayHelper::getValue($filters, 'dateRange'))) {
      // TRICKY. Если не выбрана никакая дата в фильтре, то принудительно выставляем НЕДЕЛЮ.
      // Потому что каждый раз при открытии статы с нуля, всегда отображается неделя по-умолчанию.
      $filters['forceDatePeriod'] = DatePeriod::PERIOD_LAST_WEEK;
    }

    $formModel->load(['FormModel' => $filters]);
    // подбор шаблона
    $selectedTemplate = ColumnsTemplate::getSelected();
    $templateId = $selectedTemplate ? $selectedTemplate->id : ColumnsTemplate::SYS_TEMPLATE_TOTAL;

    $showSubIdGroups = count($formModel->users) === 1 &&
      !in_array(Group::BY_MANAGERS, $formModel->groups); // TODO: перенести в модель?
    if (
      // Если группировки не выбраны
      empty($formModel->groups) ||
      // Или выбраны группировки по subId, но в фильтре не один партнер
      (!$showSubIdGroups && array_intersect([Group::BY_SUBID_1, Group::BY_SUBID_2], $formModel->groups))
    ) {
      // Выставляем группировку по датам
      $formModel->groups = [Group::BY_DATES];
    }

    /** @var Fetch|SubidFetch $fetch */
    $fetch = Yii::createObject($this->getFetchClass($formModel), [$formModel, $templateId]);

    // id виджета экспорта
    $exportWidgetId = 'exportWidget';
    $pageSize = FormModel::DEFAULT_PAGE_SIZE;

    if (!empty($_POST['exportFull_' . $exportWidgetId])) {
      $formModel->setScenario(FormModel::SCENARIO_EXPORT);
      $pageSize = Yii::$app->getModule('statistic')->getExportLimit();
    }

    $dataProvider = $fetch->getDataProvider($this->getDataProviderConfig($fetch, $pageSize));

    if (Yii::$app->request->isPjax || !Yii::$app->request->isAjax) {
      return $this->render('index', [
        'showSubIdGroups' => $showSubIdGroups,
        'dataProvider' => $dataProvider,
        'exportFileName' => $this->exportFileName($formModel->dateFrom, $formModel->dateTo),
        'exportGrid' => new Grid(['dataProvider' => $dataProvider, 'statisticModel' => $formModel, 'templateId' => $templateId]),
        'formModel' => $formModel,
        'maxGroups' => 2,
        'groups' => array_intersect_key(
          Group::getGroupByLabelsAvailable(),
          array_flip([
            Group::BY_DATES,
            Group::BY_HOURS,
            Group::BY_MONTH_NUMBERS,
            Group::BY_WEEK_NUMBERS,
            Group::BY_LANDINGS,
            Group::BY_WEBMASTER_SOURCES,
            Group::BY_LINKS,
            Group::BY_STREAMS,
            Group::BY_PLATFORMS,
            Group::BY_OPERATORS,
            Group::BY_COUNTRIES,
            Group::BY_PROVIDERS,
            Group::BY_USERS,
            Group::BY_LANDING_PAY_TYPES,
            Group::BY_MANAGERS,
            Group::BY_SUBID_1,
            Group::BY_SUBID_2,
          ])
        ),
        'selectedTemplateId' => $templateId,
        'exportWidgetId' => $exportWidgetId,
        'columnsTemplates' => ColumnsTemplate::getAllTemplates(),
        'customField' => $this->getCustomField($templateId),
        'formattedTimezoneOffset' => $this->getFormattedTimezoneOffset(),
      ]);
    }

    // тут рендерим грид без шапок и прочего
    return $this->renderPartial('table_body', [
      'dataProvider' => $dataProvider,
      'formModel' => $formModel,
      'selectedTemplateId' => $templateId,
    ]);
  }

  /**
   * Вернет отформатированную строку смещения времени таймзоны в формате +3:00
   * @return string
   */
  protected function getFormattedTimezoneOffset()
  {
    $timezoneOffset = strftime('%z');
    $formattedTimezoneOffset = mb_substr($timezoneOffset, 0, 1);
    $formattedTimezoneOffset .= mb_substr($timezoneOffset, 1, 1) === '0'
      ? mb_substr($timezoneOffset, 2, 1)
      : mb_substr($timezoneOffset, 1, 2)
    ;

    $formattedTimezoneOffset .= ':' . mb_substr($timezoneOffset, -2);

    return $formattedTimezoneOffset;
  }

  /**
   * имя поля для ComplexFilter в зависимости от выбранного шаблона
   * @param $templateId
   * @return string
   */
  private function getCustomField($templateId)
  {
    switch ($templateId) {
      case -4: // CPA
        $customField = 'cpaRevenue';
        break;
      case -5: // revshare
        $customField = 'revshareRevenue';
        break;
      case -6: // otp
        $customField = 'otpRevenue';
        break;
      default:
        $customField = 'totalRevenue';
        break;
    }
    return $customField;
  }

  /**
   * @param FormModel $formModel
   * @return string
   */
  private function getFetchClass(FormModel $formModel)
  {
    if ($formModel->subid1 || $formModel->subid2) {
      return SubidFetch::class;
    }

    if (in_array(self::getSecondGroup(), [Group::BY_SUBID_1, Group::BY_SUBID_2], true)) {
      return SubidFetch::class;
    }

    if (in_array(Group::BY_SUBID_1, $formModel->groups, true)) {
      return SubidFetch::class;
    }

    if (in_array(Group::BY_SUBID_2, $formModel->groups, true)) {
      return SubidFetch::class;
    }

    return Fetch::class;
  }

  /**
   * @param $fetch
   * @param $pageSize
   * @return array
   */
  private function getDataProviderConfig(BaseFetch $fetch, $pageSize)
  {
    if ($fetch instanceof SubidFetch) {
      return [
        'sort' => [
          'attributes' => [
            'groups' => [
              'default' => SORT_DESC
            ]
          ],
          'defaultOrder' => [
            'groups' => SORT_DESC
          ]
        ],
        'pagination' => [
          'pageSize' => $pageSize
        ]
      ];
    }
    return [
      'sort' => [
        'attributes' => [
          'group' => [
            'default' => SORT_DESC
          ]
        ],
        'defaultOrder' => [
          'group' => SORT_DESC
        ]
      ],
      'pagination' => [
        'pageSize' => $pageSize
      ]
    ];
  }

  /**
   * todo сразу прописывать в модель это поле. Возможно на вьюхе генерить поле в FormModel[] сразу
   * иначе баг в том, что в модели FormModel это свойство пропишется только после вызова validate() и это исправлять сейчас некогда
   *
   * @return array|mixed
   */
  public static function getSecondGroup()
  {
    return Yii::$app->request->post('secondGroup');
  }
}
