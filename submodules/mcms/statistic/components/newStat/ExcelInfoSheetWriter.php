<?php


namespace mcms\statistic\components\newStat;

use mcms\common\RunnableInterface;
use mcms\promo\models\Country;
use mcms\promo\models\Landing;
use mcms\promo\models\LandingCategory;
use mcms\promo\models\LandingPayType;
use mcms\promo\models\Operator;
use mcms\promo\models\Platform;
use mcms\promo\models\Provider;
use mcms\promo\models\Source;
use mcms\promo\models\Stream;
use mcms\statistic\models\ColumnsTemplateNew;
use mcms\user\models\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Yii;

/**
 * Экспортируем лист с информацией о выгрузке: назв реселлерки, выбранные фильтры и т.д.
 */
class ExcelInfoSheetWriter implements RunnableInterface
{
  public $sheetName = 'Info';

  /**
   * @var Spreadsheet
   */
  protected $_objSpreadsheet;
  /**
   * @var FormModel
   */
  protected $_formModel;
  /**
   * @var int
   */
  protected $_templateId;
  /**
   * @var int
   */
  protected $_newSheetIndex;
  /** @var int указатель текущих координат */
  protected $_row = 1;
  /** @var int указатель текущих координат */
  protected $_col = 1;
  /**
   * @var Spreadsheet
   */
  protected $_objPHPExcelSheet;

  /**
   * @param Spreadsheet
   * @param FormModel $formModel
   * @param $templateId
   * @param $newSheetIndex
   */
  public function __construct(Spreadsheet $excel, FormModel $formModel, $templateId, $newSheetIndex)
  {
    $this->_objSpreadsheet = $excel;
    $this->_formModel = $formModel;
    $this->_newSheetIndex = $newSheetIndex;
    $this->_templateId = $templateId;
  }

  public function run()
  {
    $this->_objPHPExcelSheet = $this->_objSpreadsheet->createSheet($this->_newSheetIndex);

    // Platform - Название ресселлерки (RGK Tools)
    $this->setCellValue($this->_col, $this->_row, 'Platform');
    $this->setCellValue($this->_col + 1, $this->_row, Yii::$app->settingsManager->getValueByKey('settings.project_name', Yii::$app->name));
    $this->_row++;

    // Report type - Название шаблона и группировка (Total - by dates)
    $this->setCellValue($this->_col, $this->_row, 'Report type');
    $lastGroup = end($this->_formModel->groups);

    $this->setCellValue($this->_col + 1, $this->_row, strtr(':template - by :group', [
      ':template' => ColumnsTemplateNew::getTemplate($this->_templateId)->name,
      ':group' => strtolower(Group::getGroupByLabel($lastGroup))
    ]));
    $this->_row++;

    // Manager - e-mail пользователя кто делал выгрузку
    $this->setCellValue($this->_col, $this->_row, 'Manager');
    /** @var User $user */
    $user = Yii::$app->user->identity;
    $this->setCellValue($this->_col + 1, $this->_row, $user->email);
    $this->_row++;

    // Created - Время выгрузки
    $this->setCellValue($this->_col, $this->_row, 'Created');
    $this->setCellValue($this->_col + 1, $this->_row, Yii::$app->formatter->asDatetime('now'));
    $this->_row++;

    // Time frame - Период данных, за который сделали выгрузку
    $this->setCellValue($this->_col, $this->_row, 'Time frame');
    $this->setCellValue($this->_col + 1, $this->_row, strtr(':dateFrom - :dateTo', [
      ':dateFrom' => Yii::$app->formatter->asDate($this->_formModel->dateFrom),
      ':dateTo' => Yii::$app->formatter->asDate($this->_formModel->dateTo)
    ]));
    $this->_row++;

    // Filters - Все остальные примененные фильтры.
    $this->setCellValue($this->_col, $this->_row, 'Filters');
    $this->_row++;

    $this->drawFilters();

    $this->_objPHPExcelSheet->getColumnDimension($this->getColumnName($this->_col))->setWidth(20);
    $this->_objPHPExcelSheet->getColumnDimension($this->getColumnName($this->_col + 1))->setWidth(100);
    $this->_objPHPExcelSheet->setTitle($this->sheetName);
  }

  /**
   * @param $column
   * @return string
   */
  protected function getColumnName($column)
  {
    return ExportMenu::columnName($column);
  }

  /**
   * @param $column
   * @param $row
   * @param $value
   */
  protected function setCellValue($column, $row, $value)
  {
    $this->_objPHPExcelSheet->setCellValue($this->getColumnName($column) . $row, $value);
  }

  protected function drawFilters()
  {
    if ($this->_formModel->countries) {
      $this->setCellValue($this->_col, $this->_row, $this->_formModel->getAttributeLabel('countries'));
      $formattedValues = [];
      foreach ($this->getArray($this->_formModel->countries) as $value) {
        $formattedValues[] = Country::findOne($value)->getStringInfo();
      }
      $this->setCellValue($this->_col + 1, $this->_row, implode(', ', $formattedValues));
      $this->_row++;
    }

    if ($this->_formModel->operators) {
      $this->setCellValue($this->_col, $this->_row, $this->_formModel->getAttributeLabel('operators'));
      $formattedValues = [];
      foreach ($this->getArray($this->_formModel->operators) as $value) {
        $formattedValues[] = Operator::findOne($value)->getStringInfo();
      }
      $this->setCellValue($this->_col + 1, $this->_row, implode(', ', $formattedValues));
      $this->_row++;
    }

    if ($this->_formModel->users) {
      $this->setCellValue($this->_col, $this->_row, $this->_formModel->getAttributeLabel('users'));
      $formattedValues = [];
      foreach ($this->getArray($this->_formModel->users) as $value) {
        $formattedValues[] = User::findOne($value)->getStringInfo();
      }
      $this->setCellValue($this->_col + 1, $this->_row, implode(', ', $formattedValues));
      $this->_row++;
    }

    if ($this->_formModel->sources) {
      $this->setCellValue($this->_col, $this->_row, $this->_formModel->getAttributeLabel('sources'));
      $formattedValues = [];
      foreach ($this->getArray($this->_formModel->sources) as $value) {
        $formattedValues[] = Source::findOne($value)->getStringInfo();
      }
      $this->setCellValue($this->_col + 1, $this->_row, implode(', ', $formattedValues));
      $this->_row++;
    }

    if ($this->_formModel->landingPayTypes) {
      $this->setCellValue($this->_col, $this->_row, $this->_formModel->getAttributeLabel('landingPayTypes'));
      $formattedValues = [];
      foreach ($this->getArray($this->_formModel->landingPayTypes) as $value) {
        $formattedValues[] = LandingPayType::findOne($value)->getStringInfo();
      }
      $this->setCellValue($this->_col + 1, $this->_row, implode(', ', $formattedValues));
      $this->_row++;
    }

    if ($this->_formModel->providers) {
      $this->setCellValue($this->_col, $this->_row, $this->_formModel->getAttributeLabel('providers'));
      $formattedValues = [];
      foreach ($this->getArray($this->_formModel->providers) as $value) {
        $formattedValues[] = Provider::findOne($value)->getStringInfo();
      }
      $this->setCellValue($this->_col + 1, $this->_row, implode(', ', $formattedValues));
      $this->_row++;
    }

    if ($this->_formModel->streams) {
      $this->setCellValue($this->_col, $this->_row, $this->_formModel->getAttributeLabel('streams'));
      $formattedValues = [];
      foreach ($this->getArray($this->_formModel->streams) as $value) {
        $formattedValues[] = Stream::findOne($value)->getStringInfo();
      }
      $this->setCellValue($this->_col + 1, $this->_row, implode(', ', $formattedValues));
      $this->_row++;
    }

    if ($this->_formModel->landingCategories) {
      $this->setCellValue($this->_col, $this->_row, $this->_formModel->getAttributeLabel('landingCategories'));
      $formattedValues = [];
      foreach ($this->getArray($this->_formModel->landingCategories) as $value) {
        $formattedValues[] = LandingCategory::findOne($value)->getStringInfo();
      }
      $this->setCellValue($this->_col + 1, $this->_row, implode(', ', $formattedValues));
      $this->_row++;
    }

    if ($this->_formModel->landings) {
      $this->setCellValue($this->_col, $this->_row, $this->_formModel->getAttributeLabel('landings'));
      $formattedValues = [];
      foreach ($this->getArray($this->_formModel->landings) as $value) {
        $formattedValues[] = Landing::findOne($value)->getStringInfo();
      }
      $this->setCellValue($this->_col + 1, $this->_row, implode(', ', $formattedValues));
      $this->_row++;
    }

    if ($this->_formModel->platforms) {
      $this->setCellValue($this->_col, $this->_row, $this->_formModel->getAttributeLabel('platforms'));
      $formattedValues = [];
      foreach ($this->getArray($this->_formModel->platforms) as $value) {
        $formattedValues[] = Platform::findOne($value)->getStringInfo();
      }
      $this->setCellValue($this->_col + 1, $this->_row, implode(', ', $formattedValues));
      $this->_row++;
    }

    if ($this->_formModel->isFake) {
      $formattedValue = '';
      if ($this->_formModel->isFake && count($this->_formModel->isFake) === 1 && (int)reset($this->_formModel->isFake) === 1) {
        $formattedValue = Yii::_t('statistic.statistic.is_fake_yes');
      }

      if ($this->_formModel->isFake && count($this->_formModel->isFake) === 1 && (int)reset($this->_formModel->isFake) === 0) {
        $formattedValue = Yii::_t('statistic.statistic.is_fake_no');
      }

      if ($formattedValue) {
        $this->setCellValue($this->_col, $this->_row, $this->_formModel->getAttributeLabel('isFake'));
        $this->setCellValue($this->_col + 1, $this->_row, $formattedValue);
        $this->_row++;
      }
    }

    if ($this->_formModel->ltvDateTo) {
      $this->setCellValue($this->_col, $this->_row, $this->_formModel->getAttributeLabel('ltvDateTo'));
      $this->setCellValue($this->_col + 1, $this->_row, Yii::$app->formatter->asDate($this->_formModel->ltvDateTo));
      $this->_row++;
    }
  }

  /**
   * @param $value
   * @return array
   */
  protected function getArray($value)
  {
    if (is_array($value)) {
      return $value;
    }
    return [$value];
  }
}
