<?php

namespace mcms\common\controller;

use admin\components\MainMenu;
use Yii;
use mcms\common\helpers\ArrayHelper;

abstract class AdminBaseController extends AbstractBaseController
{

    protected $breadcrumbs = [];

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        /**
         * TRICKY из-за условия ниже все контроллеры ПП должны наследоваться ТОЛЬКО от @see SiteBaseController
         * Иначе будет редиректить на главную страницу ПП
         * То есть урлы из админки теперь для ПП недоступны
         */
        /** @var \mcms\user\Module $userModule */
        $userModule = Yii::$app->getModule('users');

        if (!Yii::$app->user->can($userModule::PERMISSION_CAN_VIEW_ADMIN_CABINET)) {
            $this->redirect($userModule->urlCabinet);
        }

        $this->initHeaders();

        return parent::beforeAction($action);
    }

    /**
     * @param $view
     * @param array $params
     * @return string
     */
    public function render($view = NULL, array $params = [])
    {
        $this->getView()->params['breadcrumbs'] = $this->getRenderBreadcrumbs();
        $params['breadcrumbs'] = $this->getRenderBreadcrumbs();

        $this->controllerTitle = $this->getView()->title ?: $this->controllerTitle;
        $this->getView()->title = $this->controllerTitle;
        $params['title'] = $this->controllerTitle;

        // TRICKY Беру куки напрямую из глобальной переменной, потому что они были установлены через js
        $isMinifiedMenu = isset($_COOKIE['minifiedMenu']) && $_COOKIE['minifiedMenu'] === 'true';
        $isHiddenMenu = isset($_COOKIE['hiddenMenu']) && $_COOKIE['hiddenMenu'] === 'true';
        $this->getView()->params['bodyClass'] = '';
        if ($isMinifiedMenu === true) {
            $this->getView()->params['bodyClass'] = 'minified';
        }
        if ($isHiddenMenu === true) {
            $this->getView()->params['bodyClass'] = 'hidden-menu';
        }

        return parent::render($view, $params);
    }

    public function getRenderBreadcrumbs()
    {
        if (!is_array($this->breadcrumbs)) return null;
        if (empty($this->breadcrumbs)) return [];

        $breadcrumbs = $this->breadcrumbs;
        $last = array_pop($breadcrumbs);

        if (is_array($last) && ArrayHelper::getValue($last, 'url', null)) unset($last['url']);

        $breadcrumbs[] = $last;

        return $breadcrumbs;
    }

    /**
     * @param $label
     * @param array $url
     * @param $translate
     * @return void}
     */
    public function setBreadcrumb($label, array $url = [], $translate = true)
    {
        $label = $translate ? Yii::_t($label) : $label;
        if (empty($url)) {
            $this->breadcrumbs[] = $label;
            return;
        }

        $this->breadcrumbs[] = [
            'label' => $label,
            'url' => $url
        ];
    }

    /**
     * Заголовок 1 уровня - это активный пункт меню 1 уровня
     * Заголовок 2 уровня - это активный пункт меню последнего уровня
     *
     * Можно переопределить во вьюхах например так:
     * $this->beginBlock('headerData');
     *  echo "<h1 class=\"page-title\">Кастомный заголовок</h1>";
     * $this->endBlock();
     *
     * $this->view->beginBlock('subHeader');
     *  echo 'Кастомный подзаголовок'
     * $this->view->endBlock();
     * TRICKY нам понадобится виджет меню, но сразу рендерить его не будем
     * (он будет рендериться как и раньше в лейауте через Yii::$app->mainMenu->render())
     * Сохраняем меню через сервис-локатор, ищем активные элементы меню и присваиваем их в качестве заголовков
     */
    private function initHeaders()
    {
        // tricky: setComponents вызывается здесь, а не в бутстрапе, потому что на этапе бутстрапа нет нужных данных
        Yii::$app->setComponents([
            'mainMenu' => \admin\components\MainMenu::class
        ]);

        /** @var MainMenu $menu */
        $menu = Yii::$app->mainMenu;

        $items = $menu->getActiveItems();

        $first = reset($items);
        $last = end($items);

        if ($first) {
            $this->view->beginBlock('headerData');
            echo "<h1 class=\"page-title\">{$first->label}</h1>";
            $this->view->endBlock();
        }

        if ($last) {
            $this->view->beginBlock('subHeader');
            // Если глобальный заголовок совпадает с подзаголовком, выводим дефолтный подзаголовок
            $label = ($first && $first->label == $last->label)
                ? Yii::_t(MainMenu::DEFAULT_SUBHEADER)
                : $last->label;

            echo $label;
            $this->view->endBlock();
        }
    }
}