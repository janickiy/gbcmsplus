<?php

namespace mcms\promo\components;

use mcms\common\RunnableInterface;
use mcms\promo\components\events\PartnerProgramCleaned;
use mcms\promo\models\Landing;
use mcms\promo\models\PartnerProgram;
use mcms\promo\models\PartnerProgramItem;
use Yii;
use yii\db\Query;

/**
 * Чистка ПП от неактивных лендов и запуск синка партнеров этой ПП
 */
class PartnerProgramClean implements RunnableInterface
{
  /** @var  PartnerProgram */
  private $partnerProgram;

  /**
   * @param PartnerProgram $partnerProgram
   */
  public function __construct(PartnerProgram $partnerProgram)
  {
    $this->partnerProgram = $partnerProgram;
  }


  public function run()
  {
    $landingIds = $this->clean();

    (new PartnerProgramCleaned($this->partnerProgram, $landingIds))->trigger();

    if (!empty($landingIds)) {
      $this->sync();
    }
  }

  /**
   * Очистка ПП
   * @return int[]
   */
  protected function clean()
  {
    // Неактивные ленды, которые будем удалять
    $landingIds = (new Query())
      ->select('ppi.landing_id', 'DISTINCT')
      ->from(['ppi' => PartnerProgramItem::tableName()])
      ->innerJoin(['l' => Landing::tableName()], 'ppi.landing_id = l.id')
      ->andWhere(['<>', 'l.status', Landing::STATUS_ACTIVE])
      ->column(); // todo если будет большой массив, переделать на batchQuery()

    if (empty($landingIds)) {
      return [];
    }

    Yii::$app->db->createCommand()
      ->delete(PartnerProgramItem::tableName(), ['landing_id' => $landingIds])
      ->execute();

    return $landingIds;
  }

  /**
   * Синк партнеров
   */
  protected function sync()
  {
    $userIds = $this->partnerProgram->getAutoSyncUserIds();
    foreach ($userIds as $userId) {
      PartnerProgramSync::runAsync($userId);
    }
  }
}
