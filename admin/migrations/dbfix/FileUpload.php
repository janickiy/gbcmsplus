<?php

namespace admin\migrations\dbfix;

use mcms\common\helpers\ArrayHelper;
use Yii;
use kartik\builder\Form;
use yii\helpers\FileHelper;
use mcms\common\helpers\Html;
use yiidreamteam\upload\FileUploadBehavior;

class FileUpload extends SettingsAbstract
{
  protected $type = Form::INPUT_FILE;
  protected $options = ['class' => 'btn btn-default'];
  /**
   * "jpg, gif, png"
   * @var StringObject
   */
  protected $extensions;
  protected $imageValidation;

  private $moduleId;

  private $uploadDir;
  const DEFAULT_UPLOAD_DIR = '@uploadPath/{moduleId}/{attributeName}';

  private $uploadUrl;
  const DEFAULT_UPLOAD_URL = '@uploadUrl/{moduleId}/{attributeName}';

  const FILE_NAME_PARAM = 'fileName';
  const DIR_PARAM = 'dir';
  const URL_PARAM = 'url';

  private $defaultFile;

  function __construct($moduleId)
  {
    $this->moduleId = $moduleId;
  }

  public function getFormAttributes()
  {
    return array_merge(parent::getFormAttributes(), [
      'options' => $this->options,
    ]);
  }

  public function getBehaviors()
  {
    return array_merge(parent::getBehaviors(), [
      [
        'class' => FileUploadBehavior::class,
        'attribute' => $this->getKey(),
      ]
    ]);
  }

  public function setExtensions($extensions)
  {
    $this->extensions = $extensions;
    return $this;
  }

  public function setImageValidation($imageValidation)
  {
    $this->imageValidation = $imageValidation;
    return $this;
  }

  public function beforeValue(&$value)
  {
    $deleteFile = Yii::$app->request->post('delete', []);
    $value = $value ? : $this->getValue();

    if (isset($deleteFile[$this->getKey()])) {
      @unlink($this->getFilePath());
      $value = '';
    }

    if (!$value instanceof \yii\web\UploadedFile) return;

    $fileName = uniqid();
    /** @var \yii\web\UploadedFile $value */

    $fullName = $value->extension ? sprintf('%s.%s', $fileName, $value->extension) : $fileName;

    $path = $this->convertPath(($this->uploadDir ?: self::DEFAULT_UPLOAD_DIR), $fullName);

    FileHelper::createDirectory(pathinfo($path, PATHINFO_DIRNAME), 0775, true);
    if ($value->saveAs($path)) {
      $value = $fullName;
    }
  }

  public function getHint()
  {
    $defaultFileLink = $this->uploadUrl && $this->getUrl()
      ? Html::tag(
        'div',
        \yii\helpers\Html::a('View default file', $this->getUrl(), ['target' => '_blank', 'data-pjax' => '0'])
      )
      : '';

    return ($this->getValue() ? Html::tag('div',
      Html::tag('div', '<input type="submit" name="delete[' . $this->getKey() . ']" value="' . Yii::t('yii', 'Delete') . '" class="btn btn-xs btn-default">', ['class' => 'col-xs-1']) .
      Html::tag('div', basename($this->getValue()), ['class' => 'col-xs-11'])
      , ['class' => 'row', 'style' => 'margin-top: 15px'])
      : '') . $defaultFileLink;
  }

  public function getValidator()
  {
    if ($this->extensions) {
      return [
        ['file', ['extensions' => $this->extensions]]
      ];
    }
    if ($this->imageValidation) {
      return [
        ['image', $this->imageValidation]
      ];
    }
    return [
      ['file']
    ];
  }

  public function setUploadDir($uploadDir)
  {
    $this->uploadDir = $uploadDir;
    return $this;
  }

  private function convertPath($str, $fileName)
  {
    $replaced = strtr($str, [
      '{attributeName}' => $this->getKey(),
      '{moduleId}' => $this->moduleId
    ]);
    return Yii::getAlias(sprintf('%s/%s', $replaced, $fileName));
  }

  public function setDefaultFile($defaultFile)
  {
    $this->defaultFile = $defaultFile;
    return $this;
  }

  public function getDefaultFileUrl()
  {
    $fileUrl = ArrayHelper::getValue($this->defaultFile, self::URL_PARAM, self::DEFAULT_UPLOAD_URL);
    $fileName = ArrayHelper::getValue($this->defaultFile, self::FILE_NAME_PARAM);

    if (!$fileName) return '';

    return $fileName ? $this->convertPath($fileUrl, $fileName) : '';
  }

  public function getFilePath()
  {
    $dir = $this->getValue()
      ? $this->uploadDir
      : ArrayHelper::getValue($this->defaultFile, self::DIR_PARAM, self::DEFAULT_UPLOAD_DIR)
    ;

    $fileName = $this->getValue()
      ?
      : ArrayHelper::getValue($this->defaultFile, self::FILE_NAME_PARAM)
    ;

    if (!$fileName) return '';

    return $this->convertPath($dir, $fileName);
  }

  public function setUploadUrl($uploadUrl)
  {
    $this->uploadUrl = $uploadUrl;
    return $this;
  }

  public function getUrl()
  {
    $fileUrl = $this->getValue()
      ? $this->uploadUrl
      : ArrayHelper::getValue($this->defaultFile, self::URL_PARAM, self::DEFAULT_UPLOAD_URL)
    ;

    $fileName = $this->getValue()
      ?
      : ArrayHelper::getValue($this->defaultFile, self::FILE_NAME_PARAM)
    ;

    if (!$fileName) return '';

    return $this->convertPath($fileUrl, $fileName);
  }
}
