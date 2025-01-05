<?php

namespace mcms\promo\components\api;

use Yii;
use mcms\common\helpers\FormHelper;
use mcms\common\module\api\ApiResult;
use mcms\common\helpers\ArrayHelper;

use mcms\promo\models\Source as LinkSource;
use mcms\promo\models\Source;

class SourceCreate extends ApiResult
{
  protected $postData;
  protected $userId;
  protected $save;
  protected $formName;
  protected $attributes;

  public function init($params = [])
  {
    $this->postData = ArrayHelper::getValue($params, 'postData');
    $this->userId = ArrayHelper::getValue($params, 'userId');
    $this->save = ArrayHelper::getValue($params, 'save');
    $this->formName = ArrayHelper::getValue($params, 'formName');
    $this->attributes = ArrayHelper::getValue($params, 'attributes');
  }

  public function getResult()
  {
    $source = new LinkSource([
      'status' => LinkSource::STATUS_MODERATION,
      'source_type' => LinkSource::SOURCE_TYPE_WEBMASTER_SITE
    ]);
    $source->scenario = LinkSource::SCENARIO_PARTNER_CREATE_WEBMASTER_SOURCE;
    $source->user_id = $this->userId;

    $source->load($this->postData, $this->formName);

    // Попытка найти источник в БД
    if ($source->refresh()) {
      $source->load($this->postData, $this->formName); // Повторно загрузим данные из запроса
    }

    if (!$this->save) {
      return FormHelper::validate($source, $this->attributes, strtolower($this->formName));
    } else {
      $source->initHash();
      //Добавляем дефолтный преленд, чтобы 3 шаге сохранения формы преленды не удалились
      $source->addPrelandOperatorIds = $source->getAddPrelandOperatorIds();
      $transaction = Yii::$app->db->beginTransaction();

      $result = false;
      try {
        $result = $source->save();
        $transaction->commit();
      } catch (\Exception $e) {
        $transaction->rollBack();
      } finally {
        return [
          'success' => $result,
          'id' => $source->id,
          'hash' => $source->hash,
          'errors' => $source->getErrors()
        ];
      }


    }
  }

}