<?php
use mcms\common\helpers\ArrayHelper;
use mcms\common\widget\Editable;
use mcms\promo\assets\LandingsViewAsset;
use yii\bootstrap\Html;
use yii\widgets\DetailView;

/**
 * @var mcms\promo\models\Landing $model
 * @var mcms\payments\components\exchanger\CurrencyCourses $currencies
 */
LandingsViewAsset::register($this);
$formatter = Yii::$app->formatter;
?>
  <div class="row" id="landing_image_description_wrap">
    <div class="col-lg-8 col-md-8 col-sm-8">
      <?= Html::a(Html::img($model->image_src, ['id' => 'landing_image', 'class' => 'file-preview-image']), $model->image_src, ['target' => '_blank']); ?>
      <div id="landing_description_label">
        <?= $model->getAttributeLabel('description'); ?>
      </div>
      <div id="landing_description">
        <?= nl2br($model->description); ?>
      </div>
      <?php if ($model->provider->is_rgk): ?>
      <div id="landing_description_label">
        <?= $model->getAttributeLabel('operators_text'); ?>
      </div>
      <?php endif;?>
      <div id="landing_description">
        <?= $model->operators_text ?>
      </div>
    </div>
    <div class="col-lg-4 col-md-4 col-sm-4">
      <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
          'id',
          'code',
          [
            'attribute' => 'offer_category_id',
            'format' => 'raw',
            'value' => $model->offerCategoryLink,
          ],
          [
            'attribute' => 'category_id',
            'format' => 'raw',
            'value' => $model->categoryLink,
          ],
          'name',
          [
            'attribute' => 'provider_id',
            'format' => 'raw',
            'value' => $model->providerLink,
          ],
          [
            'attribute' => 'status',
            'format' => 'raw',
            'value' =>
              ($model->canChangeStatus() && Yii::$app->user->can('PromoLandingsUpdateEditable')
                ? Editable::getWidget([
                  'name' => 'status',
                  'inputType' => Editable::INPUT_DROPDOWN_LIST,
                  'value' => $model->status,
                  'data' => $model->getStatuses(),
                  'displayValueConfig' => $model->getStatuses(),
                ], [
                  'update-editable',
                  'landingId' => $model->id,
                  'attribute' => 'status',
                ])
                : $model->getCurrentStatusName()
              )
              .
              ($model->allow_sync_status
                ? ''
                : Html::icon('pushpin', ['title' => Yii::_t('promo.landings.sync-disallow')])
              ),
          ],
          [
            'attribute' => 'access_type',
            'format' => 'raw',
            'value' => (Yii::$app->user->can('PromoLandingsUpdateEditable')
                ? Editable::getWidget([
                  'name' => 'access_type',
                  'inputType' => Editable::INPUT_DROPDOWN_LIST,
                  'value' => $model->access_type,
                  'data' => $model->getAccessTypes(),
                  'displayValueConfig' => $model->getAccessTypes(),
                ], [
                  'update-editable',
                  'landingId' => $model->id,
                  'attribute' => 'access_type',
                ])
                : $model->getCurrentAccessTypeName()
              )
              .
              ($model->allow_sync_access_type
                ? ''
                : Html::icon('pushpin', ['title' => Yii::_t('promo.landings.sync-disallow')])
              ),
          ],
          [
            'attribute' => 'auto_rating',
            'value' => $model->currentAutoRatingTypeName,
            'visible' => $model->provider->is_rgk,
          ],
          'rebill_period',
          [
            'attribute' => 'rating',
            'visible' => $model->provider->is_rgk,
          ],
          'send_id',
          'created_at:datetime',
          'updated_at:datetime',
          [
            'attribute' => 'created_by',
            'value' => $model->createdBy->username,
          ],
        ],
        'options' => ['id' => 'landing_detail_view', 'class' => 'table table-striped table-bordered detail-view'],
      ]); ?>
    </div>
  </div>

  <?php if ($model->comment): ?>
    <div id="forbiddenTrafficTypes_wrap">
      <span class="bold_label"><?= $model->getAttributeLabel('comment') . ': '; ?></span>
      <?= $model->comment ?>
    </div>
  <?php endif ?>
  <div id="forbiddenTrafficTypes_wrap">
    <span class="bold_label"><?= Yii::_t('promo.landings.attribute-forbiddenTrafficTypeIds') . ': '; ?></span>
    <?= $model->getForbiddenTrafficTypesNames() ?: Yii::_t('promo.landings.not_exists'); ?>
  </div>
  <div id="forbiddenTrafficTypes_wrap">
    <span class="bold_label"><?= Yii::_t('promo.landings.attribute-platformIds') . ': '; ?></span>
    <?= $model->getPlatformsNameText() ?: Yii::_t('promo.landings.not_exists'); ?>
  </div>

  <div id="landing_operators_table_wrap">
    <table id="landing_operators_table" class="table table-bordered table-striped">
      <thead>
      <th id="operator_th"><?= Yii::_t('promo.landings.operator-attribute-operator_id'); ?></th>
      <?php if ($model->provider->is_rgk): ?>
        <th><?= Yii::_t('promo.landings.operator-attribute-days_hold'); ?></th>
      <?php endif;?>
      <th><?= Yii::_t('promo.landings.operator-attribute-local_currency_id'); ?></th>
      <th><?= Yii::_t('promo.landings.operator-attribute-local_currency_rebill_price'); ?></th>
      <th><?= Yii::_t('promo.landings.buyout_price_usd'); ?></th>
      <th><?= Yii::_t('promo.landings.buyout_price_eur'); ?></th>
      <th><?= Yii::_t('promo.landings.buyout_price_rub'); ?></th>
      <th><?= Yii::_t('promo.landings.rebill_price_usd'); ?></th>
      <th><?= Yii::_t('promo.landings.rebill_price_eur'); ?></th>
      <th><?= Yii::_t('promo.landings.rebill_price_rub'); ?></th>
      <th><?= Yii::_t('promo.landings.operator-attribute-cost_price'); ?></th>
      <th><?= Yii::_t('promo.landings.operator-attribute-subscription_type_id'); ?></th>
      <th><?= Yii::_t('promo.landings.operator-attribute-payTypeIds'); ?></th>
      </thead>
      <tbody>
      <?php foreach ($model->landingOperator as $operator): ?>
        <?php $prices = $operator->getCompletePrices(); ?>
        <?php $isLocalMainCurrency = in_array($operator->localCurrency->code, ['rub', 'eur', 'usd'], true); ?>
        <tr<?php if ($operator->is_deleted):?> class="danger"<?php endif;?>>
          <td><?= $operator->operatorLink; ?></td>
          <?php if ($model->provider->is_rgk): ?>
            <td><?= $operator->days_hold; ?></td>
          <?php endif;?>
          <td><?= $operator->localCurrency->name; ?></td>
          <td><?= $formatter->asDecimal($operator->local_currency_rebill_price); ?></td>
          <td>
            <?php if ($operator->buyout_price_usd == 0 && (($isLocalMainCurrency && $operator->localCurrency->code != 'usd') || (!$isLocalMainCurrency))): ?>
            <?php // Запретить редактировать цену за выкуп в rub, usd если локальная валюта не является одной из основных ?>
              <?= Html::tag('span', $formatter->asDecimal($prices->getBuyoutPrice('usd')), ['class' => 'converted_price']); ?>
            <?php else: ?>
              <?php if ($operator->canUpdateBuyoutProfit()): ?>
                <?= Editable::getWidget([
                  'name' => 'buyout_price_usd',
                  'value' => $operator->buyout_price_usd,
                  'header' => Yii::_t('promo.landings.buyout_price_usd'),
                  'options' => [
                    'class' => 'form-control',
                  ],
                ], [
                  'update-buyout-profit',
                  'landingId' => $model->id,
                  'operatorId' => $operator->operator_id,
                  'attribute' => 'buyout_price_usd',
                ], false, true) ?>
              <?php else: ?>
                <?= $formatter->asDecimal($prices->getBuyoutPrice('usd')); ?>
              <?php endif; ?>

              <?php if (!$model->allow_sync_buyout_prices): ?>
                <?= Html::icon('pushpin', ['title' => Yii::_t('promo.landings.sync-disallow')]) ?>
              <?php endif ?>

            <?php endif; ?>
          </td>
          <td>
            <?php if ($operator->buyout_price_eur == 0 && $isLocalMainCurrency && $operator->localCurrency->code != 'eur'): ?>
              <?= Html::tag('span', $formatter->asDecimal($prices->getBuyoutPrice('eur')), ['class' => 'converted_price']); ?>
            <?php else: ?>
              <?php if ($operator->canUpdateBuyoutProfit()): ?>
                <?= Editable::getWidget([
                  'name' => 'buyout_price_eur',
                  'value' => $operator->buyout_price_eur,
                  'header' => Yii::_t('promo.landings.buyout_price_eur'),
                  'options' => [
                    'class' => 'form-control',
                  ],
                ], [
                  'update-buyout-profit',
                  'landingId' => $model->id,
                  'operatorId' => $operator->operator_id,
                  'attribute' => 'buyout_price_eur',
                ], false, true); ?>
              <?php else: ?>
                <?= $formatter->asDecimal($prices->getBuyoutPrice('eur')); ?>
              <?php endif; ?>

              <?php if (!$model->allow_sync_buyout_prices): ?>
                <?= Html::icon('pushpin', ['title' => Yii::_t('promo.landings.sync-disallow')]) ?>
              <?php endif ?>

            <?php endif; ?>
          <td>
            <?php if ($operator->buyout_price_rub == 0 && (($isLocalMainCurrency  && $operator->localCurrency->code != 'rub') || (!$isLocalMainCurrency))): ?>
              <?php // Запретить редактировать цену за выкуп в rub, usd если локальная валюта не является одной из основных ?>
              <?= Html::tag('span', $formatter->asDecimal($prices->getBuyoutPrice('rub')), ['class' => 'converted_price']); ?>
            <?php else: ?>
              <?php if ($operator->canUpdateBuyoutProfit()): ?>
                <?= Editable::getWidget([
                  'name' => 'buyout_price_rub',
                  'value' => $operator->buyout_price_rub,
                  'header' => Yii::_t('promo.landings.buyout_price_rub'),
                  'options' => [
                    'class' => 'form-control',
                  ],
                ], [
                  'update-buyout-profit',
                  'landingId' => $model->id,
                  'operatorId' => $operator->operator_id,
                  'attribute' => 'buyout_price_rub',
                ], false, true); ?>
              <?php else: ?>
                <?= $formatter->asDecimal($prices->getBuyoutPrice('rub')) ?>
              <?php endif; ?>

              <?php if (!$model->allow_sync_buyout_prices): ?>
                <?= Html::icon('pushpin', ['title' => Yii::_t('promo.landings.sync-disallow')]) ?>
              <?php endif ?>

            <?php endif; ?>
          </td>
          <td>
            <?php if ($operator->rebill_price_usd == 0): ?>
              <?= Html::tag('span', $formatter->asDecimal($prices->getRebillPrice('usd')), ['class' => 'converted_price']); ?>
            <?php else: ?>
              <?= $formatter->asDecimal($prices->getRebillPrice('usd')); ?>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($operator->rebill_price_eur == 0): ?>
              <?= Html::tag('span', $formatter->asDecimal($prices->getRebillPrice('eur')), ['class' => 'converted_price']); ?>
            <?php else: ?>
              <?= $formatter->asDecimal($prices->getRebillPrice('eur')); ?>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($operator->rebill_price_rub == 0): ?>
              <?= Html::tag('span', $formatter->asDecimal($prices->getRebillPrice('rub')), ['class' => 'converted_price']); ?>
            <?php else: ?>
              <?= $formatter->asDecimal($prices->getRebillPrice('rub')); ?>
            <?php endif; ?>
          </td>
          <td><?= $operator->cost_price; ?></td>
          <td><?= $operator->subscriptionType ? $operator->subscriptionType->name : null; ?></td>
          <td><?= implode(', ', ArrayHelper::getColumn($operator->payTypes, 'name')); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<?php if ($mainCurrencies): ?>
  <div id="currencies_exchange" class="row">
    <div class="col-lg-2 col-md-2 col-sm-2">
      <?= Yii::_t('promo.landings.usd_rur') . ' - ' . round(ArrayHelper::getValue($mainCurrencies, [\mcms\promo\components\api\MainCurrencies::USD,'to_rub']), 3); ?>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-2">
      <?= Yii::_t('promo.landings.rur_usd') . ' - ' . round(ArrayHelper::getValue($mainCurrencies, [\mcms\promo\components\api\MainCurrencies::RUB,'to_usd']), 3); ?>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-2">
      <?= Yii::_t('promo.landings.usd_eur') . ' - ' . round(ArrayHelper::getValue($mainCurrencies, [\mcms\promo\components\api\MainCurrencies::USD,'to_eur']), 3); ?>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-2">
      <?= Yii::_t('promo.landings.eur_usd') . ' - ' . round(ArrayHelper::getValue($mainCurrencies, [\mcms\promo\components\api\MainCurrencies::EUR,'to_usd']), 3); ?>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-2">
      <?= Yii::_t('promo.landings.eur_rur') . ' - ' . round(ArrayHelper::getValue($mainCurrencies, [\mcms\promo\components\api\MainCurrencies::EUR,'to_usd']), 3); ?>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-2">
      <?= Yii::_t('promo.landings.rur_eur') . ' - ' . round(ArrayHelper::getValue($mainCurrencies, [\mcms\promo\components\api\MainCurrencies::RUB,'to_eur']), 3); ?>
    </div>
  </div>
<?php endif; ?>