<?php

use yii\helpers\Url;
use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Html;

$sourceFilterOperators = json_decode($source->filter_operators, true);

?>
<div class="collapse-content">
	<div class="option__list option__list-source">
		<div class="row">
			<div class="col-xs-6">
        <h3><?= Yii::_t('sources.ad_format_settings'); ?></h3>
        <ul class="radio_s">
          <?php foreach($adsTypes as $adsType):?>
            <li class="<?= $source->ads_type == $adsType->id ? 'active' : '' ?>">
              <div class="row">

                <div class="col-xs-3">
                  <div class="radio radio-primary">
                    <?= Html::radio('adstype', $source->ads_type == $adsType->id, [
                      'id' => 'settings-adstype-' . $adsType->id,
                      'data-source' => $source->id,
                      'data-url' => Url::to(['edit']),
                      'value' => $adsType->id,
                    ]); ?>
                    <label for="settings-adstype-<?= $adsType->id ?>"><?= $adsType->name ?></label>
                  </div>
                </div>

                <div class="col-xs-9">
                  <?= $this->render('_adstype', ['model' => $adsType, 'source' => $source]); ?>
                </div>

              </div>
          	</li>
          <?php endforeach;?>
        </ul>
			</div>
			<div class="col-xs-6">
        <h3><?= Yii::_t('sources.monetization_settings'); ?></h3>
				<ul class="row radio_s">
          <?php foreach ($profitTypes as $type => $label): ?>
            <li class="col-xs-6 <?= $source->default_profit_type == $type ? 'active' : '' ?>">
              <div class="row">
                <div class="col-xs-12">
                  <div class="radio radio-primary">
                    <?= Html::radio('default_profit_type', $source->default_profit_type == $type, [
                      'id' => 'settings-default_profit_type-' . $type,
                      'data-source' => $source->id,
                      'data-url' => Url::to(['edit']),
                      'value' => $type,
                    ]); ?>
                    <label for="radio1"><?= $label ?></label>
                  </div>
                </div>
                <div class="col-xs-12">
                  <span class="option__list-description align_box"><?= Yii::_t('sources.profit_type_hint_edit_' . $type) ?></span>
                </div>
              </div>
            </li>
          <?php endforeach; ?>
				</ul>

        <?php if($source->isStatusModeration()): ?>
        <div class="moderation">
          <span>
            <i class="icon-danger"></i>
            <?= Yii::_t('sources.sources_targeting_moderation') ?>
          </span>
        </div>
        <?php else: ?>
        <div class="postback-formate" data-source="<?= $source->id ?>" data-url="<?= Url::to(['edit', 'isOperators' => 1]) ?>">
					<span class="" data-toggle="collapse" href="#table" aria-expanded="true" aria-controls="collapseExample"><span><?= Yii::_t('sources.sources_targeting_settings') ?></span> <i class="caret"></i></span>
					<div id="table" class="postback-formate_hidden collapse" aria-expanded="true">
            <?= Html::dropDownList('country', null, ArrayHelper::map($countries, 'id', 'name'),
            [
              'id' => 'country',
              'class' => 'selectpicker',
              'data-width' => '50%'
            ]) ?>

						<div class="checkbox__list">
							<p><?= Yii::_t('sources.sources_show_ads_to') ?></p>

              <?php foreach ($countries as $country): ?>

                <?php
                $countryOperatorIds = ArrayHelper::getColumn($country->activeOperator, 'id');
                $toAll = (isset($sourceFilterOperators) && count(array_diff($countryOperatorIds, $sourceFilterOperators)) == 0) || is_null($sourceFilterOperators);
                $isNone = isset($sourceFilterOperators) && count(array_intersect($sourceFilterOperators, $countryOperatorIds)) == 0;
                ?>


                <div class="checkbox checkbox-inline checkbox-primary cb_select_all" data-country="<?= $country->id ?>">
                    <?= Html::checkbox('countrybox_' . $country->id . '_all', $toAll, [
                      'id' => 'countrybox_' . $country->id . '_all',
                      'value' => 'all',
                    ]); ?>
                  <label for="countrybox_<?= $country->id ?>_all"><?= Yii::_t('sources.sources_all') ?></label>
                </div>
                <div class="checkbox checkbox-inline checkbox-primary cb_deselect_all" data-country="<?= $country->id ?>">
                    <?= Html::checkbox('countrybox_' . $country->id . '_noone', $isNone, [
                      'id' => 'countrybox_' . $country->id . '_noone',
                      'value' => 'noone',
                    ]); ?>
                  <label for="countrybox_<?= $country->id ?>_noone"><?= Yii::_t('sources.sources_noone') ?></label>
                </div>

                <?php foreach ($country->activeOperator as $operator): ?>

                  <?php
                    $checked = $sourceFilterOperators ? in_array($operator->id, $sourceFilterOperators) : false;
                  ?>

                  <div class="checkbox checkbox-inline checkbox-primary" data-country="<?= $country->id ?>">
                    <?= Html::checkbox('operators', ($toAll ? true : $checked), [
                      'id' => 'countrybox_' . $country->id . '_' . $operator->id,
                      'value' => $operator->id
                    ]); ?>
                    <label for="countrybox_<?= $country->id ?>_<?= $operator->id ?>"><?= $operator->name ?></label>
                  </div>
                <?php endforeach; ?>

              <?php endforeach; ?>
            </div>
					</div>
				</div>
        <?php endif;?>

      </div>
    </div>
  </div>
</div>