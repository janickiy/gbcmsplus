<?php

namespace mcms\common\output;


/**
 * Interface OutputInterface
 * @package mcms\common\output
 */
interface OutputInterface
{
  const BREAK_AFTER = 'breakAfter';
  const BREAK_BEFORE = 'breakBefore';

  /**
   * @param $message
   * @param array $params
   * @return
   */
  public function log($message, $params = []);
}