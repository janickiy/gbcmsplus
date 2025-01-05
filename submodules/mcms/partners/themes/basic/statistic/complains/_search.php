<?php

use yii\bootstrap\ActiveForm;
use kartik\date\DatePicker;
use mcms\common\helpers\ArrayHelper;
use yii\helpers\Html;

/** @var \mcms\statistic\models\mysql\Statistic $model */
/** @var mcms\common\web\View $this */
/** @var array $filterDatePeriods */

// Отмечаем выбранный диапазон в фильтрах, при загрузке страницы
$this->registerJs(<<<JS
$('.change_date-period > label').removeClass('active').find('input').prop('checked', false);
    if ($("#statistic-start_date").val() != undefined) {
      $('[data-from="' + $("#statistic-start_date").val() + '"][data-to="' + $("#statistic-end_date").val() + '"]').parent().addClass('active');
    } else {
      $('[data-to="' + $("#statistic-end_date").val() + '"]').parent().addClass('active');
    }
JS
);
?>

<?php $form = ActiveForm::begin([
  'id' => 'statistic-filter-form',
  'options' => [
    'data-pjax' => true,
  ],
  'fieldConfig' => [
    'template' => "{input}",
    'options' => [
      'tag'=>'span'
    ]
  ]
]); ?>

<div class="statistics">
  <div class="row no_m row-filter">
    <div class="col-xs-4 text-center no_p w100-xs">
      <div class="btn-group btn-group_custom change_date-period" data-toggle="buttons">
        <?php if (ArrayHelper::getValue($filterDatePeriods, 'today')):  ?>
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
        <?php if (ArrayHelper::getValue($filterDatePeriods, 'yesterday')):  ?>
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
        <?php if (ArrayHelper::getValue($filterDatePeriods, 'week')):  ?>
          <label class="btn btn-sm">
            <input type="radio" name="options"
                   data-start="<?= ArrayHelper::getValue($filterDatePeriods, 'week.from') ?>"
                   data-end="<?= ArrayHelper::getValue($filterDatePeriods, 'week.to') ?>"
                   data-from="<?= ArrayHelper::getValue($filterDatePeriods, 'week.from') ?>"
                   data-to="<?= ArrayHelper::getValue($filterDatePeriods, 'week.to') ?>"
                   autocomplete="off"><?= Yii::_t('statistic.statistic.filter_date_week') ?>
          </label>
        <?php endif ?>
        <?php if (ArrayHelper::getValue($filterDatePeriods, 'month')):  ?>
          <label class="btn btn-sm">
            <input type="radio" name="options"
                   data-start="<?= ArrayHelper::getValue($filterDatePeriods, 'month.from') ?>"
                   data-end="<?= ArrayHelper::getValue($filterDatePeriods, 'month.to') ?>"
                   data-from="<?= ArrayHelper::getValue($filterDatePeriods, 'month.from') ?>"
                   data-to="<?= ArrayHelper::getValue($filterDatePeriods, 'month.to') ?>"
                   autocomplete="off"><?= Yii::_t('statistic.statistic.filter_date_month') ?>
          </label>
        <?php endif ?>
      </div>
    </div>
    <div class="col-xs-8 col-filters-xs no_w no_p">
      <div class="row no_m">
        <div class="col-xs-8 text-center border-r no_p dp_container show">
          <?php
          $model->start_date = date('d.m.Y', strtotime($model->start_date));
          $model->end_date = date('d.m.Y', strtotime($model->end_date));
          echo DatePicker::widget([
            'model' => $model,
            'attribute' => 'start_date',
            'attribute2' => 'end_date',
            'type' => DatePicker::TYPE_RANGE,
            'layout' => '<span class="input-group-addon">' . $model->getAttributeLabel('start_date') . '</span>' .
              '{input1}{separator}{input2}' .
              '<span class="input-group-addon kv-date-remove"><i class="glyphicon glyphicon-remove"></i></span>',
            'separator' => $model->getAttributeLabel('end_date'),
            'pluginOptions' => [
              'format' => 'dd.mm.yyyy',
              'autoclose' => true,
              'startDate' => '-3m',
              'endDate' => 'today',
              'todayHighlight' => true,
            ],
            'options' => ['readonly' => true]
          ]); ?>
          <div id="dp_mobile" class="input-group input-daterange date_filter">
            <input id="m_statistic-start_date" type="date" class="form-control" value="">
            <input id="m_statistic-end_date" type="date" class="form-control" value="">
          </div>
        </div>
        <div class="col-xs-4 w100-xs">
          <div class="row">
            <div class="no_p border-r">
              <div class="collapse_filters" data-target="#settings"><i class="icon-filter"></i><span><?= Yii::_t('main.filters') ?></span></div>
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
            <span><?= Yii::_t('statistic.source') ?><i><?= $model->webmasterSources ? '(' . count($model->webmasterSources) . ')' : '' ?></i></i></span>
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
              <?php if(!$model->webmasterSources): ?><div class="hidden_text"><?= Yii::_t('statistic.no_source_selected') ?></div><?php endif;?>
              <?php foreach($model->getWebmasterSources() as $id => $name):?>
                <?php if($model->webmasterSources && in_array($id, $model->webmasterSources)):?>
                  <div class="checkbox checkbox-inline">
                    <input type="checkbox" checked class="styled" name="statistic[webmasterSources][]" id="ids<?=$id?>" value="<?=$id?>">
                    <label for="ids<?=$id?>"><?=$id?>. <?=$name?></label>
                  </div>
                <?php endif?>
              <?php endforeach;?>
            </div>
            <div class="filter-body_deselected">
              <div class="hidden_text"><?= Yii::_t('main.selected_all_options') ?></div>
              <div class="form-group">
                <?php foreach($model->getWebmasterSources() as $id => $name):?>
                  <?php if(!($model->webmasterSources && in_array($id, $model->webmasterSources))):?>
                    <div class="checkbox checkbox-inline">
                      <input type="checkbox" class="styled" name="statistic[webmasterSources][]" id="ids<?=$id?>" value="<?=$id?>">
                      <label for="ids<?=$id?>"><?=$id?>. <?=$name?></label>
                    </div>
                  <?php endif?>
                <?php endforeach;?>
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
            <span><?= Yii::_t('main.streams') ?><i><?= $model->streams ? '(' . count($model->streams) . ')' : '' ?></i></i></span>
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
              <?php if(!$model->streams): ?><div class="hidden_text"><?= Yii::_t('main.no_stream_selected') ?></div><?php endif;?>
              <?php foreach($model->getStreams() as $id => $name):?>
                <?php if($model->streams && in_array($id, $model->streams)):?>
                  <div class="checkbox checkbox-inline">
                    <input type="checkbox" checked class="styled" name="statistic[streams][]" id="idst<?=$id?>" value="<?=$id?>">
                    <label for="idst<?=$id?>"><?=$id?>. <?= Html::encode($name) ?></label>
                  </div>
                <?php endif?>
              <?php endforeach;?>
            </div>
            <div class="filter-body_deselected">
              <div class="hidden_text"><?= Yii::_t('main.selected_all_options') ?></div>
              <div class="form-group">
                <?php foreach($model->getStreams() as $id => $name):?>
                  <?php if(!($model->streams && in_array($id, $model->streams))):?>
                    <div class="checkbox checkbox-inline">
                      <input type="checkbox" class="styled" name="statistic[streams][]" id="idst<?=$id?>" value="<?=$id?>">
                      <label for="idst<?=$id?>"><?=$id?>. <?= Html::encode($name) ?></label>
                    </div>
                  <?php endif?>
                <?php endforeach;?>
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
            <span><?= Yii::_t('main.links') ?><i><?= $model->arbitraryLinks ? '(' . count($model->arbitraryLinks) . ')' : '' ?></i></i></span>
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
              <?php $streamSelector = 0; foreach($model->getArbitraryLinksByStreams() as $stream => $links): $streamSelector++; ?>
                <?php if($model->arbitraryLinks && array_intersect(array_keys($links), $model->arbitraryLinks) ):?>
                  <div class="cb_group">
                    <div class="cb_group-name">
                      <div class="checkbox checkbox-inline cb_g">
                        <input type="checkbox" class="styled" id="cb_g_s<?= $streamSelector ?>">
                        <label for="cb_g_s<?= $streamSelector ?>"></label>
                      </div>
                      <span><?= $stream ?></span> <i class="icon-down2"></i>
                    </div>
                    <div class="cb_group-list"<?php if($streamSelector == 1): ?> style="display: block;"<?php endif;?>>
                      <div class="form-group">
                        <?php foreach($links as $id => $name): ?>
                          <div class="checkbox checkbox-inline cb_g_s<?= $streamSelector ?>">
                            <input type="checkbox" name="statistic[arbitraryLinks][]" <?php if(in_array($id, $model->arbitraryLinks)):?> checked<?php endif;?> class="styled" id="idl<?= $id ?>" value="<?= $id ?>">
                            <label for="idl<?= $id ?>"><?= Html::encode($name) ?></label>
                          </div>
                        <?php endforeach;?>
                      </div>
                    </div>
                  </div>
                <?php endif?>
              <?php endforeach;?>
            </div>
            <div class="filter-body_deselected">
              <div class="hidden_text"></div>
              <?php $streamSelector = 0; foreach($model->getArbitraryLinksByStreams() as $stream => $links): $streamSelector++; ?>
                <?php if(!($model->arbitraryLinks && array_intersect(array_keys($links), $model->arbitraryLinks))):?>
                  <div class="cb_group">
                    <div class="cb_group-name">
                      <div class="checkbox checkbox-inline cb_g">
                        <input type="checkbox" class="styled" id="cb_g_s<?= $streamSelector ?>">
                        <label for="cb_g_s<?= $streamSelector ?>"></label>
                      </div>
                      <span><?= $stream ?></span> <i class="icon-down2"></i>
                    </div>
                    <div class="cb_group-list"<?php if($streamSelector == 1): ?> style="display: block;"<?php endif;?>>
                      <div class="form-group">
                        <?php foreach($links as $id => $name): ?>
                          <div class="checkbox checkbox-inline cb_g_s<?= $streamSelector ?>">
                            <input type="checkbox" name="statistic[arbitraryLinks][]" class="styled" id="idl<?= $id ?>" value="<?= $id ?>">
                            <label for="idl<?= $id ?>"><?= Html::encode($name) ?></label>
                          </div>
                        <?php endforeach;?>
                      </div>
                    </div>
                  </div>
                <?php endif?>
              <?php endforeach;?>
            </div>
          </div>
        </div>
      </div>
      <?php $this->endBlockAccessVerifier(); ?>
      <?php $this->beginBlockAccessVerifier('countries', ['StatisticFilterByCountries']); ?>
      <div class="col-xs-20">
        <div class="filter">
          <div class="filter-header">
            <span><?= Yii::_t('main.countries') ?><i><?= $model->countries ? '(' . count($model->countries) . ')' : '' ?></i></i></span>
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
              <?php if(!$model->countries): ?><div class="hidden_text"><?= Yii::_t('statistic.no_countries_selected') ?></div><?php endif;?>
              <?php foreach($model->getCountries() as $id => $name):?>
                <?php if($model->countries && in_array($id, $model->countries)):?>
                  <div class="checkbox checkbox-inline">
                    <input type="checkbox" checked class="styled" name="statistic[countries][]" id="idc<?=$id?>" value="<?=$id?>">
                    <label for="idc<?=$id?>"><?=$name?></label>
                  </div>
                <?php endif?>
              <?php endforeach;?>
            </div>
            <div class="filter-body_deselected">
              <div class="hidden_text"><?= Yii::_t('main.selected_all_options') ?></div>
              <div class="form-group">
                <?php foreach($model->getCountries() as $id => $name):?>
                  <?php if(!($model->countries && in_array($id, $model->countries))):?>
                    <div class="checkbox checkbox-inline">
                      <input type="checkbox" class="styled" name="statistic[countries][]" id="idc<?=$id?>" value="<?=$id?>">
                      <label for="idc<?=$id?>"><?=$name?></label>
                    </div>
                  <?php endif?>
                <?php endforeach;?>
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
            <span><?= Yii::_t('main.operators') ?><i><?= $model->operators ? '(' . count($model->operators) . ')' : '' ?></i></i></span>
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
              <?php $countrySelector = 0; foreach($model->getOperatorsByCountry() as $country => $operators): $countrySelector++; ?>
                <?php if($model->operators && array_intersect(array_keys($operators), $model->operators) ):?>
                  <div class="cb_group">
                    <div class="cb_group-name">
                      <div class="checkbox checkbox-inline cb_g">
                        <input type="checkbox" class="styled" id="cb_g_c<?= $countrySelector ?>">
                        <label for="cb_g_c<?= $countrySelector ?>"></label>
                      </div>
                      <span><?= $country ?></span> <i class="icon-down2"></i>
                    </div>
                    <div class="cb_group-list"<?php if($countrySelector == 1): ?> style="display: block;"<?php endif;?>>
                      <div class="form-group">
                        <?php foreach($operators as $id => $name): ?>
                          <div class="checkbox checkbox-inline cb_g_c<?= $countrySelector ?>">
                            <input type="checkbox" name="statistic[operators][]" <?php if(in_array($id, $model->operators)):?> checked<?php endif;?> class="styled" id="op<?= $id ?>" value="<?= $id ?>">
                            <label for="op<?= $id ?>"><?= $name ?></label>
                          </div>
                        <?php endforeach;?>
                      </div>
                    </div>
                  </div>
                <?php endif?>
              <?php endforeach;?>
            </div>
            <div class="filter-body_deselected">
              <div class="hidden_text"></div>
              <?php $countrySelector = 0; foreach($model->getOperatorsByCountry() as $country => $operators):  $countrySelector++; ?>
                <?php if(!($model->operators && array_intersect_key($operators, $model->operators))):?>
                  <div class="cb_group">
                    <div class="cb_group-name">
                      <div class="checkbox checkbox-inline cb_g">
                        <input type="checkbox" class="styled" id="cb_g_c<?= $countrySelector ?>">
                        <label for="cb_g_c<?= $countrySelector ?>"></label>
                      </div>
                      <span><?= $country ?></span> <i class="icon-down2"></i>
                    </div>
                    <div class="cb_group-list"<?php if($countrySelector == 1): ?> style="display: block;"<?php endif;?>>
                      <div class="form-group">
                        <?php foreach($operators as $id => $name): ?>
                          <div class="checkbox checkbox-inline cb_g_c<?= $countrySelector ?>">
                            <input type="checkbox" name="statistic[operators][]" class="styled" id="op<?= $id ?>" value="<?= $id ?>">
                            <label for="op<?= $id ?>"><?= $name ?></label>
                          </div>
                        <?php endforeach;?>
                      </div>
                    </div>
                  </div>
                <?php endif?>
              <?php endforeach;?>
            </div>
          </div>
        </div>
      </div>
      <?php $this->endBlockAccessVerifier(); ?>
      <?php $this->beginBlockAccessVerifier('platforms', ['StatisticFilterByPlatforms']); ?>
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
              <?php if(!$model->platforms): ?><div class="hidden_text"><?= Yii::_t('statistic.no_platforms_selected') ?></div><?php endif;?>
              <?php foreach($model->getPlatforms() as $id => $name):?>
                <?php if($model->platforms && in_array($id, $model->platforms)):?>
                  <div class="checkbox checkbox-inline">
                    <input type="checkbox" checked class="styled" name="statistic[platforms][]" id="idpl<?=$id?>" value="<?=$id?>">
                    <label for="idpl<?=$id?>"><?=$name?></label>
                  </div>
                <?php endif?>
              <?php endforeach;?>
            </div>
            <div class="filter-body_deselected">
              <div class="hidden_text"><?= Yii::_t('main.selected_all_options') ?></div>
              <div class="form-group">
                <?php foreach($model->getPlatforms() as $id => $name):?>
                  <?php if(!($model->platforms && in_array($id, $model->platforms))):?>
                    <div class="checkbox checkbox-inline">
                      <input type="checkbox" class="styled" name="statistic[platforms][]" id="idpl<?=$id?>" value="<?=$id?>">
                      <label for="idpl<?=$id?>"><?=$name?></label>
                    </div>
                  <?php endif?>
                <?php endforeach;?>
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
            <span><?= Yii::_t('main.landings') ?><i><?= $model->landings ? '(' . count($model->landings) . ')' : '' ?></i></i></span>
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
              <?php $countrySelector = 0; foreach($model->getLandingsByCountry() as $country => $landings): $countrySelector++; ?>
                <?php if($model->landings && array_intersect(array_keys($landings), $model->landings) ):?>
                  <div class="cb_group">
                    <div class="cb_group-name">
                      <div class="checkbox checkbox-inline cb_g">
                        <input type="checkbox" class="styled" id="cb_g_lc<?= $countrySelector ?>">
                        <label for="cb_g_lc<?= $countrySelector ?>"></label>
                      </div>
                      <span><?= $country ?></span> <i class="icon-down2"></i>
                    </div>
                    <div class="cb_group-list"<?php if($countrySelector == 1): ?> style="display: block;"<?php endif;?>>
                      <div class="form-group">
                        <?php foreach($landings as $id => $name): ?>
                          <div class="checkbox checkbox-inline cb_g_lc<?= $countrySelector ?>">
                            <input type="checkbox" name="statistic[landings][]" <?php if(in_array($id, $model->landings)):?> checked<?php endif;?> class="styled" id="idp<?= $id ?>" value="<?= $id ?>">
                            <label for="idp<?= $id ?>"><?= $id ?>. <?= $name ?></label>
                          </div>
                        <?php endforeach;?>
                      </div>
                    </div>
                  </div>
                <?php endif?>
              <?php endforeach;?>
            </div>
            <div class="filter-body_deselected">
              <div class="hidden_text"></div>
              <?php $countrySelector = 0; foreach($model->getLandingsByCountry() as $country => $landings):  $countrySelector++; ?>
                <?php if(!($model->landings && array_intersect_key($landings, $model->landings))):?>
                  <div class="cb_group">
                    <div class="cb_group-name">
                      <div class="checkbox checkbox-inline cb_g">
                        <input type="checkbox" class="styled" id="cb_g_lc<?= $countrySelector ?>">
                        <label for="cb_g_lc<?= $countrySelector ?>"></label>
                      </div>
                      <span><?= $country ?></span> <i class="icon-down2"></i>
                    </div>
                    <div class="cb_group-list"<?php if($countrySelector == 1): ?> style="display: block;"<?php endif;?>>
                      <div class="form-group">
                        <?php foreach($landings as $id => $name): ?>
                          <div class="checkbox checkbox-inline cb_g_lc<?= $countrySelector ?>">
                            <input type="checkbox" name="statistic[landings][]" class="styled" id="idp<?= $id ?>" value="<?= $id ?>">
                            <label for="idp<?= $id ?>"><?= $id ?>. <?= $name ?></label>
                          </div>
                        <?php endforeach;?>
                      </div>
                    </div>
                  </div>
                <?php endif?>
              <?php endforeach;?>
            </div>
          </div>
        </div>
      </div>
      <?php $this->endBlockAccessVerifier(); ?>
      <div class="col-xs-20 col-xs-offset-40">
        <button class="btn btn-primary refresh_stat"><?= Yii::_t('main.apply') ?>
          <div><i class="icon-clock"></i><span data-count="300">5:00</span></div>
        </button>
      </div>
    </div>
  </div>
</div>

<?php ActiveForm::end(); ?>
