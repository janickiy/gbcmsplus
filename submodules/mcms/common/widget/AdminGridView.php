<?php

namespace mcms\common\widget;

use kartik\grid\GridView;
use mcms\common\helpers\Html;
use Yii;

class AdminGridView extends GridView
{
  const DEFAULT_PAGE_SIZE = 20;

  public static $autoIdPrefix = 'agv';
  public $layout = "{items}
      <div class=\"dt-toolbar-footer\">
      <div class=\"col-sm-6 col-xs-12 hidden-xs\">
        <div class=\"dataTables_info\" id=\"dt_basic_info\" role=\"status\" aria-live=\"polite\">{summary}
        </div>
      </div>
      <div class=\"col-xs-12 col-sm-6 dataTables_paginate paging_simple_numbers\">{pager}</div>
    </div>";
//  public $filterPosition = parent::FILTER_POS_HEADER;
  public $export = false;
  public $dataColumnClass = 'mcms\common\widget\AdminDataColumn';
  public $resizableColumns = false;

  /* TRICKY: иначе картик суёт свой оранжевый конфирм */
  public $krajeeDialogSettings = ['overrideYiiConfirm' => false];

  public function init()
  {
    parent::init();
    Html::addCssClass($this->tableOptions, 'table table-striped table-bordered table-hover dataTable no-footer');

    if ($this->dataProvider->getPagination()) {
      $this->dataProvider->pagination->defaultPageSize = $this->getPageSize();
    }

    // По умолчанию, картиковский грид подменяет конфирмы Yii своими
    if (!isset($this->krajeeDialogSettings['overrideYiiConfirm'])) {
      $this->krajeeDialogSettings['overrideYiiConfirm'] = false;
    }

    /** @var \mcms\user\Module $userModule */
    $userModule = Yii::$app->getModule('users');
    if (!Yii::$app->user->can($userModule::PERMISSION_CAN_MANAGE_ALL_USERS)) {
      $this->emptyText = Yii::_t('commonMsg.main.assigned_users_elements_list_empty');
    }
  }

  /**
   * получаем pageSize для грида
   * @return int
   */
  private function getPageSize()
  {
    $gridPageSize = Yii::$app->getModule('promo')->api('userPromoSettings')->getGridPageSize(Yii::$app->user->id);
    return $gridPageSize ?: self::DEFAULT_PAGE_SIZE;
  }
}