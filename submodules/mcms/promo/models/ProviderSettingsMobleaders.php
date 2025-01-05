<?php

namespace mcms\promo\models;

use mcms\common\traits\Translate;

/**
 * Настройки хэндлера Mobleaders
 */
class ProviderSettingsMobleaders extends AbstractProviderSettings
{
  use Translate;

  const LANG_PREFIX = 'promo.provider_settings.';

  public $preland_add_param;
  public $preland_off_param;
  public $mobleaders_user_id;
  public $api_url;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      ['api_url', 'filter', 'filter' => function ($value) {
        return rtrim($value, '/');
      }],
      [['preland_add_param', 'preland_off_param', 'mobleaders_user_id', 'api_url'], 'required'],
      [['preland_add_param', 'preland_off_param', 'mobleaders_user_id'], 'string'],
      ['api_url', 'url'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return $this->translateAttributeLabels([
      'preland_add_param',
      'preland_off_param',
      'mobleaders_user_id',
      'api_url',
    ]);
  }

  /**
   * @inheritdoc
   */
  public function attributeHints()
  {
    return [
      'preland_add_param' => static::t('attribute-preland_add_param_hint'),
      'preland_off_param' => static::t('attribute-preland_off_param_hint'),
    ];
  }

  /**
   * @inheritdoc
   */
  public function getViewName()
  {
    return 'external';
  }
}