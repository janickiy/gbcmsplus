<?php

namespace mcms\partners\models;

use mcms\common\helpers\ArrayHelper;
use mcms\common\helpers\curl\Curl;
use mcms\common\validators\LocalhostUrlValidator;
use mcms\promo\models\Source;
use Yii;
use yii\base\Model;
use yii\helpers\Html;
use mcms\common\validators\UrlValidator;

/**
 * Форма тестирования постбек URL
 */
class TestPostbackUrlForm extends Model
{
  public $postbackTestLink;
  public $postbackUrl;
  public $linkId;
  public $on;
  public $off;
  public $rebill;
  public $cpa;
  private $statuses;

  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      ['postbackTestLink', 'required'],
      [['postbackTestLink'], UrlValidator::class, 'enableIDN' => true],
      [['postbackUrl', 'linkId', 'on', 'off', 'rebill', 'cpa'], 'safe'],
      [['postbackUrl', 'postbackTestLink'], LocalhostUrlValidator::class],
    ];
  }

  /**
   * @inheritdoc
   */
  public function attributeLabels()
  {
    return [
      'postbackTestLink' => Yii::_t('links.link_url'),
    ];
  }

  public function getResult()
  {
    if ($this->on) $this->statuses[] = 'on';
    if ($this->off) $this->statuses[] = 'off';
    if ($this->rebill) $this->statuses[] = 'rebill';
    if ($this->cpa) $this->statuses[] = 'sell';

    /* @var \mcms\promo\models\Source $link */
    $link = Yii::$app->getModule('promo')->api('getSource', [
      'source_id' => $this->linkId,
      'user_id' => Yii::$app->user->id,
    ])->getResult();

    if ($link) {
      preg_match('/([0-9\.]+)\s-/uis', $link->getIPs(), $ip);
      $ip = ArrayHelper::getValue($ip, 1);

      list($subid1, $subid2, $cid) = $this->getLabels($link, $this->postbackTestLink, $ip);

      $operatorId = $landingId = null;
      foreach ($link->sourceOperatorLanding as $operatorLanding) {
        $operatorId = $operatorLanding->operator_id;
        $landingId = $operatorLanding->landing_id;
        break;
      }
    } else {
      $subid1 = Yii::$app->security->generateRandomString(5);
      $subid2 = Yii::$app->security->generateRandomString(5);
      $cid = Yii::$app->security->generateRandomString(5);
      $operatorId = rand(1, 1000);
      $landingId = rand(1, 1000);
      $link = (object)[
        'stream_id' => rand(1, 1000),
        'id' => rand(1, 1000),
        'name' => Yii::$app->security->generateRandomString(10),
        'hash' => Yii::$app->security->generateRandomString(10),
        'user_id' => Yii::$app->user->id,
      ];
    }

    $params = [
      '{subid1}' => $subid1,
      '{subid2}' => $subid2,
      '{cid}' => $cid,
      '{subscription_id}' => rand(1, 1000),
      '{rebill_id}' => rand(1, 1000),
      '{stream_id}' => $link->stream_id,
      '{user_id}' => $link->user_id,
      '{link_id}' => $link->id,
      '{operator_id}' => $operatorId,
      '{landing_id}' => $landingId,
      '{link_name}' => $link->name,
      '{link_hash}' => $link->hash,
      '{action_time}' => time() - rand(60, 1000),
      '{action_date}' => date('Y-m-d H:i:s'),
      '{notice_time}' => time(),
      '{notice_date}' => date('Y-m-d H:i:s'),
      '{sum_rub}' => rand(1, 100),
      '{sum_eur}' => rand(1, 100),
      '{sum_usd}' => rand(1, 100),
      '{test}' => 1
    ];

    if (empty($this->statuses)) {
      $postbackUrl = strtr($this->postbackUrl, $params);
      return $this->testPostbackUrl($postbackUrl);
    }

    $result = '';
    foreach ($this->statuses as $status) {
      $params['{type}'] = $status;
      $postbackUrl = strtr($this->postbackUrl, $params);

      $result .= $status . PHP_EOL . $this->testPostbackUrl($postbackUrl) . PHP_EOL . PHP_EOL;
    }

    return $result;
  }

  private function testPostbackUrl($postbackUrl)
  {
    $data = parse_url($postbackUrl);
    parse_str(ArrayHelper::getValue($data, 'query'), $query);
    $query['test'] = 1;
    if (!$this->statuses) unset($query['status']);
    $postbackUrl = strtok($postbackUrl, '?') . '?' . http_build_query($query);

    $curl = (new Curl([
      'userAgent' => 'Mozilla/5.0 (iPhone; U; CPU like Mac OS X; en) AppleWebKit/420.1 (KHTML, like Gecko) Version/3.0 Mobile/3B48b Safari/419.3',
      'url' => $postbackUrl,
      'header' => true,
    ]));

    $request = $curl->getResult();
    $header_size = ArrayHelper::getValue($curl->getCurlInfo(), 'header_size');

    return strtok($request ? $request : 'HTTP/1.1 404 Not Found', PHP_EOL) . PHP_EOL . Html::encode(substr($request, $header_size));
  }

  /**
   * @param Source $source
   * @param $link
   * @param $ip
   * @return array
   * @throws \mcms\common\helpers\curl\CurlInitException
   * @throws \mcms\common\helpers\curl\CurlMandatoryUrlException
   */
  private function getLabels(Source $source, $link, $ip)
  {
    do {

      $url = parse_url($link);

      $curl = (new Curl([
        'userAgent' => 'Mozilla/5.0 (iPhone; U; CPU like Mac OS X; en) AppleWebKit/420.1 (KHTML, like Gecko) Version/3.0 Mobile/3B48b Safari/419.3',
        'httpHeader' => ["X-Forwarded-For: $ip", "Client-IP: $ip", "Real-IP: $ip"],
        'url' => $link,
        'header' => true,
      ]));

      $request = $curl->getResult();

      $link = false;

      //meta refresh
      if (preg_match('/meta.+?http-equiv\W+?refresh/i', $request)) {
        preg_match('/content.+?url\W+?(.+?)\"/i', $request, $matches);
        $link = ArrayHelper::getValue($matches, 1);
      }

      //window location
      if (preg_match('/(window.location|window.location.href)\s*=\s*("|\')(.+)("|\')/Ui', $request, $matches)) {

        if (preg_match('/https?/i', ArrayHelper::getValue($matches, 3))) {
          $link = ArrayHelper::getValue($matches, 3);
        } else {
          $scheme = ArrayHelper::getValue($url, 'scheme');
          $link = ($scheme ? $scheme . '://' : '') . ArrayHelper::getValue($url, 'host') . '/' . ArrayHelper::getValue($matches, 3);
        }
      }

    } while ($link);

    $url = ArrayHelper::getValue($curl->getCurlInfo(), 'url');

    list($subid1, $subid2, $cid) = $this->parseLabel($source, $url);

    if (!$subid1 && !$subid2 && !$cid) list($subid1, $subid2, $cid) = $this->parseLabel($source, $request);

    return [$subid1, $subid2, $cid];
  }

  /**
   * @param Source $source
   * @param $data
   * @return array
   */
  private function parseLabel(Source $source, $data)
  {
    preg_match('/subid1=([^\&\?\s]*)/uis', $data, $subid1);
    preg_match('/subid2=([^\&\?\s]*)/uis', $data, $subid2);
    preg_match('/' . $source->getCidAttrName() . '=([^\&\?\s]*)/uis', $data, $cid);

    return [ArrayHelper::getValue($subid1, 1), ArrayHelper::getValue($subid2, 1), ArrayHelper::getValue($cid, 1)];
  }
}