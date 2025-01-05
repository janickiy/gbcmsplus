<?php
use mcms\common\grid\ContentViewPanel;
use yii\helpers\Html;
use wbraganca\fancytree\FancytreeWidget;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\Pjax;
use yii\helpers\Url;
use mcms\common\widget\alert\Alert;
use mcms\user\admin\assets\AuthTreeAsset;

/** @var array $roles */
/** @var View $this */
/** @var array $data */

AuthTreeAsset::register($this);

$treeTableId = 'auth_treetable';
$refreshBtnId = 'refresh_tree';
$collapseBtnId = 'collapse_tree';
$expandBtnId = 'expand_tree';
$searchInputId = 'search_tree';
$pjaxContainer = 'pjaxContainer';
$assignUrl = Url::to(['/users/admin/tree/assign']);
$treeUrl = Url::to(['/users/admin/tree/get-tree']);
$confirmTextAssign = Yii::_t('users.tree.confirm_assign');
$confirmTextRevoke = Yii::_t('users.tree.confirm_revoke');
$alertSuccess = Alert::success(Yii::_t('app.common.operation_success'));
$alertFailure = Alert::danger(Yii::_t('app.common.operation_failure'));

?>


<?php ContentViewPanel::begin([
  'padding' => false,
  'toolbar' =>
    Html::a('<i class="fa fa-refresh"></i>', 'javascript:void(0);', [
      'class' => 'btn btn-xs btn-info',
      'title' => Yii::_t('users.tree.refresh'),
      'id' => $refreshBtnId
    ]) . ' ' .
    Html::beginTag('div', ['class' => 'btn-group']) .
    Html::a(Yii::_t('users.tree.collapse_all'), 'javascript:void(0);', [
      'class' => 'btn btn-xs btn-success',
      'id' => $collapseBtnId
    ]) .
    Html::a(Yii::_t('users.tree.expand_all'), 'javascript:void(0);', [
      'class' => 'btn btn-xs btn-success',
      'id' => $expandBtnId
    ]) .
    Html::endTag('div') . ' ' .
    Html::textInput($searchInputId, null, [
      'class' => 'form-control',
      'id' => $searchInputId,
      'placeholder' => Yii::_t('users.tree.search')
    ])
]);
?>

<?php Pjax::begin(['id' => $pjaxContainer]) ?>

<?= FancytreeWidget::widget([
  'options' => [
    'id' => $treeTableId,
    'source' => $data,
    'extensions' => ['table', 'filter', 'persist'],
    'renderColumns' => new JsExpression('function (event, data) {
      var node = data.node;
      var $tdList = $(node.tr).find(">td");
          
      var roles = ' . json_encode($roles) . ';
      var i = 0;
      $.each(roles, function (role) {
        i++;
        
        if (role == node.key) return; 
        
        var assignStatus = node.data.assigns[role];
        var aTag = document.createElement("a");
        aTag.setAttribute("href", "#");
        aTag.setAttribute("data-assign", assignStatus == 0 ? 1 : 0);
        aTag.setAttribute("data-role", role);
        aTag.setAttribute("data-item", node.key);
        aTag.setAttribute("data-item-description", node.title);
        if (assignStatus == 1) {
          aTag.innerHTML = "<i class=\"fa fa-circle text-success\"></i>";
          $tdList.eq(i).html(aTag.outerHTML);
          return;
        }
        if (assignStatus == 2) {
          $tdList.eq(i).html("<i class=\"fa fa-circle-o text-success\"></i>");
          return;
        }
        
        aTag.innerHTML = "<i class=\"fa fa-circle-thin text-muted\"></i>";
        $tdList.eq(i).html(aTag.outerHTML);
      });
    }'),
    'filter' => [
      'mode' => 'hide'
    ]
  ],
]);
?>

<?php $this->registerJs(<<<JS
  function getFilterText(selector) {
    return $(selector).val().toLowerCase()
  }
  function filterTree(tree, match) {
      tree.filterNodes(function(node) {
      return node.title.toLowerCase().indexOf(match) !== -1 || node.key.toLowerCase().indexOf(match) !== -1;
    }, {"autoExpand" : true});
  }
  
  $('#$collapseBtnId').click(function(){
    $("#$treeTableId").fancytree("getRootNode").visit(function(node){
      node.setExpanded(false);
    });
  });
  $('#$expandBtnId').click(function(){
    $("#$treeTableId").fancytree("getRootNode").visit(function(node){
      node.setExpanded(true);
    });
  });
  $('#$refreshBtnId').click(function(){
    $.ajax({
        url: "$treeUrl",
        success: function (response) {
            $("#$treeTableId").fancytree('getTree').reload({children: response}).done(function(){ $alertSuccess });
        },
        error: function (response) {
          $alertFailure
        }
      });
  });
  
  var tree = $("#$treeTableId").fancytree("getTree");
  
  // Применение фильтра через 100мс после ввода последнего символа
  var searchTimeout = null;
  $('#$searchInputId').keyup(function (e) {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(function () {
      var match = getFilterText('#$searchInputId');

      if (e && e.which === $.ui.keyCode.ESCAPE || $.trim(match) === "") {
        tree.clearFilter();
        $(this).val("");
        return;
      }

      filterTree(tree, match);
    }, 100);
  });

  $(document).on('click', 'a[data-assign]', function () {
    var link = $(this);
    var text = link.data("assign") ? "$confirmTextAssign" : "$confirmTextRevoke";
    text += '\\n' + link.data("role") + '\\n' + link.data("item-description");
    if (!confirm(text)) {
      return false;
    }
    $.ajax({
        url: "$assignUrl",
        method: 'post',
        data: {
          "role" : link.data("role"),
          "item" : link.data("item"),
          "assign" : link.data("assign"),
        },
        success: function (response) {
          if (response.success) {
            var tree = $("#$treeTableId").fancytree('getTree'),
                filterText = getFilterText('#$searchInputId');
            tree.clearFilter();
            tree.reload(response.data.tree).done(function(){ $alertSuccess });
            if (filterText.length > 0) filterTree(tree, filterText);    
            return;
          }
          $alertFailure
        },
        error: function (response) {
          $alertFailure
        }
      });
    return false;
  });
  
  var tableOffset = $("#$treeTableId").offset().top;
  var header = $("#$treeTableId > thead").clone();
  var colgroup = $("#$treeTableId > colgroup").clone();
  var firstWidth = $("#$treeTableId > thead > tr > th:first-child").width();
  colgroup.find("col:first-child").width(firstWidth);
  $("#header-fixed").append(colgroup);
  var fixedHeader = $("#header-fixed").append(header);
  
  $(window).bind("scroll", function() {
    var offset = $(this).scrollTop();
    
    if (offset >= tableOffset && fixedHeader.is(":hidden")) {
      fixedHeader.show();
      return;
    }
    if (offset < tableOffset) {
      fixedHeader.hide();
    }
  });
  
JS
); ?>

<table id="<?= $treeTableId ?>" class="tree-table">
    <colgroup>
        <col width="*">
      <?php foreach ($roles as $role): ?>
        <?= Html::tag('col', '', ['width' => '50px']) ?>
      <?php endforeach ?>
    </colgroup>
    <thead>
    <tr>
        <th></th>
      <?php foreach ($roles as $role): ?>
        <?= Html::tag('th', strlen($role) == 4 ? $role : substr($role, 0, 3) . '.', ['title' => $role]) ?>
      <?php endforeach ?>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td></td>
      <?php foreach ($roles as $role): ?>
        <?= Html::tag('td') ?>
      <?php endforeach ?>
    </tr>
    </tbody>
</table>

<table id="header-fixed"></table>

<?php Pjax::end() ?>

<?php ContentViewPanel::end() ?>


<p><i class="fa-fw fa fa-info"></i><?= Yii::_t('users.tree.legend') ?>:</p>
<p>
    &nbsp;&nbsp;<i class="fa fa-circle text-success"></i>&nbsp;
  <?= Yii::_t('users.tree.assigned') ?>&nbsp;&nbsp;
</p>
<p>
    &nbsp;&nbsp;<i class="fa fa-circle-o text-success"></i>&nbsp;
  <?= Yii::_t('users.tree.inherited') ?>&nbsp;&nbsp;
</p>

<p>
    &nbsp;&nbsp;<i class="fa fa-circle-thin text-muted"></i>&nbsp;
  <?= Yii::_t('users.tree.not_assigned') ?>&nbsp;&nbsp;
</p>