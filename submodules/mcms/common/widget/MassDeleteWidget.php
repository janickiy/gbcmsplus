<?php
namespace mcms\common\widget;
use yii\base\Exception;
use rgk\utils\helpers\Html;
use Yii;
use yii\base\Widget;
use yii\db\ActiveRecord;
use yii\bootstrap\Html as BHtml;

/**
 * Виджет массового удаления
 * Class MassDeleteWidget
 * @package mcms\common\widget
 */
class MassDeleteWidget extends Widget
{
  public $url = ['mass-delete'];
  public $id;
  public $pjaxId;

  public function init()
  {
    parent::init();
    !$this->id && $this->id = Html::getUniqueId();
    $this->registerJs();
  }

  public function run()
  {
    return AjaxRequest::widget([
      'title' => BHtml::icon('trash') . ' ' . Yii::_t('commonMsg.main.mass-delete-label'),
      'confirm' => Yii::_t('commonMsg.main.mass-delete-confirm'),
      'url' => $this->url,
      'pjaxId' => $this->pjaxId,
      'beforeSubmit' => $this->beforeSubmitJs(),
      'buttonClass' => 'mass-delete-button',
      'options' => [
        'class' => 'btn btn-xs btn-success',
        'id' => $this->id,
        'disabled' => true,
      ],
    ]);
  }

  private function registerJs()
  {
    $js = <<<JS
  $(document).on('change', '[name="selection[]"], .select-on-check-all', function(event) {
  var selection = [];
  $('[name="selection[]"]').each(function(ind, element) {
    var element = $(element);
    if (element.is(":checked")) {
      selection.push(element.val());
    }
  });
  $('.mass-delete-button').attr('disabled', !selection.length);
});
$('{$this->pjaxId}').on('pjax:complete', function() {
    $('.mass-delete-button').attr('disabled', true);
  });
JS;
    $this->view->registerJs($js);
  }

  /**
   * JS, который выполнится перед отправкой данных на сервер
   * @return string
   */
  private function beforeSubmitJs()
  {
    return <<<JS
  var selection = [];
  $('[name="selection[]"]').each(function(ind, element) {
    var element = $(element);
    if (element.is(":checked")) {
      selection.push(element.val());
    }
  });
  var disabled = selection.length < 1;
  if (disabled) return false;
      
  $('#{$this->id}').data('value', JSON.stringify(selection));
JS;
  }

  /**
   * @param ActiveRecord $model
   * @param array $ids
   * @return bool
   */
  public static function deleteValues(ActiveRecord $model, array $ids = [])
  {
    $ids = array_map(function ($value) {
      return (int)$value;
    }, $ids);
    $transaction = Yii::$app->db->beginTransaction();
    try {
      foreach ($model::findAll($ids) as $value) {
        if (!$value->delete()) {
          $transaction->rollBack();
          return false;
        }
      }
      $transaction->commit();
    } catch (Exception $e) {
      $transaction->rollBack();
      return false;
    }

    return true;
  }
}