<?php

namespace mcms\promo\models;

use mcms\common\traits\Translate;

/**
 * Настройки хэндлера Default
 */
class ProviderSettingsDefault extends AbstractProviderSettings
{
  use Translate;

  const LANG_PREFIX = 'promo.provider_settings.';

  /** @var string */
  public $transaction_type_alias;
  /** @var string */
  public $custom_postback_status_on;
  /** @var string */
  public $custom_postback_status_off;
  /** @var string */
  public $custom_postback_status_onetime;
  /** @var string */
  public $custom_postback_status_rebill;
  /** @var string */
  public $custom_postback_status_complaint;
  /** @var string */
  public $custom_postback_status_refund;
  /** @var int */
  public $parse_raw_data;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [
        [
          'transaction_type_alias',
          'custom_postback_status_on',
          'custom_postback_status_off',
          'custom_postback_status_onetime',
          'custom_postback_status_rebill',
          'custom_postback_status_complaint',
          'custom_postback_status_refund',
        ],
        'string'
      ],
      [['parse_raw_data',], 'integer'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return $this->translateAttributeLabels([
      'transaction_type_alias',
      'custom_postback_status_on',
      'custom_postback_status_off',
      'custom_postback_status_onetime',
      'custom_postback_status_rebill',
      'custom_postback_status_complaint',
      'custom_postback_status_refund',
      'parse_raw_data',
    ]);
  }

  /**
   * @inheritdoc
   */
  public function attributeHints()
  {
    return [
      'custom_postback_status_on' => static::t('hint-custom_postback_status_on'),
      'custom_postback_status_off' => static::t('hint-custom_postback_status_off'),
      'custom_postback_status_onetime' => static::t('hint-custom_postback_status_onetime'),
      'custom_postback_status_rebill' => static::t('hint-custom_postback_status_rebill'),
      'custom_postback_status_complaint' => static::t('hint-custom_postback_status_complaint'),
      'custom_postback_status_refund' => static::t('hint-custom_postback_status_refund'),
    ];
  }

  /**
   * Название вьюхи для формы
   * @return string
   */
  public function getViewName()
  {
    return 'default';
  }
}