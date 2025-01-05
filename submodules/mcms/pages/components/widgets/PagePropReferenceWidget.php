<?php

namespace mcms\pages\components\widgets;

use mcms\common\helpers\ArrayHelper;
use mcms\pages\models\CategoryProp;
use mcms\pages\models\Page;
use mcms\pages\models\PageProp;
use Yii;
use yii\base\Widget;

class PagePropReferenceWidget extends Widget
{

  /**
   * @var \kartik\form\ActiveForm
   */
  public $form;

  /**
   * @var CategoryProp;
   */
  public $categoryProp;

  /**
   * @var Page
   */
  public $page;

  public function init()
  {
    parent::init();
  }

  /**
   * @inheritdoc
   */
  public function run()
  {
    $entityIds = ArrayHelper::getColumn($this->page->getProps()->where([
      'page_category_prop_id' => $this->categoryProp->id
    ])->each(), 'entity_id');

    return $this->render('page_prop_reference', [
      'form' => $this->form,
      'pageProp' => new PageProp([
        'page_category_prop_id' => $this->categoryProp->id,
        'entities' => $entityIds
      ]),
      'categoryProp' => $this->categoryProp
    ]);
  }
}