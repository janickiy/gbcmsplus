<?php

use mcms\partners\components\mainStat\FiltersDataProvider;
use mcms\partners\components\mainStat\FormModel;
use kartik\date\DatePicker;
use yii\bootstrap\ActiveForm;
use mcms\partners\components\widgets\PriceWidget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/** @var FormModel $model */
/** @var mcms\common\web\View $this */
/** @var string[] $groupBy */
/** @var boolean $isRatioByUniquesEnabled */
/** @var string[] $revshareOrCpaFilter */
/** @var FiltersDataProvider $filtersDataProvider */
/** @var array $filterDatePeriods */
?>


<?php $form = ActiveForm::begin([
  'id' => 'statistic-filter-form',
  'options' => [
    'data-pjax' => true,
  ],
  'fieldConfig' => [
    'template' => '{input}',
    'options' => [
      'tag' => 'span'
    ]
  ]
]); ?>
  <script>
    SETTINGS_MAX_NUMBER_OF_MONTH = <?= Yii::$app->getModule('partners')->getMaxNumberOfMonth()?>;
  </script>

  <div class="row no_m row-filter">
    <div class="col-xs-3 no_p col-dropdowns w100-xs">
      <div class="row no_m">
        <div class="col-xs-5 no_p">
          <?= $form
            ->field($model, 'revshareOrCpa')
            ->dropDownList($revshareOrCpaFilter, [
              'id' => 'revshareOrCPA',
              'class' => 'selectpicker bs-select-hidden select_customs auto_filter',
              'data-width' => '100%',
            ]); ?>
        </div>
        <div class="col-xs-7 no_p">
          <?= $form
            ->field($model, 'groups[]')
            ->dropDownList($groupBy, [
              'id' => 'statistic-group',
              'class' => 'selectpicker bs-select-hidden select_customs auto_filter',
              'data-width' => '100%',
            ])->label(false);
          ?>
        </div>
      </div>
    </div>
    <div class="col-xs-3 text-center no_p w100-xs">
      <div class="btn-group btn-group_custom change_date-period" data-toggle="buttons">
        <?php if (ArrayHelper::getValue($filterDatePeriods, 'today')) { ?>
          <label data-period="today" class="btn btn-sm">
            <input type="radio" name="options"
                   data-start="<?= ArrayHelper::getValue($filterDatePeriods, 'today.from') ?>"
                   data-end="<?= ArrayHelper::getValue($filterDatePeriods, 'today.to') ?>"
                   data-from="<?= ArrayHelper::getValue($filterDatePeriods, 'today.from') ?>"
                   data-to="<?= ArrayHelper::getValue($filterDatePeriods, 'today.to') ?>"
                   autocomplete="off">
            <?= Yii::_t('statistic.statistic.filter_date_today') ?>
          </label>
        <?php } ?>
        <?php if (ArrayHelper::getValue($filterDatePeriods, 'yesterday')) {  ?>
          <label data-period="yesterday" class="btn btn-sm">
            <input type="radio" name="options"
                   data-start="<?= ArrayHelper::getValue($filterDatePeriods, 'yesterday.from') ?>"
                   data-end="<?= ArrayHelper::getValue($filterDatePeriods, 'yesterday.to') ?>"
                   data-from="<?= ArrayHelper::getValue($filterDatePeriods, 'yesterday.from') ?>"
                   data-to="<?= ArrayHelper::getValue($filterDatePeriods, 'yesterday.to') ?>"
                   autocomplete="off">
            <?= Yii::_t('statistic.statistic.filter_date_yesterday') ?>
          </label>
        <?php } ?>
        <?php if (ArrayHelper::getValue($filterDatePeriods, 'week')) { ?>
          <label data-period="week" class="btn btn-sm active">
            <input type="radio" name="options"
                   data-start="<?= ArrayHelper::getValue($filterDatePeriods, 'week.from') ?>"
                   data-end="<?= ArrayHelper::getValue($filterDatePeriods, 'week.to') ?>"
                   data-from="<?= ArrayHelper::getValue($filterDatePeriods, 'week.from') ?>"
                   data-to="<?= ArrayHelper::getValue($filterDatePeriods, 'week.to') ?>"
                   autocomplete="off" checked><?= Yii::_t('statistic.statistic.filter_date_week') ?>
          </label>
        <?php } ?>
        <?php if (ArrayHelper::getValue($filterDatePeriods, 'month')) { ?>
          <label data-period="month" class="btn btn-sm hidden_mobile">
            <input type="radio" name="options"
                   data-start="<?= ArrayHelper::getValue($filterDatePeriods, 'month.from') ?>"
                   data-end="<?= ArrayHelper::getValue($filterDatePeriods, 'month.to') ?>"
                   data-from="<?= ArrayHelper::getValue($filterDatePeriods, 'month.from') ?>"
                   data-to="<?= ArrayHelper::getValue($filterDatePeriods, 'month.to') ?>"
                   autocomplete="off"><?= Yii::_t('statistic.statistic.filter_date_month') ?>
          </label>
        <?php } ?>
        <label class="btn btn-sm visible_mobile">
          <input type="radio" name="options" autocomplete="off"><?= Yii::_t('statistic.period') ?>
        </label>
      </div>
    </div>
    <div class="col-xs-6 col-filters-xs no_p">
      <div class="row no_m">
        <div class="col-xs-5 text-center border-r no_p dp_container">
          <?php
          $model->dateFrom = date('d.m.Y', strtotime($model->dateFrom));
          $model->dateTo = date('d.m.Y', strtotime($model->dateTo));

          echo DatePicker::widget([
            'model' => $model,
            'attribute' => 'dateFrom',
            'attribute2' => 'dateTo',
            'type' => DatePicker::TYPE_RANGE,
            'layout' => '<span class="input-group-addon"></span>' .
            '{input1}{separator}{input2}' .
              '<span class="input-group-addon kv-date-remove"><i class="glyphicon glyphicon-remove"></i></span>',
            'separator' => '-',
            'pluginOptions' => [
              'format' => 'dd.mm.yyyy',
              'startDate' => '-6m',
              'endDate' => 'today',
              'todayHighlight' => true,
            ],
            'pluginEvents' => [
              'changeDate' => 'function(e) { setDpDate(e.target.id, true); }'
            ],
            'options' => ['readonly' => true, 'id' => 'statistic-start_date'], // id нужны для statistic.js
            'options2' => ['id' => 'statistic-end_date'], // id нужны для statistic.js
          ]); ?>
          <div id="dp_mobile" class="input-group input-daterange date_filter">
            <input id="m_statistic-start_date" type="date" class="form-control" value="" title="">
            <input id="m_statistic-end_date" type="date" class="form-control" value="" title="">
          </div>
        </div>

        <div class="col-xs-7 w100-xs">
          <div class="row">
            <div class="col-xs-4 no_p border-r">
              <div class="collapse_filters" data-target="#settings"><i
                  class="icon-filter"></i><span><?= Yii::_t('main.filters') ?></span></div>
            </div>
            <div class="col-xs-4 no_p table_filter">
              <select id="table_filter" class="selectpicker bs-select-hidden select_customs select_customs-table"
                      multiple
                      data-width="100%"
                      data-selected-text-format="static"
                      title="<?= Yii::_t('statistic.table') ?>">

                <optgroup data-optgroup="1" label="<?= Yii::_t('statistic.traffic') ?>">
                  <option value="1"><?= Yii::_t('statistic.statistic_traffic-hits') ?></option>
                  <option value="2"><?= Yii::_t('statistic.statistic_traffic-uniques') ?></option>
                  <option value="3"><?= Yii::_t('statistic.statistic_traffic-tb') ?></option>
                  <option value="4"><?= Yii::_t('statistic.statistic_traffic-accepted_full') ?></option>
                </optgroup>
                <optgroup data-optgroup="2" label="<?= Yii::_t('statistic.statistic_revshare') ?>">
                  <option value="5"><?= Yii::_t('statistic.statistic_revshare-ons_full') ?></option>
                  <option value="6"><?= Yii::_t('statistic.statistic_revshare-ratio') ?></option>
                  <option value="7"><?= Yii::_t('statistic.statistic_revshare-offs_full') ?></option>
                  <option value="8"><?= Yii::_t('statistic.statistic_revshare-rebills_full') ?></option>
                  <option value="9"
                          data-grid="9, 10, 11"><?= Yii::_t('statistic.statistic_revshare-sum_full') ?> </option>
                </optgroup>
                <optgroup data-optgroup="3" label="<?= Yii::_t('main.cpa') ?>">
                  <option value="10" data-grid="12"><?= Yii::_t('statistic.statistic_cpa-count_full') ?></option>
                  <option value="11" data-grid="13, 14, 15"
                          data-content='<?= Yii::_t('statistic.statistic_cpa-ecpm', ['currency' => PriceWidget::widget(['currency' => $model->currency])]) ?>'></option>
                  <option value="12" data-grid="16"><?= Yii::_t('statistic.statistic_cpa-ratio') ?></option>
                  <option value="13" data-grid="17, 18, 19"
                          data-content='<?= Yii::_t('statistic.statistic_cpa-count_curr', ['currency' => PriceWidget::widget(['currency' => $model->currency])]) ?>'></option>
                </optgroup>
              </select>
            </div>
            <div class="col-xs-4 hidden_mobile no_p border-r">
              <div data-target="#export" class="collapse_filters"><i
                  class="icon-export"></i><span><?= Yii::_t('main.export') ?></span></div>
            </div>
          </div>
        </div>


      </div>

    </div>
  </div>

<?php
$cols = 0;
$colsInRow = 5;
?>

  <div id="settings" class="statistics_collapsed">
    <div class="row">

      <?php $this->beginBlockAccessVerifier('sources', ['StatisticFilterBySources']); ?>
      <?php $webmasterSources = $filtersDataProvider->getWebmasterSources(); ?>
      <?php if (count($webmasterSources) > 0) { ?>
        <?php $cols++ ?>
        <div class="col-xs-20">
          <div class="filter">
            <div class="filter-header">
              <span><?= Yii::_t('statistic.source') ?>
                <i><?= $model->webmasterSources ? '(' . count($model->webmasterSources) . ')' : '' ?></i></i></span>
              <div class="caret_wrap">
                <i class="caret"></i>
              </div>
            </div>
            <div class="filter-body filter-body_left">
              <div class="filter-body_search">
                <i class="icon-search"></i>
                <input type="text" class="form-control" placeholder="<?= Yii::_t('main.quick_search') ?>">
                <span class="reset_search">
                <i class="icon-cancel_4"></i>
              </span>
              </div>
              <div class="filter-body_selected">
                <?php if (!$model->webmasterSources) { ?>
                  <div class="hidden_text"><?= Yii::_t('statistic.no_source_selected') ?></div>
                <?php } ?>
                <?php foreach ($webmasterSources as $id => $name) { ?>
                  <?php if ($model->webmasterSources && in_array($id, $model->webmasterSources, false)) { ?>
                    <div class="checkbox checkbox-inline">
                      <input type="checkbox" checked class="styled" name="FormModel[webmasterSources][]"
                             id="ids<?= $id ?>" value="<?= $id ?>">
                      <label for="ids<?= $id ?>"><?= $id ?>. <?= $name ?></label>
                    </div>
                  <?php } ?>
                <?php } ?>
              </div>
              <div class="filter-body_deselected">
                <div class="hidden_text"><?= Yii::_t('main.selected_all_options') ?></div>
                <div class="form-group">
                  <?php foreach ($webmasterSources as $id => $name) { ?>
                    <?php if (!($model->webmasterSources && in_array($id, $model->webmasterSources, false))) { ?>
                      <div class="checkbox checkbox-inline">
                        <input type="checkbox" class="styled" name="FormModel[webmasterSources][]" id="ids<?= $id ?>"
                               value="<?= $id ?>">
                        <label for="ids<?= $id ?>"><?= $id ?>. <?= $name ?></label>
                      </div>
                    <?php } ?>
                  <?php } ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php } ?>
      <?php $this->endBlockAccessVerifier(); ?>

      <?php $this->beginBlockAccessVerifier('sources', ['StatisticFilterBySources']); ?>
      <?php $arbitraryLinksByStreams = $filtersDataProvider->getArbitraryLinksByStreams(); ?>
      <?php if (count($arbitraryLinksByStreams) > 0) { ?>
        <?php $cols++ ?>
        <div class="col-xs-20">
          <div class="filter">
            <div class="filter-header">
              <span><?= Yii::_t('main.links') ?>
                <i><?= $model->arbitraryLinks ? '(' . count($model->arbitraryLinks) . ')' : '' ?></i></i></span>
              <div class="caret_wrap">
                <i class="caret"></i>
              </div>
            </div>
            <div class="filter-body filter-body_left">
              <div class="filter-body_search">
                <i class="icon-search"></i>
                <input type="text" class="form-control" placeholder="<?= Yii::_t('main.quick_search') ?>">
                <span class="reset_search">
                <i class="icon-cancel_4"></i>
              </span>
              </div>
              <div class="filter-body_selected"<?= $model->arbitraryLinks ? ' style="display: block;"' : '' ?>>
                <?php $streamSelector = 0; ?>
                <?php foreach ($arbitraryLinksByStreams as $stream) { ?>
                  <?php $streamSelector++; ?>
                  <?php if ($model->arbitraryLinks && array_intersect(array_keys($stream['links']), $model->arbitraryLinks)) { ?>
                    <div class="cb_group" data-l1="<?= substr(md5($stream['name']), 0, 10) ?>">
                      <div class="cb_group-name">
                        <div class="checkbox checkbox-inline cb_g">
                          <input type="checkbox" class="styled" id="cb_g_s<?= $streamSelector ?>">
                          <label for="cb_g_s<?= $streamSelector ?>"></label>
                        </div>
                        <span><?= $stream['id'] . '. ' . $stream['name'] ?></span> <i class="icon-down2"></i>
                      </div>
                      <div class="cb_group-list"<?= $streamSelector === 1 ? 'style="display: block;"' : '' ?>
                           data-opened="<?= $streamSelector === 1 ? 1 : 0 ?>">
                        <div class="form-group">
                          <?php foreach ($stream['links'] as $id => $name) { ?>
                            <div class="checkbox checkbox-inline cb_g_s<?= $streamSelector ?>" data-l2="<?= $id ?>">
                              <input type="checkbox"
                                     name="FormModel[arbitraryLinks][]" <?= in_array($id, $model->arbitraryLinks, false) ? 'checked' : '' ?>
                                     class="styled" id="idl<?= $id ?>" value="<?= $id ?>">
                              <label for="idl<?= $id ?>"><?= Html::encode($name) ?></label>
                            </div>
                          <?php } ?>
                        </div>
                      </div>
                    </div>
                  <?php } ?>
                <?php } ?>
              </div>
              <div class="filter-body_deselected">
                <div class="hidden_text"></div>
                <?php $streamSelector = 0; ?>
                <?php foreach ($arbitraryLinksByStreams as $stream) { ?>
                  <?php $streamSelector++; ?>
                  <?php if (!($model->arbitraryLinks && array_intersect(array_keys($stream['links']), $model->arbitraryLinks))) { ?>
                    <div class="cb_group" data-l1="<?= substr(md5($stream['name']), 0, 10) ?>">
                      <div class="cb_group-name">
                        <div class="checkbox checkbox-inline cb_g">
                          <input type="checkbox" class="styled" id="cb_g_s<?= $streamSelector ?>">
                          <label for="cb_g_s<?= $streamSelector ?>"></label>
                        </div>
                        <span><?= $stream['id'] . '. ' . $stream['name'] ?></span> <i class="icon-down2"></i>
                      </div>
                      <div class="cb_group-list"<?= $streamSelector === 1 ? 'style="display: block;"' : '' ?>
                           data-opened="<?= $streamSelector === 1 ? 1 : 0 ?>">
                        <div class="form-group">
                          <?php foreach ($stream['links'] as $id => $name) { ?>
                            <div class="checkbox checkbox-inline cb_g_s<?= $streamSelector ?>" data-l2="<?= $id ?>">
                              <input type="checkbox" name="FormModel[arbitraryLinks][]" class="styled"
                                     id="idl<?= $id ?>" value="<?= $id ?>">
                              <label for="idl<?= $id ?>"><?= $id . '. ' . Html::encode($name) ?></label>
                            </div>
                          <?php } ?>
                        </div>
                      </div>
                    </div>
                  <?php } ?>
                <?php } ?>
              </div>
            </div>
          </div>
        </div>
      <?php } ?>
      <?php $this->endBlockAccessVerifier(); ?>

      <?php $this->beginBlockAccessVerifier('operators', ['StatisticFilterByOperators']); ?>
      <?php $cols++ ?>
      <div class="col-xs-20">
        <div class="filter">
          <div class="filter-header">
            <span><?= Yii::_t('main.operators') ?>
              <i><?= $model->operators ? '(' . count($model->operators) . ')' : '' ?></i></i></span>
            <div class="caret_wrap">
              <i class="caret"></i>
            </div>
          </div>
          <div class="filter-body filter-body_left">
            <div class="filter-body_search">
              <i class="icon-search"></i>
              <input type="text" class="form-control" placeholder="<?= Yii::_t('main.quick_search') ?>">
              <span class="reset_search">
                <i class="icon-cancel_4"></i>
              </span>
            </div>
            <div class="filter-body_selected"<?= $model->operators ? ' style="display: block;"' : '' ?>>
              <?php $countrySelector = 0; ?>
              <?php foreach ($filtersDataProvider->getOperatorsGroupedByCountry() as $country => $operators) { ?>
                <?php $countrySelector++; ?>
                <?php if ($model->operators && array_intersect(array_keys($operators), $model->operators)) { ?>
                  <div class="cb_group" data-l1="<?= substr(md5($country), 0, 10) ?>">
                    <div class="cb_group-name">
                      <div class="checkbox checkbox-inline cb_g">
                        <input type="checkbox" class="styled" id="cb_g_c<?= $countrySelector ?>">
                        <label for="cb_g_c<?= $countrySelector ?>"></label>
                      </div>
                      <span><?= $country ?></span> <i class="icon-down2"></i>
                    </div>
                    <div
                      class="cb_group-list"<?= $countrySelector === 1 ? 'style="display: block;"' : '' ?>
                      data-opened="<?= ($countrySelector === 1) ? 1 : 0 ?>">
                      <div class="form-group">
                        <?php foreach ($operators as $id => $name) { ?>
                          <div class="checkbox checkbox-inline cb_g_c<?= $countrySelector ?>" data-l2="<?= $id ?>">
                            <input type="checkbox"
                                   name="FormModel[operators][]" <?= in_array($id, $model->operators, false) ? 'checked' : '' ?>
                                   class="styled" id="op<?= $id ?>" value="<?= $id ?>">
                            <label for="op<?= $id ?>"><?= $name ?></label>
                          </div>
                        <?php } ?>
                      </div>
                    </div>
                  </div>
                <?php } ?>
            <?php } ?>
            </div>
            <div class="filter-body_deselected">
              <div class="hidden_text"></div>
              <?php $countrySelector = 0; ?>
              <?php foreach ($filtersDataProvider->getOperatorsGroupedByCountry() as $country => $operators) { ?>
                <?php $countrySelector++; ?>
                <?php if (!($model->operators && array_intersect_key($operators, $model->operators))) { ?>
                  <div class="cb_group" data-l1="<?= substr(md5($country), 0, 10) ?>">
                    <div class="cb_group-name">
                      <div class="checkbox checkbox-inline cb_g">
                        <input type="checkbox" class="styled" id="cb_g_c<?= $countrySelector ?>">
                        <label for="cb_g_c<?= $countrySelector ?>"></label>
                      </div>
                      <span><?= $country ?></span> <i class="icon-down2"></i>
                    </div>
                    <div
                      class="cb_group-list"<?= $countrySelector === 1 ? 'style="display: block;"' : '' ?>
                      data-opened="<?= ($countrySelector === 1) ? 1 : 0 ?>">
                      <div class="form-group">
                        <?php foreach ($operators as $id => $name) { ?>
                          <div class="checkbox checkbox-inline cb_g_c<?= $countrySelector ?>" data-l2="<?= $id ?>">
                            <input type="checkbox" name="FormModel[operators][]" class="styled" id="op<?= $id ?>"
                                   value="<?= $id ?>">
                            <label for="op<?= $id ?>"><?= $name ?></label>
                          </div>
                        <?php } ?>
                      </div>
                    </div>
                  </div>
                <?php } ?>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>
      <?php $this->endBlockAccessVerifier(); ?>

      <?php $this->beginBlockAccessVerifier('platforms', ['StatisticFilterByPlatforms']); ?>
      <?php $cols++ ?>
      <div class="col-xs-20">
        <div class="filter">
          <div class="filter-header">
            <span><?= Yii::_t('main.platforms') ?><i><?= $model->platforms ? '(' . count($model->platforms) . ')' : '' ?></i></i></span>
            <div class="caret_wrap">
              <i class="caret"></i>
            </div>
          </div>
          <div class="filter-body filter-body_left">
            <div class="filter-body_search">
              <i class="icon-search"></i>
              <input type="text" class="form-control" placeholder="<?= Yii::_t('main.quick_search') ?>">
              <span class="reset_search">
                <i class="icon-cancel_4"></i>
              </span>
            </div>
            <div class="filter-body_selected">
              <?php if (!$model->platforms) { ?>
                <div class="hidden_text"><?= Yii::_t('statistic.no_platforms_selected') ?></div>
              <?php } ?>
              <?php foreach ($filtersDataProvider->getPlatforms() as $id => $name) { ?>
                <?php if ($model->platforms && in_array($id, $model->platforms, false)) { ?>
                  <div class="checkbox checkbox-inline">
                    <input type="checkbox" checked class="styled" name="FormModel[platforms][]" id="idpl<?=$id?>" value="<?=$id?>">
                    <label for="idpl<?=$id?>"><?=$name?></label>
                  </div>
                <?php } ?>
              <?php } ?>
            </div>
            <div class="filter-body_deselected">
              <div class="hidden_text"><?= Yii::_t('main.selected_all_options') ?></div>
              <div class="form-group">
                <?php foreach ($filtersDataProvider->getPlatforms() as $id => $name) { ?>
                  <?php if (!($model->platforms && in_array($id, $model->platforms, false))) { ?>
                    <div class="checkbox checkbox-inline">
                      <input type="checkbox" class="styled" name="FormModel[platforms][]" id="idpl<?= $id ?>"
                             value="<?= $id ?>">
                      <label for="idpl<?= $id ?>"><?= $name ?></label>
                    </div>
                  <?php } ?>
                <?php } ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php $this->endBlockAccessVerifier(); ?>

      <?php $this->beginBlockAccessVerifier('landings', ['StatisticFilterByLandings']); ?>
      <?php $cols++ ?>
      <div class="col-xs-20">
        <div class="filter">
          <div class="filter-header">
            <span><?= Yii::_t('main.landings') ?><i><?= $model->landings ? '(' . count($model->landings) . ')' : '' ?></i></i></span>
            <div class="caret_wrap">
              <i class="caret"></i>
            </div>
          </div>
          <div class="filter-body filter-body_right">
            <div class="filter-body_search">
              <i class="icon-search"></i>
              <input type="text" class="form-control" placeholder="<?= Yii::_t('main.quick_search') ?>">
              <span class="reset_search">
                <i class="icon-cancel_4"></i>
              </span>
            </div>
            <div class="filter-body_selected"<?= $model->landings ? ' style="display: block;"' : '' ?>>
              <?php $countrySelector = 0; ?>
              <?php foreach ($filtersDataProvider->getLandingsByCountry() as $country => $landings) { ?>
                <?php $countrySelector++; ?>
                <?php if ($model->landings && array_intersect(array_keys($landings), $model->landings)) { ?>
                  <div class="cb_group" data-l1="<?= substr(md5($country), 0, 10) ?>">
                    <div class="cb_group-name">
                      <div class="checkbox checkbox-inline cb_g">
                        <input type="checkbox" class="styled" id="cb_g_lc<?= $countrySelector ?>">
                        <label for="cb_g_lc<?= $countrySelector ?>"></label>
                      </div>
                      <span><?= $country ?></span> <i class="icon-down2"></i>
                    </div>
                    <div class="cb_group-list"<?= $countrySelector === 1 ? 'style="display: block;"' : '' ?>
                         data-opened="<?= $countrySelector === 1 ?>">
                      <div class="form-group">
                        <?php foreach ($landings as $id => $name) { ?>
                          <div class="checkbox checkbox-inline cb_g_lc<?= $countrySelector ?>" data-l2="<?= $id ?>">
                            <input type="checkbox"
                                   name="FormModel[landings][]" <?= in_array($id, $model->landings, false) ? 'checked' : '' ?>
                                   class="styled" id="idp<?= $id ?>" value="<?= $id ?>">
                            <label for="idp<?= $id ?>"><?= $id ?>. <?= $name ?></label>
                          </div>
                        <?php } ?>
                      </div>
                    </div>
                  </div>
                <?php } ?>
              <?php } ?>
            </div>
            <div class="filter-body_deselected">
              <div class="hidden_text"></div>
              <?php $countrySelector = 0; ?>
              <?php foreach ($filtersDataProvider->getLandingsByCountry() as $country => $landings) { ?>
                <?php $countrySelector++; ?>
                <?php if (!($model->landings && array_intersect_key($landings, $model->landings))) { ?>
                  <div class="cb_group" data-l1="<?= substr(md5($country), 0, 10) ?>">
                    <div class="cb_group-name">
                      <div class="checkbox checkbox-inline cb_g">
                        <input type="checkbox" class="styled" id="cb_g_lc<?= $countrySelector ?>">
                        <label for="cb_g_lc<?= $countrySelector ?>"></label>
                      </div>
                      <span><?= $country ?></span> <i class="icon-down2"></i>
                    </div>
                    <div class="cb_group-list"<?= $countrySelector === 1 ? 'style="display: block;"' : '' ?>
                         data-opened="<?= $countrySelector === 1 ?>">
                      <div class="form-group">
                        <?php foreach ($landings as $id => $name) { ?>
                          <div class="checkbox checkbox-inline cb_g_lc<?= $countrySelector ?>" data-l2="<?= $id ?>">
                            <input type="checkbox" name="FormModel[landings][]" class="styled" id="idp<?= $id ?>"
                                   value="<?= $id ?>">
                            <label for="idp<?= $id ?>"><?= $id ?>. <?= $name ?></label>
                          </div>
                        <?php }?>
                      </div>
                    </div>
                  </div>
                <?php }?>
              <?php }?>
            </div>
          </div>
        </div>
      </div>
      <?php $this->endBlockAccessVerifier(); ?>

      <?php if ($isRatioByUniquesEnabled) { ?>
        <div class="col-xs-20">
          <div class="form-group">
            <div class="checkbox checkbox-inline">
              <input type="checkbox" name="FormModel[isRatioByUniques]" id="isRatioByUniques-0" value="0" checked>
              <input type="checkbox" name="FormModel[isRatioByUniques]" id="isRatioByUniques" value="1" checked>
              <label for="isRatioByUniques"><?= Yii::_t('statistic.statistic.isRatioByUniques') ?></label>
            </div>
          </div>
        </div>
      <?php } ?>

      <?php $buttonColsOffset = $colsInRow - ($cols % $colsInRow) - ($isRatioByUniquesEnabled ? 2 : 1) ?>
      <div class="col-xs-20 col-xs-offset-<?= 20 * $buttonColsOffset ?>">
        <button class="btn btn-primary refresh_stat"><?= Yii::_t('main.apply') ?>
          <div><i class="icon-clock"></i><span data-count="300">5:00</span></div>
        </button>
      </div>
    </div>
  </div>


<?php ActiveForm::end();