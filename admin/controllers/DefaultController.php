<?php

namespace admin\controllers;

use admin\dashboard\common\base\BaseBlock;
use admin\dashboard\common\DasboardRequest;
use admin\dashboard\common\DasboardResponse;
use admin\dashboard\gadgets\base\BaseGadget;
use admin\dashboard\models\DashboardGadget;
use admin\dashboard\models\DashboardGadgetWithFilters;
use admin\dashboard\models\DashboardWidget;
use admin\dashboard\widgets\base\BaseWidget;
use mcms\common\controller\AdminBaseController;
use mcms\modmanager\components\ModulesGroupedSettings;
use mcms\promo\models\Country;
use mcms\statistic\components\CheckPermissions;
use Yii;
use yii\helpers\Json;

class DefaultController extends AdminBaseController
{
    public $layout = '@app/views/layouts/main';

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if ($action->id == 'eval') {
            if (!YII_ENV_DEV || !YII_DEBUG) {
                return false;
            }
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $userId = Yii::$app->user->id;
        $this->view->title = Yii::_t('app.dashboard.dashboard');

        DashboardWidget::initItems($userId);
        DashboardGadget::initItems($userId);

        if (Yii::$app->request->isPost) {
            $dashboardItems = Yii::$app->request->post('dashboard-items');
            DashboardWidget::saveUserItems($userId, DashboardWidget::filterByPrefix($dashboardItems));
            DashboardGadget::saveUserItems($userId, DashboardGadget::filterByPrefix($dashboardItems));
        }
        $userWidgets = DashboardWidget::getItems($userId);
        $widgetsSelect = DashboardWidget::getSelectItems($userId, true);
        $widgetsSelected = DashboardWidget::getSelectedItems($userId, true);

        $userGadgets = DashboardGadget::getItems($userId);
        $gadgetsSelect = DashboardGadget::getSelectItems($userId, true);
        $gadgetsSelected = DashboardGadget::getSelectedItems($userId, true);

        $userGadgetsWithFilters = DashboardGadgetWithFilters::getItems($userId);
        $gadgetsSelectWithFilters = DashboardGadgetWithFilters::getSelectItems($userId, true);
        $gadgetsSelectedWithFilters = DashboardGadgetWithFilters::getSelectedItems($userId, true);

        $countries = array_map(function ($item) {
            return [
                'name' => $item['name'],
                'code' => strtolower($item['code']),
            ];
        }, Country::getActiveWithActiveLandings());

        $currencies = array_map(function ($item) {
            return strtoupper($item);
        }, Yii::$app->getModule('promo')
            ->api('mainCurrencies')
            ->setResultTypeMap()
            ->setMapParams(['code', 'code'])
            ->getResult());

        $filters = BaseWidget::getFiltersHandler($userId)->getFilters();

        $permissionChecker = new CheckPermissions([
            'viewerId' => Yii::$app->user->id,
        ]);

        $canViewRevenue =
            $permissionChecker->canViewAdminProfit() ||
            $permissionChecker->canViewResellerProfit() ||
            $permissionChecker->canViewPartnerProfit();

        return $this->render('index', [
            'userWidgets' => $userWidgets,
            'widgetsSelect' => $widgetsSelect,
            'widgetsSelected' => $widgetsSelected,

            'userGadgets' => $userGadgets,
            'gadgetsSelect' => $gadgetsSelect,
            'gadgetsSelected' => $gadgetsSelected,

            'userGadgetsWithFilters' => $userGadgetsWithFilters,
            'gadgetsSelectWithFilters' => $gadgetsSelectWithFilters,
            'gadgetsSelectedWithFilters' => $gadgetsSelectedWithFilters,

            'countries' => $countries,
            'filters' => Json::encode($filters),
            'currencies' => $currencies,
            'canFilterByCurrency' => $permissionChecker->canFilterByCurrency() && $canViewRevenue,
        ]);
    }

    /**
     * Экшн для получения данных для элементов дашборда
     * @return array
     */
    public function actionGetDashboardData()
    {
        /** @var DasboardRequest $request содержит объект с полученными данными дашборда */
        $request = new DasboardRequest(Yii::$app->request->post());

        /** Применение сохраненных фильтров для текущего юзера */
        BaseBlock::handleFilters($request);

        $response = new DasboardResponse();
        $userId = Yii::$app->user->id;

        /** Подготовка виджетов */
        $userWidgets = DashboardWidget::getItems($userId);
        $widgetsSelected = DashboardWidget::getSelectedItems($userId);

        /** @var array $requestWidgets список запрошенных виджетов */
        $requestWidgets = $request->getWidgets();

        foreach ($requestWidgets as $widgetName => $params) {
            /** Если запрошенный виджет есть среди выбранных для отображения */
            if (in_array($widgetName, $widgetsSelected)) {
                $widgetClass = $userWidgets[$widgetName]['class'];

                /** Применяем общие фильтры дашборда к текущему виджету */
                $params = array_merge($params, $request->getFilters());

                /** @var BaseWidget $widget */
                $widget = $widgetClass::getInstance($params);

                /** Если есть доступ к виджету - добавляем его в DashboardResponse */
                $widget->hasAccess() && $response->setWidget($widgetName, $widget->getFrontData());
            }
        }

        /** Подготовка гаджетов */
        $userGadgetsWithFilters = DashboardGadgetWithFilters::getItems($userId);
        $gadgetsSelectedWithFilters = DashboardGadgetWithFilters::getSelectedItems($userId);

        /** @var array $requestGadgets список запрошенных гаджетов */
        $requestGadgets = $request->getGadgets();

        foreach ($requestGadgets as $gadgetName => $params) {
            /** Если запрошенный гаджет есть среди выбранных для отображения */
            if (in_array($gadgetName, $gadgetsSelectedWithFilters)) {
                $gadgetClass = $userGadgetsWithFilters[$gadgetName]['class'];

                /** Применяем общие фильтры дашборда к текущему гаджету */
                $params = array_merge($params, $request->getFilters());

                /** @var BaseGadget $gadget */
                $gadget = $gadgetClass::getInstance($params);

                /** Если есть доступ к гаджету - добавляем его в DashboardResponse */
                $gadget->hasAccess() && $response->setGadget($gadgetName, $gadget->getFrontData());
            }
        }

        return $response->send();
    }

    public function actionEval()
    {

        if (Yii::$app->request->isPost) {
            ob_start();
            eval(Yii::$app->request->post('eval_code', ''));
            $res = ob_get_clean();
            return '<pre>' . $res . '</pre>';
        }
        return $this->renderPartial('eval');
    }

    /**
     * Выводит сгруппированные настройки модулей
     * @return string
     */
    public function actionSettings()
    {
        return $this->redirect(['settings/index']);
        $title = Yii::_t('modmanager.main.settings');
        $this->getView()->title = $title;

        $this->view->beginBlock('headerData');
        echo "<h1 class=\"page-title\">$title</h1>";
        $this->view->endBlock();

        //Выбираем настройки и модели модулей
        $modulesSettingsAndDynamicModels = ModulesGroupedSettings::getModulesSettingsAndDynamicModels();

        //Группируем настройки
        $groupedFormSettings = ModulesGroupedSettings::getGroupedSettings($modulesSettingsAndDynamicModels['modulesSettings']);

        if (Yii::$app->request->isPost) {
            $errors = [];
            foreach ($modulesSettingsAndDynamicModels['moduleDynamicModels'] as $key => $dynamicModel) {
                //Загружаем данные в динамическую модель
                $dynamicModel->load(Yii::$app->request->post());
                //Валидируем ее
                $dynamicModel->validate();
                //Берем настройки из динамической модели
                $modulesSettingsAndDynamicModels['moduleSettingsRepository'][$key]->import($dynamicModel);
                $model = $modulesSettingsAndDynamicModels['moduleModels'][$key];
                //Записываем настройки в модель
                $model->setSettings($modulesSettingsAndDynamicModels['moduleSettingsRepository'][$key]);
                if ($model->save()) {
                    $errors[] = false;
                } else {
                    $errors[] = true;
                }
            }
            if (in_array(true, $errors)) {
                $this->flashFail('app.common.Save failed');
            } else {
                $this->flashSuccess('app.common.Saved successfully');
            }
        }

        return $this->render('settings', [
            'moduleDynamicModels' => $modulesSettingsAndDynamicModels['moduleDynamicModels'],
            'groupedFormSettings' => $groupedFormSettings,
        ]);
    }
}