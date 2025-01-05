<?php

namespace mcms\common\helpers;

use Yii;
use Closure;
use yii\helpers\BaseInflector;

class Menu
{

    private static $notifications;

  static function getItems($notifications = [])
  {
    Yii::beginProfile('getItems', 'mcms\common\helpers\Menu');
    self::$notifications = $notifications;
    $modules = array_keys(Yii::$app->getModules());

    $menu = array_filter(array_map(function ($moduleId) {
      $module = Yii::$app->getModule($moduleId);
      if (!property_exists($module, 'menu')) return null;

      $item = self::checkMenuItemsRecursive($module->menu);

      // Не отображать единственный вложенный пункт
      // Не отображать пустой пункт
      if(!empty($item['items'])) {
        foreach ($item['items'] as $key => &$subItem) {
          if (empty($subItem['label'])) {
            unset($item['items'][$key]);
          }
        }
        $childItems = array_values($item['items']);

        if (count($childItems) === 1 && empty($childItems['items'])) {
          $item['url'] = $childItems[0]['url'];
          unset($item['items']);
        }
      }
      return self::changeVisible($item);
    }, $modules));

    Yii::endProfile('getItems', 'mcms\common\helpers\Menu');
    return $menu;
  }

  private static function changeVisible($menu)
  {
    if (isset($menu['url']) && (!isset($menu['visible']) || $menu['visible'])) {
      return $menu;
    }
    if (empty($menu['items'])) {
      $menu['visible'] = false;
      return $menu;
    }

    $visible = 0;
    foreach ($menu['items'] as &$menuItem) {
      $menuItem = self::changeVisible($menuItem);
      if (isset($menuItem['visible']) && $menuItem['visible'] !== false) {
        $visible = true;
      }
    }
    if (!$visible || empty($menu['label'])) {
      $menu['visible'] = false;
    }
    return $menu;
  }

  private static function checkMenuItemsRecursive($menu, $is_parent = true)
  {
    if (!empty($menu['label'])) {

      $totalCount = array_sum(array_map(function ($event) {
        return ArrayHelper::getValue(self::$notifications, $event, 0);
      }, ArrayHelper::getValue($menu, 'events', [])));
      $iconClass = ArrayHelper::getValue($menu, 'icon');

      $icon = $is_parent ? ($iconClass ? "<i class=\"{$iconClass}\"></i>" : '') . PHP_EOL : '';

      $menu['label'] = $icon . Html::tag('span', Yii::_t($menu['label']), ['class' => 'menu-item-parent']) .
        ($totalCount ? ' ' . Html::tag('span', $totalCount, ['class' => 'badge pull-right inbox-badge' . ($is_parent ? ' margin-right-13' : '')])
          : ''
        );
      $menu['template'] = $totalCount && !$is_parent ? '<a href="{url}" class="nested-with-notify">{label}</a>' : '<a href="{url}">{label}</a>';
    }

    if (!empty($menu['url'])) {
      $canViewUrl = Yii::$app->user->can(BaseInflector::camelize($menu['url'][0]));
      $menu['visible'] = isset($menu['visible']) && $canViewUrl
        ? $menu['visible']
        : $canViewUrl;
      $menu = (isset($menu['visible']) && (($menu['visible'] instanceof Closure && !$menu['visible']()) || !$menu['visible']))
        ? []
        : $menu;
    } elseif (!empty($menu['label']) && !empty($menu['items'])) {
      $canViewUrl = false;
      foreach ($menu['items'] as $i) {
        if (!empty($i['url']) && Yii::$app->user->can(BaseInflector::camelize($i['url'][0]))) {
          $canViewUrl = true;
        }
      }
      if (!$canViewUrl) {
        return null;
      }
      $menu['url'] = '#';
    }

    if (empty($menu['items'])) {
      return $menu;
    }

    $menu['items'] = array_map(function ($menuItem) {
      return static::checkMenuItemsRecursive($menuItem, false);
    }, $menu['items']);

    return $menu;
  }
}
