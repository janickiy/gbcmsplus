<?php

namespace mcms\pages\components\widgets;

use mcms\pages\models\Category;
use mcms\pages\models\Page;
use Yii;
use yii\base\Widget;
use yii\web\View;

class PagePropsWidget extends Widget
{

  public static $counter = 0;

  /**
   * @var \kartik\form\ActiveForm
   */
  public $form;

  /**
   * @var Page
   */
  public $page;

  const PJAX_CONTAINER = 'pagePropsPjax';

  public function init()
  {
    parent::init();
  }

  /**
   * @inheritdoc
   */
  public function run()
  {

    $categoryProps = $this->page->isNewRecord ? false : [];
    if ($this->page->page_category_id && $category = Category::findOne($this->page->page_category_id) ) {
      $categoryProps = $category->getProps()->orderBy(['id' => SORT_ASC])->all();
    }

    $render = $this->render('page_props', [
      'page' => $this->page,
      'form' => $this->form,
      'categoryProps' => $categoryProps
    ]);

    $this->view->registerJs('var propsCounter = ' . self::$counter . ';', View::POS_END);

    return $render;
  }
}