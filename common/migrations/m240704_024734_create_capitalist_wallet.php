<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
 */
class m240704_024734_create_capitalist_wallet extends Migration
{
    use PermissionTrait;

    /**
     */
    public function up()
    {
        $this->insert('wallets', [
            'code' => 'capitalist',
            'name' => serialize(['ru' => 'Капиталист', 'en' => 'Capitalist']),
            'info' => '',
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
            'is_active' => 0,
            'is_check_file_required' => 0,
            'is_check_file_show' => 0,
            'is_invoice_file_required' => 0,
            'is_invoice_file_show' => 0,
            'is_mgmp_payments_enabled' => 1,
            'is_rub' => 1,
            'is_usd' => 1,
            'is_eur' => 1,
        ]);
        $this->insert('payment_systems_api', [
            'name' => 'Capitalist rub',
            'code' => 'capitalist',
            'currency' => 'rub',
        ]);

        $this->insert('payment_systems_api', [
            'name' => 'Capitalist usd',
            'code' => 'capitalist',
            'currency' => 'usd',
        ]);

        $this->insert('payment_systems_api', [
            'name' => 'Capitalist eur',
            'code' => 'capitalist',
            'currency' => 'eur',
        ]);
    }

    /**
     */
    public function down()
    {
        $this->delete('wallets', ['code' => 'capitalist']);
        $this->delete('payment_systems_api', ['code' => 'capitalist']);
    }
}
