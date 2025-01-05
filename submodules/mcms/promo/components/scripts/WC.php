<?php

class WC
{
  private static $filename;
  private static $updateInterval = 3600;

  static function redirect($domain, $hash)
  {
    $domain = rtrim($domain, '/');
    self::$filename = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'ips.json';
    //получение ip операторов и данных источника по api
    if (!file_exists(self::$filename) || (time() - filemtime(self::$filename)) > self::$updateInterval) {
      $ipsJson = file_get_contents($domain . "/api/?r=ips");
      $fp = fopen(self::$filename, "w");
      fwrite($fp, $ipsJson);
      fclose($fp);
    } else {
      $ipsJson = file_get_contents(self::$filename);
    }

    $data = json_decode($ipsJson);

    if (!$data || empty($data)) return;

    $operatorIps = $data->operatorIps;

    $operatorId = 0;
    $ip = ip2long(self::getIp());

    foreach($operatorIps as $operator) {
      if ( $ip >= $operator->from_ip && $ip <= $operator->to_ip) {
        $operatorId = $operator->operator_id;
        break;
      }
    }

    if ($operatorId == 0) return;

    echo "<script>(function(i, s, o, g, r, a, m) { i[r] = i[r] || function() {(i[r].q = i[r].q || []).push(arguments) }; ".
      "a = s.createElement(o), m = s.getElementsByTagName(o)[0]; a.async = 1; a.src = g; m.parentNode.insertBefore(a, m) }) ".
      "(window, document, 'script', '" . str_replace(["http://","https://"],"//",$domain) . "/js/embed.js?hash=" . $hash . "', 'wc'); wc('start', '" . $hash . "', {});</script>";

  }

  public static function getIp()
  {
    if (!empty($_SERVER['HTTP_CLIENT_IP']) && empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ip = trim(strtok($_SERVER['HTTP_CLIENT_IP'], ','));
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ip = trim(strtok($_SERVER['HTTP_X_FORWARDED_FOR'], ','));
    } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
      $ip = trim(strtok($_SERVER['HTTP_X_REAL_IP'], ','));
    } else {
      $ip = trim(strtok($_SERVER['REMOTE_ADDR'], ','));
    }
    return $ip;
  }

}
