<?php

namespace mcms\modmanager\controllers;


use mcms\common\helpers\ArrayHelper;
use mcms\common\traits\Flash;
use mcms\modmanager\models\Module;
use Yii;
use mcms\common\controller\AdminBaseController;
use yii\bootstrap\Html;
use yii\helpers\FileHelper;
use yii\helpers\VarDumper;
use yii\helpers\BaseInflector;

/**
 * Class MessagesController
 * @package mcms\modmanager\controllers
 *
 */
class MessagesController extends AdminBaseController
{

  use Flash;

  const CONST_STRLEN_TEXTAREA = 50;
  const LOCAL_SUFFIX = '-local';

  public $layout = '@app/views/layouts/main';

  /**
   * Отображение и сохранение перевода
   * @param $module_id
   * @return string|\yii\web\Response
   */
  public function actionEdit($module_id)
  {
    if (!Module::canEditTranslations($module_id)) {
      return $this->redirect(['modules/index']);
    }

    $this->getView()->title = Yii::_t('main.edit_messages');

    $messages = Yii::$app->getModule($module_id)->messages;

    $dir = Yii::getAlias($messages);

    if (is_dir($dir)) {
      $languages = Yii::$app->params['languages'];
      $data = [];

      foreach ($languages as $lang) {
        $langDir = $dir . DIRECTORY_SEPARATOR . $lang;
        if (is_dir($langDir)) {

          $files = array_reduce(FileHelper::findFiles($langDir, [DIRECTORY_SEPARATOR . '.php']),
            function ($result, $file) {
              $result[basename($file, ".php")] = $file;
              return $result;
            }, []);

          foreach ($files as $filename => $file) {
            if (array_key_exists($filename . self::LOCAL_SUFFIX, $files)) continue;
            $name = str_replace(self::LOCAL_SUFFIX, '', $filename);
            $fields = substr_count($filename, self::LOCAL_SUFFIX) > 0
              ? ArrayHelper::merge(include($files[$name]), include($file))
              : include($file)
            ;
            $data[$lang][$name] = $this->getHtmlFromFileItems($fields, $lang, $name);
          }
        }
      }

      if (Yii::$app->request->isPost) {
        $post = array_filter(Yii::$app->request->post(), function ($k) use ($languages) {
          return in_array($k, $languages);
        }, ARRAY_FILTER_USE_KEY);

        foreach ($post as $langFolder => $files) {
          foreach ($files as $fileName => $data) {
            $path = $dir . DIRECTORY_SEPARATOR . $langFolder . DIRECTORY_SEPARATOR . $fileName . self::LOCAL_SUFFIX . '.php';

            // очищаем теги
            $data = array_map(['\yii\helpers\HtmlPurifier', 'process'], $data);

            if ($this->saveToFile($data, $path) === false) {
              $this->flashFail('messages.save_error');
            }
          }
        }

        $data = $this->transformPostToFormData($post);
        $this->flashSuccess('main.saved');
      }
      return $this->render('form', [
        'data' => $data
      ]);
    }

    return $this->redirect(['edit', 'module_id' => $module_id]);

  }

  /**
   * Формирование html кода для фраз перевода(инпуты) для вывода в форме
   * @param $fields
   * @param $lang
   * @param $filename
   * @return string
   */
  protected function getHtmlFromFileItems($fields, $lang, $filename)
  {
    $html = '';
    foreach ($fields as $label => $content) {
      $content = \yii\helpers\HtmlPurifier::process($content);
      if (strlen($content) > self::CONST_STRLEN_TEXTAREA) {
        $html .= Html::label($label);
        $html .= Html::textarea($lang . '[' . $filename . '][' . $label . ']', $content, ['class' => 'form-control']);
      } else {
        $html .= Html::label($label);
        $html .= Html::textinput($lang . '[' . $filename . '][' . $label . ']', $content, ['class' => 'form-control']);
      }
    }
    return $html;
  }

  /**
   * сохранение отредактированного перевода в файл
   * @param $data
   * @param $file
   * @return bool|integer
   */
  protected function saveToFile($data, $file)
  {
    return file_put_contents($file, "<?php\nreturn " . VarDumper::export($data) . ";\n", LOCK_EX);
  }

  /**
   * преобразования несохраненного перевода пользователя к выводу на форму
   * @param array $post
   * @return array
   */
  protected function transformPostToFormData($post)
  {
    foreach ($post as $langFolder => $files) {
      foreach ($files as $fileName => $data) {
       $post[$langFolder][$fileName] = $this->getHtmlFromFileItems($post[$langFolder][$fileName], $langFolder, $fileName);
      }
    }

    return $post;
  }
}