<?php

namespace admin\dashboard\models;

use Yii;

/**
 * @inheritdoc
 */
class DashboardGadgetWithFilters extends BaseDashboard
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
        return 'gf';
    }

    /**
     * @inheritdoc
     */
    protected static function getItemsInternal()
    {
        return include(Yii::getAlias('@app/config/dashboard-gadgets.php'));
    }
}
