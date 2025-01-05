<?php
use mcms\common\helpers\Link;
use yii\bootstrap\Html;
use mcms\common\grid\ContentViewPanel;
use mcms\common\widget\modal\Modal;
use yii\helpers\Url;

?>

<?php if($modules): ?>
  <?php ContentViewPanel::begin([
    'padding' => false,
  ]);
  ?>
  <table class="table table-striped">
    <tr>
      <th></th>
      <th><?= Yii::_t('main.module_id') ?></th>
      <th><?= Yii::_t('main.module_name') ?></th>
    </tr>
    <?php foreach($modules as $module):?>
      <tr>
        <td>
          <div class="btn-group">
            <?= Link::get('/notifications/settings/view', ['id' => $module['id']], ['class' => 'btn btn-xs btn-default'], '<i class="glyphicon glyphicon-pencil"></i>') ?>
          </div>
        </td>
        <td><?= $module['id'] ?></td>
        <td><?= Yii::_t($module['name']) ?></td>
      </tr>
    <?php endforeach;?>
  </table>
  <?php ContentViewPanel::end() ?>
<?php endif ?>