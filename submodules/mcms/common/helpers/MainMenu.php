<?php

namespace mcms\common\helpers;

use yii\widgets\Menu;
use Yii;

class MainMenu extends Menu
{
    protected function isItemActive($item)
    {
        if (!isset($item['url']) || !is_array($item['url']) || !isset($item['url'][0])) {
            return false;
        }

        $route = Yii::getAlias($item['url'][0]);
        if ($route[0] !== '/' && Yii::$app->controller) {
            $route = Yii::$app->controller->module->getUniqueId() . '/' . $route;
        }
        // Если у меню стоит флаг isActionCheck проверка идет по action, иначе по контроллеру
        $isActionCheck = ArrayHelper::getValue($item, 'isActionCheck');
        if ($isActionCheck && ltrim($route, '/') !== $this->route) {
            return false;
        }
        if (
            !$isActionCheck &&
            substr(ltrim($route, '/'), 0, strrpos(ltrim($route, '/'), '/')) !==
            substr($this->route, 0, strrpos($this->route, '/'))
        ) {
            return false;
        }

        unset($item['url']['#']);
        if (count($item['url']) > 1) {
            $params = $item['url'];
            unset($params[0]);
            foreach ($params as $name => $value) {
                if ($value !== null && (!isset($this->params[$name]) || $this->params[$name] != $value)) {
                    return false;
                }
            }
        }
        return true;
    }
}
