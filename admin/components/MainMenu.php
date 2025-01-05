<?php

namespace admin\components;

use mcms\common\helpers\SmartMenuHelper;
use rgk\theme\smartadmin\widgets\menu\MenuItem;
use rgk\theme\smartadmin\widgets\menu\SmartAdminMenu;
use Yii;
use yii\base\Object;

/**
 * Главное меню админки. Используется как Yii::$app->mainMenu
 * Сделано для того, чтобы можно было в любом месте приложения получить доступ к главному меню.
 */
class MainMenu extends Object
{
    /** Текст подзаголовка, на случай совпадения с глобальным пунктом меню */
    const DEFAULT_SUBHEADER = 'app.common.list';

    /** @var  SmartAdminMenu */
    private $widget;

    /** @var  MenuItem[] активные пункты меню */
    private $activeItems;

    public function init()
    {
        parent::init();

        $this->widget = new SmartAdminMenu([
            // проверка isset потому что в ПП нет меню и табов. И получается Notice при действии logout-by-user
            'items' => isset(Yii::$app->params['menu']) ? SmartMenuHelper::format(Yii::$app->params['menu']) : [],
            'tabs' => isset(Yii::$app->params['tabs']) ? Yii::$app->params['tabs'] : [],
        ]);
    }

    public function render()
    {
        $this->widget->run();
    }

    /**
     * Получаем массив активных элементов в порядке от родительского к дочерним
     * @return MenuItem[]
     */
    public function getActiveItems()
    {
        if (!is_null($this->activeItems)) return (array)$this->activeItems;

        $this->activeItems = [];
        if ($this->widget->itemsObj) {
            $this->fetchActiveItems($this->widget->itemsObj);
        }

        return $this->activeItems;
    }

    /**
     * Достаем активные пункты меню и кладем в $this->activeItems
     * @param MenuItem[] $items
     */
    protected function fetchActiveItems($items)
    {
        /**
         * TRICKY проблема этой логики в том, что в классе виджета пункты меню которые родительские
         * всегда возвращают isActive()=false. Только самые дочерние isActive()=true
         * Поэтому проверяем методом hasActiveChilds()
         */
        foreach ($items as $item) {
            if (!$item->hasChilds() && $item->isActive()) {
                $this->activeItems[] = $item;
                continue;
            }

            if ($item->hasChilds() && $item->hasActiveChilds()) {
                $this->activeItems[] = $item;
                $this->fetchActiveItems($item->itemsObj);
            }
        }
    }


}