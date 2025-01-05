<?php

namespace admin\dashboard\models;

use Yii;

/**
 * @inheritdoc
 */
class DashboardGadget extends BaseDashboard
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dashboard_gadgets';
    }

    /**
     * @inheritdoc
     */
    public static function getPrefix()
    {
        return 'g';
    }

    /**
     * @inheritdoc
     */
    protected static function getItemsInternal()
    {
        return include(Yii::getAlias('@app/config/dashboard-gadgets.php'));
    }
}
