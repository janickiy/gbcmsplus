<?php

namespace mcms\partners\components\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\httpclient\Client;
use yii\httpclient\CurlTransport;

/**
 * Виджет для проверки припаркованости домена на IP, прописанном в настройке приложения "Domain registration -> [DOMAINS] A-record"
 * В domainSelector необходимо передать ??? js-указатель ??? на место, где искать домен для проверки
 * Пример использования: CheckDomainWidget::widget(['domainSelector' => '$(\'#linkstep1form-domain_id option:selected\').html()'])
 */
class CheckDomainWidget extends Widget
{
  const BUTTON_ID_PREFIX = 'check-domain-';
  const API_URL = 'http://ip-api.com/json/';
  /**
   * @var string
   */
  public $domainSelector;
  /**
   * @var string
   */
  protected $buttonId;

  /**
   * @inheritdoc
   */
  public function init()
  {
    parent::init();

    $this->buttonId = self::BUTTON_ID_PREFIX . $this->id;
    $this->registerAsset();
  }

  /**
   * @inheritdoc
   */
  public function run()
  {
    return $this->render('check_domain', ['buttonId' => $this->buttonId]);
  }

  private function registerAsset()
  {
    $errorMessage = Yii::_t('links.check_domain_error');
    $successMessage = Yii::_t('links.check_domain_success');

    $url = Url::to(['domains/check']);

    $this->view->registerJs(
      <<<JS
        $('#{$this->buttonId}').tooltip();
        $('#{$this->buttonId}').click(function() {
          var \$t = $(this);
          \$t.attr('disabled', true);
          
          const url = document.createElement('a');
          url.setAttribute('href', {$this->domainSelector});
          
          var link = '$url?host=' + url.hostname;
          
          $.get(link, function(equals) {
            \$t.attr('data-original-title', equals ? '$successMessage' : '$errorMessage').tooltip('show');
            setTimeout(function() {
              \$t.tooltip('hide');
            }, 2000);
            
            \$t.removeClass('tooltip-success tooltip-danger').addClass(equals ? 'tooltip-success' : 'tooltip-danger');
            
            \$t.attr('disabled', false);
          });
        });
JS
    );

    $this->view->registerCss(
      <<<CSS
      .tooltip-danger + .tooltip.top > .tooltip-inner {
        background-color: #F16453; color: #fff;
      }
      .tooltip-danger + .tooltip.top > .tooltip-arrow:after {
        border-top-color: #F16453;
      }
      .tooltip-success + .tooltip.top > .tooltip-inner {
        background-color: #86C290; color: #fff;
      }
      .tooltip-success + .tooltip.top > .tooltip-arrow:after {
         border-top-color: #86C290;
      }
CSS

    );
  }

  /**
   * @param $host
   * @return bool
   * @throws \yii\base\InvalidConfigException
   */
  public static function isHostParked($host)
  {
    $url = self::API_URL . $host;

    $client = Yii::createObject(Client::class);
    $client->setTransport(CurlTransport::class);

    $request = $client->createRequest()
      ->setMethod('get')
      ->setUrl($url)
      ->setData(['fields' => 'query']);

    try {
      $response = $request->send();
    } catch (\Exception $e) {
      return false;
    }

    $aDomainIp = Yii::$app->getModule('promo')->getSettingsDomainIp();
    $responseArray = Json::decode($response->getContent());

    return ArrayHelper::getValue($responseArray, 'query') === $aDomainIp;
  }
}