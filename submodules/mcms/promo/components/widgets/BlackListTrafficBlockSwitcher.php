<?php

namespace mcms\promo\components\widgets;

use mcms\common\widget\alert\Alert;
use mcms\promo\components\api\UserPromoSettings;
use mcms\promo\models\TrafficBlock;
use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Виджет аякс-переключателя режима блокировки операторов для партнера
 * Т.е. выбираем использовать блэклист или вайтлист для партнера
 */
class BlackListTrafficBlockSwitcher extends Widget
{

  public $userId;

  /**
   * @return string
   */
  public function run()
  {
    if (!Yii::$app->user->can('PromoTrafficBlockSwitchPartner') || !$this->userId) {
      return '';
    }

    $ajaxUrl = Url::to(['/promo/traffic-block/switch-partner', 'userId' => $this->userId]);
    $successAlert = Alert::success(Yii::_t('app.common.Saved successfully'));
    $failAlert = Alert::danger(Yii::_t('app.common.Save failed'));
    $confirmMsg = Yii::_t('app.common.Are you sure?');
    // JS можно вынести в ассеты, но пока (а может и вообще) лучше не мусорить.
    // Класс виджета самодостаточный и даже без вьюх
    $this->view->registerJs(<<<JS
      $('[name=is_blacklist_traffic_blocks]').click(function () {
        var radio = $(this);
        var newOptionValue = radio.filter(':checked').val();
  
        yii.confirm('$confirmMsg', function () {
          $.post('$ajaxUrl', {'value': newOptionValue}, function (response) {
            if (response.hasOwnProperty('success') && response.success === true) {
              radio.filter('[value=' + newOptionValue + ']').prop('checked', true);
              $successAlert;
              return;
            }
            $failAlert;
          }).fail(function() {
            $failAlert;
          });
          
        });
        return false;
      });
JS
    );

    $isBlacklist = (new UserPromoSettings())->getIsBlacklistTrafficBlocks($this->userId);
    return Html::radioList('is_blacklist_traffic_blocks', (int)$isBlacklist, $this->getRadioLabels(), [
      'item' => function ($index, $label, $name, $checked, $value) {
        $hintText = $value ? $this->getIsBlacklistTrueHint() : $this->getIsWhitelistFalseHint();
        return '<div class="radio"><label>' .
          Html::radio($name, $checked, ['value' => $value]) . $label . '<p class="text-muted">' . $hintText . '</p>' .
          '</label></div>';
      }]);
  }

  /**
   * @return array
   */
  protected function getRadioLabels()
  {
    return [
      0 => TrafficBlock::t('is_blacklist_traffic_blocks_false_label'),
      1 => TrafficBlock::t('is_blacklist_traffic_blocks_true_label')
    ];
  }

  /**
   * @return string
   */
  protected function getIsBlacklistTrueHint()
  {
    return TrafficBlock::t('is_blacklist_traffic_blocks_true_hint');
  }

  /**
   * @return string
   */
  protected function getIsWhitelistFalseHint()
  {
    return TrafficBlock::t('is_blacklist_traffic_blocks_false_hint');
  }
}
