<?php

namespace admin\dashboard\models;

use Yii;

/**
 * This is the model class for table "dashboard".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $widget_code
 */
class DashboardWidget extends BaseDashboard
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dashboard_widgets';
    }

    /**
     * @inheritdoc
     */
    protected static function getItemsInternal()
    {
        return include(Yii::getAlias('@app/config/dashboard-widgets.php'));
    }

    /**
     * @inheritdoc
     */
    public static function getPrefix()
    {
        return 'w';
    }
}
