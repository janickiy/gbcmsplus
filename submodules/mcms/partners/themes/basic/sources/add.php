<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\Link;
use mcms\partners\assets\PromoSourcesAddAsset;
use mcms\partners\components\api\GetProjectName;
PromoSourcesAddAsset::register($this);


$zipName = (new GetProjectName())->getWcZipFileName();

/* @var mcms\common\web\View $this */
/* @var $sourceForm mcms\partners\models\SourceForm */
?>
<?php $this->beginBlock('viewport'); ?><meta name="viewport" content="width=1250"><?php $this->endBlock() ?>
<div class="container-fluid">
  <div class="row">
    <div class="col-xs-7">
      <div class="bgf">
        <?php
        $form = ActiveForm::begin([
          'id' => 'sourceForm',
          'action' => ['form-handle'],
          'enableAjaxValidation' => true,
          'validateOnChange' => false,
          'validateOnBlur' => false,
          'options' => [
            'data-next' => Yii::_t('main.next'),
            'data-installed' => Yii::_t('sources.code_installed'),
            'data-done' => Yii::_t('main.done'),
          ]
        ]); ?>
        <?= Html::hiddenInput('stepNumber', 1) ?>
        <?= $form->field($sourceForm, 'id', ['options' => ['class' => 'hidden']])->hiddenInput(['id' => 'sourceId'])->label(false) ?>

          <div class="steps_wrap">

            <div class="title">
              <h2><?= Yii::_t('sources.new_source') ?></h2>
              <?= Link::get('index', [], ['class' => 'title__link'], '<i class="icon-double_arrow"></i>'. Yii::_t('sources.to_source_list')) ?>
            </div>
            <div class="row change__step">
              <div data-step="1" class="col-xs-4 steps_progress active travel">
                <span><?= Yii::_t('sources.source_info'); ?></span>
              </div>
              <div data-step="2" class="col-xs-4 steps_progress">
                <span><?= Yii::_t('sources.install_ads_code'); ?></span>
              </div>
              <div data-step="3" class="col-xs-4 steps_progress">
                <span><?= Yii::_t('sources.select_ads_format'); ?></span>
              </div>
            </div>
            <div class="steps content__position">
              <div class="step__1" >
                <?= $form->field($sourceForm, 'domain_id')->hiddenInput()->label(false); ?>

                <?= $form->field($sourceForm, 'url') ?>

                <div class="row">
                  <?= $form->field($sourceForm, 'default_profit_type')->radioList($profitTypes,
                    [
                      'item' => function ($index, $label, $name, $checked, $value) {
                        return
                          '<div class="col-xs-6 option__list"><div class="form-group radio radio-primary">' .
                          Html::radio($name, $checked, ['value' => $value, 'id' => 'source-profit-type-' . $value]) .
                          '<label for="source-profit-type-' . $value . '">' . $label . '</label>
                          <div class="hint-block">' . Yii::_t('sources.profit_type_hint_' . $value) . '</div></div></div>';
                    },
                    ]
                  )->label(false); ?>
                </div>
              </div>
              <div class="step__2" >
                <div class="code__partial">
                  <div class="">
                    <div class="">
                      <div class="code__partial-title">
                        <span><?= Yii::_t('sources.single_javascript') ?></span>
                        <small class="small_text"><i class="icon-like"></i> <?= Yii::_t('sources.recommended') ?></small>
                      </div>
                      <p><?= Yii::_t('sources.single_javascript_text_1') ?></p>

                    </div>
                    <div class="">

                      <code class="selected__text">
                        &lt;script&gt;
                          (function(i, s, o, g, r, a, m) { i[r] = i[r] || function() { (i[r].q = i[r].q || []).push(arguments) };
                            a = s.createElement(o), m = s.getElementsByTagName(o)[0]; a.async = 1; a.src = g; m.parentNode.insertBefore(a, m)
                          })
                          (window, document, 'script', '<?= str_replace(["http://","https://"],"//",$domain) ?>js/embed.js?hash=<span class="hash"><?= isset($hash) ? $hash : '' ?></span>', 'wc');
                          wc('start', '<span class="hash"><?= isset($hash) ? $hash : '' ?></span>', {});
                        &lt;/script&gt;
                      </code>

                    </div>
                    <p class="small_text"><?= Yii::_t('partners.sources.single_javascript_text_2') ?></p>
                  </div>
                  <br>
                  <div class="">
                    <div class="">
                      <div class="code__partial-title">
                        <span><?= Yii::_t('sources.single_php') ?></span>
                      </div>
                      <div class="code__partial-list">
                        <ol>
                          <li><?= Yii::_t('sources.download_our_script') ?>:
                            <div class="code__partial-load">
                              <i class="icon-zipicon2"></i>
                              <a target="_blank" href="<?= Yii::getAlias('@uploadUrl/promo/') . $zipName ?>"><?= Yii::_t('sources.download_wapclick_zip', ['fileName' => $zipName]) ?></a>
                            </div>

                          </li>
                          <li><span><?= Yii::_t('sources.single_php_text_1') ?></span>
                          </li>
                          <li><span><?= Yii::_t('sources.single_php_text_2') ?></span>
                          </li>
                          <li><span><?= Yii::_t('sources.check_script_work') ?> <a class="check" target="_blank" data-check="wc/check.php" href=""></a></span>
                          </li>
                        </ol>
                      </div>
                    </div>
                    <div class="">

                      <code class="selected__text php-script">
                        include 'wc/WC.php';
                        WC::redirect('<?= $domain ?>', '<?= isset($hash) ? $hash : '' ?>');
                      </code>

                    </div>
                  </div>
                </div>
              </div>
              <div class="step__3">
                <div class="code__partial-title">
                  <span><?= Yii::_t('sources.ad_format') ?></span>
                </div>
                <div class="code__partial-text"><?= Yii::_t('sources.ad_format_hint') ?></div>
                <br>

                <div class="option__list">
                  <ul class="radio_s">
                    <?= $form->field($sourceForm, 'ads_type')->radioList(ArrayHelper::map($adsTypes, 'id', 'name'),
                        [
                          'item' => function ($index, $label, $name, $checked, $value) use ($adsTypes){
                            $ads = null;
                            foreach ($adsTypes as $adsType) {
                              if ($adsType->id == $value) {
                                $ads = $adsType;
                                break;
                              }
                            }
                            return
                              '<li class="' . ($checked ? 'active' : '') . '">
                                <div class="row">
                                  <div class="col-xs-3">
                                    <div class="radio radio-primary">' .
                                      Html::radio($name, $checked, [
                                        'id' => 'settings-adstype-' . $value,
                                        'value' => $value,
                                      ]) .
                                      '<label for="settings-adstype-' . $value . '">' . $label . '</label>
                                    </div>
                                  </div>
                                  <div class="col-xs-9">' . $this->render('_adstype', ['model' => $ads]) . '</div>
                                </div>
                              </li>';
                          },
                        ]
                      )->label(false); ?>
                  </ul>
                </div>

                <div class="moderation">
                  <span>
                    <i class="icon-danger"></i>
                    <?= Yii::_t('sources.sources_targeting_moderation') ?>
                  </span>
                </div>

              </div>
            </div>
            <div class="steps__buttons">
              <span class="btn btn-default pull-left hidden" id="prev__step"><?= Yii::_t('main.prev') ?></span>
              <span class="btn btn-default" id="next__step"><?= Yii::_t('main.next') ?></span>
            </div>
          </div>
        <?php ActiveForm::end(); ?>
      </div>
    </div>
    <div class="col-xs-5">
      <div class="bgf">
        <div class="change__stepData">
          <div data-step="1" class="title active">
            <h2><?= Yii::_t('sources.source_add_legend_title_1') ?></h2>
          </div>
          <div data-step="2" class="title">
            <h2><?= Yii::_t('sources.source_add_legend_title_2') ?></h2>
          </div>
          <div data-step="3" class="title">
            <h2><?= Yii::_t('sources.source_add_legend_title_2') ?></h2>
          </div>
        </div>

        <div class="step__comment content__position change__stepData">
          <div data-step="1" class="active">
            <div class="gray"><?= Yii::_t('sources.source_add_text_1_line_1') ?></div>
            <ul class="decor gray">
              <li><?= Yii::_t('sources.source_add_text_1_line_2') ?></li>
              <li><?= Yii::_t('sources.source_add_text_1_line_3') ?></li>
              <li><?= Yii::_t('sources.source_add_text_1_line_5') ?></li>
            </ul>
            <blockquote class="gray"><?= Yii::_t('sources.source_add_text_1_line_6') ?></blockquote>
          </div>
          <div data-step="2">
            <p class="gray"><?= Yii::_t('sources.source_add_text_2_line_1') ?></p>
            <p class="gray"><?= Yii::_t('sources.source_add_text_2_line_2') ?></p>
          </div>
          <div data-step="3">
            <p class="gray"><?= Yii::_t('sources.source_add_text_2_line_1') ?></p>
            <p class="gray"><?= Yii::_t('sources.source_add_text_2_line_2') ?></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>