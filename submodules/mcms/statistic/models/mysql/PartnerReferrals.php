<?php


namespace mcms\statistic\models\mysql;

/**
 * Статистика реферралов по определенному партнеру
 */
class PartnerReferrals extends BaseReferrals
{
  const STATISTIC_NAME = 'partner_referrals';

  /**
   * @inheritdoc
   */
  public function getQuery(array $select = [])
  {
    $select['referral_id'] = 'st.referral_id';

    return parent::getQueryInternal('st.referral_id', 'st.referral_id', $select);
  }
}