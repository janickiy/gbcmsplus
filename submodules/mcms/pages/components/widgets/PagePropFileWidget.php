<?php

namespace mcms\pages\components\widgets;

use mcms\common\multilang\LangAttribute;
use mcms\pages\models\CategoryProp;
use mcms\pages\models\Page;
use mcms\pages\models\PageProp;
use Yii;
use yii\base\Widget;
use mcms\common\helpers\Html;
use yii\helpers\Url;

class PagePropFileWidget extends Widget
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

  /** @var PageProp */
  private $pageProp;

  private $imagesDelete = [];
  private $previews = [];

  public function init()
  {
    parent::init();
  }

  /**
   * @inheritdoc
   */
  public function run()
  {
    $this->pageProp = $this->page->getProps()->where(['page_category_prop_id' => $this->categoryProp->id])->one()
      ?
      : new PageProp(['page_category_prop_id' => $this->categoryProp->id]);

    $this->initPreview();

    $render = $this->render('page_prop_file', [
      'form' => $this->form,
      'pageProp' => $this->pageProp,
      'categoryProp' => $this->categoryProp,
      'previews' => $this->previews,
      'imagesDelete' => $this->imagesDelete
    ]);

    PagePropsWidget::$counter++;

    return $render;

  }

  private function initPreview()
  {
    if ($this->pageProp->isNewRecord) return;

    $multilang = $this->pageProp->multilang_value;

    if (!$multilang instanceof LangAttribute) return;

    $uploadUrl = PageProp::getUploadUrl($this->categoryProp->id);
    $canDelete = Html::hasUrlAccess(['/pages/pages/file-delete/']);
    foreach ($multilang as $lang => $files) {
      foreach ($files as $file) {
        $this->previews[$lang][] = Html::img($uploadUrl . '/' . $file, ['class' => 'file-preview-image', 'width' => '90%']);
        $deleteData = [
          'key' => $file,
          'extra' => [
            'lang' => $lang
          ],
          'frameAttr' => [
            'style' => 'height:80px',
            'title' => 'My Custom Title',
          ],
        ];
        $canDelete && $deleteData['url'] = Url::toRoute(['file-delete', 'isProp' => 1, 'id' => $this->pageProp->id]);
        $this->imagesDelete[$lang][] = $deleteData;
      }
    }

  }



}