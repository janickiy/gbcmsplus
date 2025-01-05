<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 22.08.16
 * Time: 20:41
 */

namespace mcms\partners\components\widgets;


use yii\base\Widget;

class TagsTableWidget extends Widget
{
  public $targetId;

  public $data;

  public function init()
  {
    parent::init();
    $this->registerJS();
    $this->registerCSS();
  }

  public function run()
  {
    $even = 0;
    $table = "<table class='table table-striped table-small'>";
    foreach ($this->data as $replacement => $label) {
      $table .= "<tr" . (($even = !$even) ? "class='even'" : "") . ">
        <td class='insert-tag-{$this->targetId}' style='padding-left: 10px !important;'><a href=#>$replacement</a></td>
        <td style='padding-left: 10px !important;'>$label</td>
        </tr>";
    }
    $table .= "</table>";
    return $table;
  }


  public function registerJS()
  {
    $js = <<<JS
    $(document).on('click','.insert-tag-$this->targetId a', function(e){
      e.preventDefault();
      var tag = $(this).html();
      var tagName = tag.replace(/[}{]/g, "");
      var link = $('#$this->targetId').val();
      
      if (link.indexOf(tagName) >= 0)
          return false;
      
      if (link.indexOf('?') < 0) {
        link += '?' + tagName + '=' + tag;
      } else {
        if (link[link.length-1] === '?' || link[link.length-1] === '&') {
            link += tagName + '=' + tag;
        } else {
            link += '&' + tagName + '=' + tag;
        }
      }
      
      $('#$this->targetId').val(link);
      $('#$this->targetId').parents('form').yiiActiveForm('validateAttribute', '$this->targetId');
    });
JS;
    $this->view->registerJs($js);
  }

  public function registerCSS()
  {
    $css = <<<CSS
    .insert-tag-{$this->targetId} a{
      border-bottom: 1px dashed;
      text-decoration: none;
    }
CSS;
    $this->view->registerCss($css);
  }
}