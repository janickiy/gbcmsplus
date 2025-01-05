<?php

use yii\bootstrap\ActiveForm;
use kartik\date\DatePicker;
use mcms\partners\components\widgets\PriceWidget;
use mcms\common\helpers\Html;
use mcms\common\helpers\ArrayHelper;
use mcms\partners\components\widgets\StatisticCustomFilterWidget;
use mcms\partners\components\widgets\StatisticCustomFilterSettingsWidget;

/** @var \mcms\partners\components\subidStat\FormModel $model */
/** @var mcms\common\web\View $this */
/** @var array $groupBy */
/** @var array $filterDatePeriods */
/** @var boolean $shouldHideGrouping */
/** @var \mcms\partners\components\mainStat\FiltersDataProvider $filtersDP */
/** @var string[] $revshareOrCpaFilter */
?>

<?php $form = ActiveForm::begin([
  'id' => 'statistic-filter-form',
  'options' => [
    'data-pjax' => true,
  ],
  'fieldConfig' => [
    'template' => "{input}",
    'options' => [
      'tag' => 'span'
    ]
  ]
]); ?>


  <div class="row no_m row-filter">
    <div class="col-xs-3 no_p col-dropdowns w100-xs">
      <div class="row no_m">
        <div class="col-xs-5 no_p">
          <?= $form
            ->field($model, 'revshareOrCpa')
            ->dropDownList($revshareOrCpaFilter,
              [
                'id' => 'revshareOrCPA',
                'class' => 'selectpicker bs-select-hidden select_customs auto_filter',
                'data-width' => '100%',
              ]
            ); ?>
        </div>
        <div class="col-xs-7 no_p">
          <?= $form
            ->field($model, 'group')
            ->dropDownList($groupBy, [
              'class' => 'selectpicker bs-select-hidden select_customs auto_filter',
              'data-width' => '100%',
            ])->label(false);
          ?>

        </div>
      </div>

    </div>

    <div class="col-xs-3 text-center no_p w100-xs">
      <div class="btn-group btn-group_custom change_date-period" data-toggle="buttons">
        <?php if (ArrayHelper::getValue($filterDatePeriods, 'today')): ?>
          <label class="btn btn-sm">
            <input type="radio" name="options"
                   data-start="<?= ArrayHelper::getValue($filterDatePeriods, 'today.from') ?>"
                   data-end="<?= ArrayHelper::getValue($filterDatePeriods, 'today.to') ?>"
                   data-from="<?= ArrayHelper::getValue($filterDatePeriods, 'today.from') ?>"
                   data-to="<?= ArrayHelper::getValue($filterDatePeriods, 'today.to') ?>"
                   autocomplete="off">
            <?= Yii::_t('statistic.statistic.filter_date_today') ?>
          </label>
        <?php endif ?>
        <?php if (ArrayHelper::getValue($filterDatePeriods, 'yesterday')): ?>
          <label class="btn btn-sm">
            <input type="radio" name="options"
                   data-start="<?= ArrayHelper::getValue($filterDatePeriods, 'yesterday.from') ?>"
                   data-end="<?= ArrayHelper::getValue($filterDatePeriods, 'yesterday.to') ?>"
                   data-from="<?= ArrayHelper::getValue($filterDatePeriods, 'yesterday.from') ?>"
                   data-to="<?= ArrayHelper::getValue($filterDatePeriods, 'yesterday.to') ?>"
                   autocomplete="off">
            <?= Yii::_t('statistic.statistic.filter_date_yesterday') ?>
          </label>
        <?php endif ?>
        <?php if (ArrayHelper::getValue($filterDatePeriods, 'week')): ?>
          <label class="btn btn-sm active">
            <input type="radio" name="options"
                   data-start="<?= ArrayHelper::getValue($filterDatePeriods, 'week.from') ?>"
                   data-end="<?= ArrayHelper::getValue($filterDatePeriods, 'week.to') ?>"
                   data-from="<?= ArrayHelper::getValue($filterDatePeriods, 'week.from') ?>"
                   data-to="<?= ArrayHelper::getValue($filterDatePeriods, 'week.to') ?>"
                   autocomplete="off" checked><?= Yii::_t('statistic.statistic.filter_date_week') ?>
          </label>
        <?php endif ?>
        <?php if (ArrayHelper::getValue($filterDatePeriods, 'month')): ?>
          <label class="btn btn-sm hidden_mobile">
            <input type="radio" name="options"
                   data-start="<?= ArrayHelper::getValue($filterDatePeriods, 'month.from') ?>"
                   data-end="<?= ArrayHelper::getValue($filterDatePeriods, 'month.to') ?>"
                   data-from="<?= ArrayHelper::getValue($filterDatePeriods, 'month.from') ?>"
                   data-to="<?= ArrayHelper::getValue($filterDatePeriods, 'month.to') ?>"
                   autocomplete="off"><?= Yii::_t('statistic.statistic.filter_date_month') ?>
          </label>
        <?php endif ?>
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
              'startDate' => '-3m',
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
            <input id="m_statistic-dateFrom" type="date" class="form-control" value="">
            <input id="m_statistic-dateTo" type="date" class="form-control" value="">
          </div>
        </div>
        <div class="col-xs-7 w100-xs">
          <div class="row">
            <div class="col-xs-4 no_p border-r">
              <div class="collapse_filters" data-target="#settings"><i
                  class="icon-filter"></i><span><?= Yii::_t('main.filters') ?></span></div>
            </div>
            <div class="col-xs-4 no_p table_filter">

              <select id="table_filter"
                      class="label_table selectpicker bs-select-hidden select_customs select_customs-table"
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
                  <option value="9"><?= Yii::_t('statistic.statistic_revshare-sum_full') ?> </option>
                </optgroup>
                <optgroup data-optgroup="3" label="<?= Yii::_t('main.cpa') ?>">
                  <option value="10"><?= Yii::_t('statistic.statistic_cpa-count_full') ?></option>
                  <option value="11"
                          data-content='<?= Yii::_t('statistic.statistic_cpa-ecpm', ['currency' => PriceWidget::widget(['currency' => $model->getCurrency()])]) ?>'></option>
                  <option value="12"><?= Yii::_t('statistic.statistic_cpa-ratio') ?></option>
                  <option value="13"
                          data-content='<?= Yii::_t('statistic.statistic_cpa-count_curr', ['currency' => PriceWidget::widget(['currency' => $model->getCurrency()])]) ?>'></option>
                </optgroup>
              </select>
            </div>
            <div class="col-xs-4 hidden_mobile <?php /*collapse_exp*/ ?> no_p border-r">
              <div data-target="#export" class="collapse_filters"><i
                  class="icon-export"></i><span><?= Yii::_t('main.export') ?></span></div>
            </div>
          </div>
        </div>


      </div>

    </div>
  </div>

  <div id="settings" class="statistics_collapsed">
    <div class="row">
      <?php $this->beginBlockAccessVerifier('sources', ['StatisticFilterBySources']); ?>
      <div class="col-xs-20">
        <div class="filter">
          <div class="filter-header">
            <span><?= Yii::_t('statistic.source') ?>
              <i><?= $model->sources ? '(' . count($model->sources) . ')' : '' ?></i></i></span>
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
              <?php if (!$filtersDP->getWebmasterSources()): ?>
                <div class="hidden_text"><?= Yii::_t('statistic.no_source_selected') ?></div><?php endif; ?>
              <?php foreach ($filtersDP->getWebmasterSources() as $id => $name): ?>
                <?php if ($filtersDP->getWebmasterSources() && in_array($id, $filtersDP->getWebmasterSources())): ?>
                  <div class="checkbox checkbox-inline">
                    <input type="checkbox" checked class="styled" name="FormModel[webmasterSources][]"
                           id="ids<?= $id ?>" value="<?= $id ?>">
                    <label for="ids<?= $id ?>"><?= $id ?>. <?= Html::encode($name) ?></label>
                  </div>
                <?php endif ?>
              <?php endforeach; ?>
            </div>
            <div class="filter-body_deselected">
              <div class="hidden_text"><?= Yii::_t('main.selected_all_options') ?></div>
              <div class="form-group">
                <?php foreach ($filtersDP->getWebmasterSources() as $id => $name): ?>
                  <?php if (!($filtersDP->getWebmasterSources() && in_array($id, $filtersDP->getWebmasterSources()))): ?>
                    <div class="checkbox checkbox-inline">
                      <input type="checkbox" class="styled" name="FormModel[webmasterSources][]" id="ids<?= $id ?>"
                             value="<?= $id ?>">
                      <label for="ids<?= $id ?>"><?= $id ?>. <?= Html::encode($name) ?></label>
                    </div>
                  <?php endif ?>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php $this->endBlockAccessVerifier(); ?>
      <?php $this->beginBlockAccessVerifier('streams', ['StatisticFilterByStreams']); ?>
      <div class="col-xs-20">
        <div class="filter">
          <div class="filter-header">
            <span><?= Yii::_t('main.streams') ?>
              <i><?= $model->streams ? '(' . count($model->streams) . ')' : '' ?></i></i></span>
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
              <?php if (!$filtersDP->getStreams()): ?>
                <div class="hidden_text"><?= Yii::_t('main.no_stream_selected') ?></div><?php endif; ?>
              <?php foreach ($filtersDP->getStreams() as $id => $name): ?>
                <?php if ($filtersDP->getStreams() && in_array($id, $filtersDP->getStreams())): ?>
                  <div class="checkbox checkbox-inline">
                    <input type="checkbox" checked class="styled" name="FormModel[streams][]" id="idst<?= $id ?>"
                           value="<?= $id ?>">
                    <label for="idst<?= $id ?>"><?= $id ?>. <?= Html::encode($name) ?></label>
                  </div>
                <?php endif ?>
              <?php endforeach; ?>
            </div>
            <div class="filter-body_deselected">
              <div class="hidden_text"><?= Yii::_t('main.selected_all_options') ?></div>
              <div class="form-group">
                <?php foreach ($filtersDP->getStreams() as $id => $name): ?>
                  <?php if (!($filtersDP->getStreams() && in_array($id, $filtersDP->getStreams()))): ?>
                    <div class="checkbox checkbox-inline">
                      <input type="checkbox" class="styled" name="FormModel[streams][]" id="idst<?= $id ?>"
                             value="<?= $id ?>">
                      <label for="idst<?= $id ?>"><?= $id ?>. <?= Html::encode($name) ?></label>
                    </div>
                  <?php endif ?>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php $this->endBlockAccessVerifier(); ?>
      <?php $this->beginBlockAccessVerifier('sources', ['StatisticFilterBySources']); ?>
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
            <div class="filter-body_selected"<?= $filtersDP->getArbitraryLinks() ? ' style="display: block;"' : '' ?>>
              <?php $streamSelector = 0;
              foreach ($filtersDP->getArbitraryLinksByStreams() as $stream): $streamSelector++; ?>
                <?php if ($filtersDP->getArbitraryLinks() && array_intersect(array_keys($stream['links']), $filtersDP->getArbitraryLinks())): ?>
                  <div class="cb_group" data-l1="<?= substr(md5($stream['name']), 0, 10) ?>">
                    <div class="cb_group-name">
                      <div class="checkbox checkbox-inline cb_g">
                        <input type="checkbox" class="styled" id="cb_g_s<?= $streamSelector ?>">
                        <label for="cb_g_s<?= $streamSelector ?>"></label>
                      </div>
                      <span><?= $stream['id'] . '. ' . $stream['name'] ?></span> <i class="icon-down2"></i>
                    </div>
                    <div class="cb_group-list"<?php if ($streamSelector == 1): ?> style="display: block;"<?php endif; ?>
                         data-opened="<?= ($streamSelector == 1) ? 1 : 0 ?>">
                      <div class="form-group">
                        <?php foreach ($stream['links'] as $id => $name): ?>
                          <div class="checkbox checkbox-inline cb_g_s<?= $streamSelector ?>">
                            <input type="checkbox"
                                   name="FormModel[arbitraryLinks][]" <?php if (in_array($id, $filtersDP->getArbitraryLinks())): ?> checked<?php endif; ?>
                                   class="styled" id="idl<?= $id ?>" value="<?= $id ?>">
                            <label for="idl<?= $id ?>"><?= $id . '. ' . Html::encode($name) ?></label>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  </div>
                <?php endif ?>
              <?php endforeach; ?>
            </div>
            <div class="filter-body_deselected">
              <div class="hidden_text"></div>
              <?php $streamSelector = 0;
              foreach ($filtersDP->getArbitraryLinksByStreams() as $stream): $streamSelector++; ?>
                <?php if (!($filtersDP->getArbitraryLinks() && array_intersect(array_keys($stream['links']), $filtersDP->getArbitraryLinks()))): ?>
                  <div class="cb_group" data-l1="<?= substr(md5($stream['name']), 0, 10) ?>">
                    <div class="cb_group-name">
                      <div class="checkbox checkbox-inline cb_g">
                        <input type="checkbox" class="styled" id="cb_g_s<?= $streamSelector ?>">
                        <label for="cb_g_s<?= $streamSelector ?>"></label>
                      </div>
                      <span><?= $stream['id'] . '. ' . $stream['name'] ?></span> <i class="icon-down2"></i>
                    </div>
                    <div class="cb_group-list"<?php if ($streamSelector == 1): ?> style="display: block;"<?php endif; ?>
                         data-opened="<?= ($streamSelector == 1) ? 1 : 0 ?>">
                      <div class="form-group">
                        <?php foreach ($stream['links'] as $id => $name): ?>
                          <div class="checkbox checkbox-inline cb_g_s<?= $streamSelector ?>">
                            <input type="checkbox" name="FormModel[arbitraryLinks][]" class="styled" id="idl<?= $id ?>"
                                   value="<?= $id ?>">
                            <label for="idl<?= $id ?>"><?= $id . '. ' . Html::encode($name) ?></label>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  </div>
                <?php endif ?>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
      <?php $this->endBlockAccessVerifier(); ?>
      <?php $this->beginBlockAccessVerifier('countries', ['StatisticFilterByCountries']); ?>
      <div class="col-xs-20">
        <div class="filter">
          <div class="filter-header">
            <span><?= Yii::_t('main.countries') ?>
              <i><?= $model->countries ? '(' . count($model->countries) . ')' : '' ?></i></i></span>
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
              <?php if (!$filtersDP->getCountries()): ?>
                <div class="hidden_text"><?= Yii::_t('statistic.no_countries_selected') ?></div><?php endif; ?>
              <?php foreach ($filtersDP->getCountries() as $id => $name): ?>
                <?php if ($filtersDP->getCountries() && in_array($id, $filtersDP->getCountries())): ?>
                  <div class="checkbox checkbox-inline">
                    <input type="checkbox" checked class="styled" name="FormModel[countries][]" id="idc<?= $id ?>"
                           value="<?= $id ?>">
                    <label for="idc<?= $id ?>"><?= Html::encode($name) ?></label>
                  </div>
                <?php endif ?>
              <?php endforeach; ?>
            </div>
            <div class="filter-body_deselected">
              <div class="hidden_text"><?= Yii::_t('main.selected_all_options') ?></div>
              <div class="form-group">
                <?php foreach ($filtersDP->getCountries() as $id => $name): ?>
                  <?php if (!($filtersDP->getCountries() && in_array($id, $filtersDP->getCountries()))): ?>
                    <div class="checkbox checkbox-inline">
                      <input type="checkbox" class="styled" name="FormModel[countries][]" id="idc<?= $id ?>"
                             value="<?= $id ?>">
                      <label for="idc<?= $id ?>"><?= Html::encode($name) ?></label>
                    </div>
                  <?php endif ?>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php $this->endBlockAccessVerifier(); ?>
      <?php $this->beginBlockAccessVerifier('operators', ['StatisticFilterByOperators']); ?>
      <div class="col-xs-20">
        <div class="filter">
          <div class="filter-header">
            <span><?= Yii::_t('main.operators') ?>
              <i><?= $model->operators ? '(' . count($model->operators) . ')' : '' ?></i></i></span>
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
            <div class="filter-body_selected"<?= $model->operators ? ' style="display: block;"' : '' ?>>
              <?php $countrySelector = 0;
              foreach ($filtersDP->getOperatorsGroupedByCountry() as $country => $operators): $countrySelector++; ?>
                <?php if ($model->operators && array_intersect(array_keys($operators), $model->operators)): ?>
                  <div class="cb_group" data-l1="<?= substr(md5($country), 0, 10) ?>">
                    <div class="cb_group-name">
                      <div class="checkbox checkbox-inline cb_g">
                        <input type="checkbox" class="styled" id="cb_g_c<?= $countrySelector ?>">
                        <label for="cb_g_c<?= $countrySelector ?>"></label>
                      </div>
                      <span><?= $country ?></span> <i class="icon-down2"></i>
                    </div>
                    <div
                      class="cb_group-list"<?php if ($countrySelector == 1): ?> style="display: block;"<?php endif; ?>
                      data-opened="<?= ($countrySelector == 1) ? 1 : 0 ?>">
                      <div class="form-group">
                        <?php foreach ($operators as $id => $name): ?>
                          <div class="checkbox checkbox-inline cb_g_c<?= $countrySelector ?>">
                            <input type="checkbox"
                                   name="FormModel[operators][]" <?php if (in_array($id, $model->operators)): ?> checked<?php endif; ?>
                                   class="styled" id="op<?= $id ?>" value="<?= $id ?>">
                            <label for="op<?= $id ?>"><?= Html::encode($name) ?></label>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  </div>
                <?php endif ?>
              <?php endforeach; ?>
            </div>
            <div class="filter-body_deselected">
              <div class="hidden_text"></div>
              <?php $countrySelector = 0;
              foreach ($filtersDP->getOperatorsGroupedByCountry() as $country => $operators): $countrySelector++; ?>
                <?php if (!($model->operators && array_intersect_key($operators, $model->operators))): ?>
                  <div class="cb_group" data-l1="<?= substr(md5($country), 0, 10) ?>">
                    <div class="cb_group-name">
                      <div class="checkbox checkbox-inline cb_g">
                        <input type="checkbox" class="styled" id="cb_g_c<?= $countrySelector ?>">
                        <label for="cb_g_c<?= $countrySelector ?>"></label>
                      </div>
                      <span><?= $country ?></span> <i class="icon-down2"></i>
                    </div>
                    <div
                      class="cb_group-list"<?php if ($countrySelector == 1): ?> style="display: block;"<?php endif; ?>
                      data-opened="<?= ($countrySelector == 1) ? 1 : 0 ?>">
                      <div class="form-group">
                        <?php foreach ($operators as $id => $name): ?>
                          <div class="checkbox checkbox-inline cb_g_c<?= $countrySelector ?>">
                            <input type="checkbox" name="FormModel[operators][]" class="styled" id="op<?= $id ?>"
                                   value="<?= $id ?>">
                            <label for="op<?= $id ?>"><?= Html::encode($name) ?></label>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  </div>
                <?php endif ?>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
      <?php $this->endBlockAccessVerifier(); ?>
      <?php $this->beginBlockAccessVerifier('platforms', ['StatisticFilterByPlatforms']); ?>
      <div class="col-xs-20">
        <div class="filter">
          <div class="filter-header">
            <span><?= Yii::_t('main.platforms') ?>
              <i><?= $model->platforms ? '(' . count($model->platforms) . ')' : '' ?></i></i></span>
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
              <?php if (!$filtersDP->getPlatforms()): ?>
                <div class="hidden_text"><?= Yii::_t('statistic.no_platforms_selected') ?></div><?php endif; ?>
              <?php foreach ($filtersDP->getPlatforms() as $id => $name): ?>
                <?php if ($filtersDP->getPlatforms() && in_array($id, $filtersDP->getPlatforms())): ?>
                  <div class="checkbox checkbox-inline">
                    <input type="checkbox" checked class="styled" name="FormModel[platforms][]" id="idpl<?= $id ?>"
                           value="<?= $id ?>">
                    <label for="idpl<?= $id ?>"><?= Html::encode($name) ?></label>
                  </div>
                <?php endif ?>
              <?php endforeach; ?>
            </div>
            <div class="filter-body_deselected">
              <div class="hidden_text"><?= Yii::_t('main.selected_all_options') ?></div>
              <div class="form-group">
                <?php foreach ($filtersDP->getPlatforms() as $id => $name): ?>
                  <?php if (!($filtersDP->getPlatforms() && in_array($id, $filtersDP->getPlatforms()))): ?>
                    <div class="checkbox checkbox-inline">
                      <input type="checkbox" class="styled" name="FormModel[platforms][]" id="idpl<?= $id ?>"
                             value="<?= $id ?>">
                      <label for="idpl<?= $id ?>"><?= Html::encode($name) ?></label>
                    </div>
                  <?php endif ?>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php $this->endBlockAccessVerifier(); ?>
      <?php $this->beginBlockAccessVerifier('landings', ['StatisticFilterByLandings']); ?>
      <div class="col-xs-20">
        <div class="filter">
          <div class="filter-header">
            <span><?= Yii::_t('main.landings') ?>
              <i><?= $model->landings ? '(' . count($model->landings) . ')' : '' ?></i></i></span>
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
            <div class="filter-body_selected"<?= $model->landings ? ' style="display: block;"' : '' ?>>
              <?php $countrySelector = 0;
              foreach ($filtersDP->getLandingsByCountry() as $country => $landings): $countrySelector++; ?>
                <?php if ($model->landings && array_intersect(array_keys($landings), $model->landings)): ?>
                  <div class="cb_group" data-l1="<?= substr(md5($country), 0, 10) ?>">
                    <div class="cb_group-name">
                      <div class="checkbox checkbox-inline cb_g">
                        <input type="checkbox" class="styled" id="cb_g_lc<?= $countrySelector ?>">
                        <label for="cb_g_lc<?= $countrySelector ?>"></label>
                      </div>
                      <span><?= $country ?></span> <i class="icon-down2"></i>
                    </div>
                    <div
                      class="cb_group-list"<?php if ($countrySelector == 1): ?> style="display: block;"<?php endif; ?>
                      data-opened="<?= ($countrySelector == 1) ? 1 : 0 ?>">
                      <div class="form-group">
                        <?php foreach ($landings as $id => $name): ?>
                          <div class="checkbox checkbox-inline cb_g_lc<?= $countrySelector ?>">
                            <input type="checkbox"
                                   name="FormModel[landings][]" <?php if (in_array($id, $model->landings)): ?> checked<?php endif; ?>
                                   class="styled" id="idp<?= $id ?>" value="<?= $id ?>">
                            <label for="idp<?= $id ?>"><?= $id ?>. <?= Html::encode($name) ?></label>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  </div>
                <?php endif ?>
              <?php endforeach; ?>
            </div>
            <div class="filter-body_deselected">
              <div class="hidden_text"></div>
              <?php $countrySelector = 0;
              foreach ($filtersDP->getLandingsByCountry() as $country => $landings): $countrySelector++; ?>
                <?php if (!($model->landings && array_intersect_key($landings, $model->landings))): ?>
                  <div class="cb_group" data-l1="<?= substr(md5($country), 0, 10) ?>">
                    <div class="cb_group-name">
                      <div class="checkbox checkbox-inline cb_g">
                        <input type="checkbox" class="styled" id="cb_g_lc<?= $countrySelector ?>">
                        <label for="cb_g_lc<?= $countrySelector ?>"></label>
                      </div>
                      <span><?= $country ?></span> <i class="icon-down2"></i>
                    </div>
                    <div
                      class="cb_group-list"<?php if ($countrySelector == 1): ?> style="display: block;"<?php endif; ?>
                      data-opened="<?= ($countrySelector == 1) ? 1 : 0 ?>">
                      <div class="form-group">
                        <?php foreach ($landings as $id => $name): ?>
                          <div class="checkbox checkbox-inline cb_g_lc<?= $countrySelector ?>">
                            <input type="checkbox" name="FormModel[landings][]" class="styled" id="idp<?= $id ?>"
                                   value="<?= $id ?>">
                            <label for="idp<?= $id ?>"><?= $id ?>. <?= Html::encode($name) ?></label>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  </div>
                <?php endif ?>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
      <?php $this->endBlockAccessVerifier(); ?>
      <div class="col-xs-20">
        <div class="form-group">
          <?= Html::activeTextInput($model, 'subid1', [
            'placeholder' => Yii::_t('statistic.subid1'),
            'class' => 'form-control',
            'data-width' => '100%',
          ]) ?>
        </div>
      </div>
      <div class="col-xs-20">
        <div class="form-group">
          <?= Html::activeTextInput($model, 'subid2', [
            'placeholder' => Yii::_t('statistic.subid2'),
            'class' => 'form-control',
            'data-width' => '100%',
          ]) ?>
        </div>
      </div>

      <?= StatisticCustomFilterWidget::widget([
        'id' => 'custom_hits',
        'label' => Yii::_t('statistic.statistic_traffic-hits'),
        'from' => 'FormModel[hitsFrom]',
        'to' => 'FormModel[hitsTo]',
      ]); ?>

      <?= StatisticCustomFilterWidget::widget([
        'id' => 'custom_uniques',
        'label' => Yii::_t('statistic.statistic_traffic-uniques'),
        'from' => 'FormModel[uniquesFrom]',
        'to' => 'FormModel[uniquesTo]',
      ]); ?>

      <?= StatisticCustomFilterWidget::widget([
        'id' => 'custom_tb',
        'label' => Yii::_t('statistic.statistic_traffic-tb'),
        'from' => 'FormModel[tbFrom]',
        'to' => 'FormModel[tbTo]',
        'shouldUpperCaseLabel' => true,
      ]); ?>

      <?= StatisticCustomFilterWidget::widget([
        'id' => 'custom_accepted',
        'label' => Yii::_t('statistic.statistic_traffic-accepted_full'),
        'from' => 'FormModel[acceptedFrom]',
        'to' => 'FormModel[acceptedTo]',
      ]); ?>

      <?= StatisticCustomFilterWidget::widget([
        'id' => 'custom_ons',
        'label' => Yii::_t('statistic.statistic_revshare-ons_full'),
        'from' => 'FormModel[onsFrom]',
        'to' => 'FormModel[onsTo]',
      ]); ?>

      <?= StatisticCustomFilterWidget::widget([
        'id' => 'custom_offs',
        'label' => Yii::_t('statistic.statistic_revshare-offs_full'),
        'from' => 'FormModel[offsFrom]',
        'to' => 'FormModel[offsTo]',
      ]); ?>

      <?= StatisticCustomFilterWidget::widget([
        'id' => 'custom_rebills',
        'label' => Yii::_t('statistic.statistic_revshare-rebills_full'),
        'from' => 'FormModel[rebillsFrom]',
        'to' => 'FormModel[rebillsTo]',
      ]); ?>

      <div class="col-xs-20 custom_col">
        <div class="filter filter_add">
          <div class="filter-header">
            <span><?= Yii::_t('statistic.add_filter') ?><i></i></span>
            <div class="caret_wrap">+</div>
          </div>
          <div class="filter-body filter-body_left">
            <ul class="filter-list">

              <?= StatisticCustomFilterSettingsWidget::widget([
                'id' => 'custom_hits',
                'label' => Yii::_t('statistic.statistic_traffic-hits'),
                'from' => 'FormModel[hitsFrom]',
                'to' => 'FormModel[hitsTo]',
              ]); ?>

              <?= StatisticCustomFilterSettingsWidget::widget([
                'id' => 'custom_uniques',
                'label' => Yii::_t('statistic.statistic_traffic-uniques'),
                'from' => 'FormModel[uniquesFrom]',
                'to' => 'FormModel[uniquesTo]',
              ]); ?>

              <?= StatisticCustomFilterSettingsWidget::widget([
                'id' => 'custom_tb',
                'label' => Yii::_t('statistic.statistic_traffic-tb'),
                'from' => 'FormModel[tbFrom]',
                'to' => 'FormModel[tbTo]',
                'shouldUpperCaseLabel' => true,
              ]); ?>

              <?= StatisticCustomFilterSettingsWidget::widget([
                'id' => 'custom_accepted',
                'label' => Yii::_t('statistic.statistic_traffic-accepted_full'),
                'from' => 'FormModel[acceptedFrom]',
                'to' => 'FormModel[acceptedTo]',
              ]); ?>

              <?= StatisticCustomFilterSettingsWidget::widget([
                'id' => 'custom_ons',
                'label' => Yii::_t('statistic.statistic_revshare-ons_full'),
                'from' => 'FormModel[onsFrom]',
                'to' => 'FormModel[onsTo]',
              ]); ?>

              <?= StatisticCustomFilterSettingsWidget::widget([
                'id' => 'custom_offs',
                'label' => Yii::_t('statistic.statistic_revshare-offs_full'),
                'from' => 'FormModel[offsFrom]',
                'to' => 'FormModel[offsTo]',
              ]); ?>

              <?= StatisticCustomFilterSettingsWidget::widget([
                'id' => 'custom_rebills',
                'label' => Yii::_t('statistic.statistic_revshare-rebills_full'),
                'from' => 'FormModel[rebillsFrom]',
                'to' => 'FormModel[rebillsTo]',
              ]); ?>


            </ul>
          </div>
        </div>
      </div>
      <div class="col-xs-20 filter_submit">
        <button class="btn btn-primary refresh_stat"><?= Yii::_t('main.apply') ?>
          <div><i class="icon-clock"></i><span data-count="300">5:00</span></div>
        </button>
      </div>
    </div>
  </div>

<?php ActiveForm::end(); ?>