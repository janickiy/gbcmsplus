<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m240806_002508_create_usdt_wallet extends Migration
{
    use PermissionTrait;

    /**
     */
    public function up()
    {
        $this->insert('wallets', [
            'code' => 'usdt',
            'name' => serialize(['ru' => 'USDT', 'en' => 'USDT']),
            'info' => serialize(['ru' => 'USDT wallet (TRC20)', 'en' => 'USDT wallet (TRC20)']),
            'profit_percent' => 0,
            'rub_min_payout_sum' => 1,
            'eur_min_payout_sum' => 1,
            'usd_min_payout_sum' => 1,
            'rub_max_payout_sum' => null,
            'rub_payout_limit_daily' => null,
            'rub_payout_limit_monthly' => null,
            'usd_max_payout_sum' => null,
            'usd_payout_limit_daily' => null,
            'usd_payout_limit_monthly' => null,
            'eur_max_payout_sum' => null,
            'eur_payout_limit_daily' => null,
            'eur_payout_limit_monthly' => null,
            'rub_sender_api_id' => null,
            'usd_sender_api_id' => null,
            'eur_sender_api_id' => null,
            'is_active' => 1,
            'is_check_file_required' => 0,
            'is_check_file_show' => 0,
            'is_invoice_file_required' => 0,
            'is_invoice_file_show' => 0,
            'is_mgmp_payments_enabled' => 1,
            'is_rub' => 0,
            'is_usd' => 1,
            'is_eur' => 0,
        ]);

        $this->insert('payment_systems_api', [
            'name' => 'USDT usd',
            'code' => 'usdt',
            'currency' => 'usd',
        ]);
    }

    /**
     */
    public function down()
    {
        $this->delete('wallets', ['code' => 'usdt']);
        $this->delete('payment_systems_api', ['code' => 'usdt']);
    }
}
