<?php

namespace mcms\pages\components\widgets;

use mcms\pages\models\CategoryProp;
use mcms\pages\models\Page;
use mcms\pages\models\PageProp;
use Yii;
use yii\base\Widget;

class PagePropCheckboxWidget extends Widget
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
    $pageProps = $this->page->getProps()->where([
      'page_category_prop_id' => $this->categoryProp->id
    ])->all();

    return $this->render('page_prop_checkbox', [
      'form' => $this->form,
      'pageProps' => empty($pageProps)
        ? [new PageProp(['page_category_prop_id' => $this->categoryProp->id])]
        : $pageProps
      ,
      'categoryProp' => $this->categoryProp
    ]);
  }
}