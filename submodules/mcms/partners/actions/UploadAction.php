<?php

namespace mcms\partners\actions;

use vova07\fileapi\Widget;
use yii\base\DynamicModel;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
use yii\web\BadRequestHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use Yii;

/**
 * UploadAction for images and files.
 *
 * Usage:
 * ```php
 * public function actions()
 * {
 *     return [
 *         'upload' => [
 *             'class' => 'vova07\fileapi\actions\UploadAction',
 *             'path' => '@path/to/files',
 *             'uploadOnlyImage' => false
 *         ]
 *     ];
 * }
 * ```
 */
class UploadAction extends \vova07\fileapi\actions\UploadAction
{
  /**
   * @var string Path to directory where files will be uploaded
   */
  public $path;

  /**
   * @var string Validator name
   */
  public $uploadOnlyImage = true;

  /**
   * @var string The parameter name for the file form data (the request argument name).
   */
  public $paramName = 'file';

  /**
   * @var boolean If `true` unique filename will be generated automatically
   */
  public $unique = true;

  /**
   * @var array Model validator options
   */
  public $validatorOptions = [];

  /**
   * @var string Model validator name
   */
  private $_validator = 'image';

  const TEMP_FILE_COUNT = 10;

  /**
   * @inheritdoc
   */
  public function run()
  {
    if (Yii::$app->request->isPost) {
      $file = UploadedFile::getInstanceByName($this->paramName);
      $model = new DynamicModel(compact('file'));
      $model->addRule('file', $this->_validator, $this->validatorOptions)->validate();

      if ($model->hasErrors()) {
        $result = [
          'error' => $model->getFirstError('file')
        ];
      } else {
        if ($this->unique === true && $model->file->extension) {

          $files = FileHelper::findFiles($this->path);
          if (count($files) >= self::TEMP_FILE_COUNT) @unlink(array_shift($files));

          $model->file->name =  md5(Yii::$app->user->id) . time() . '.' . $model->file->extension;
        }
        if ($model->file->saveAs($this->path . $model->file->name)) {
          $result = ['name' => $model->file->name];
        } else {
          $result = ['error' => Widget::t('fileapi', 'ERROR_CAN_NOT_UPLOAD_FILE')];
        }
      }
      Yii::$app->response->format = Response::FORMAT_JSON;

      return $result;
    } else {
      throw new BadRequestHttpException('Only POST is allowed');
    }
  }
}
