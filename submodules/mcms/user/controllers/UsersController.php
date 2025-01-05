<?php

namespace mcms\user\controllers;

use mcms\common\actions\GetModalAction;
use mcms\common\controller\AdminBaseController;
use mcms\common\behavior\ModelFetcher;
use mcms\common\rbac\AuthItemsManager;
use mcms\common\web\AjaxResponse;
use mcms\common\widget\UserSelect2;
use mcms\promo\components\api\UserPromoSettings;
use mcms\promo\components\widgets\UserFakeWidget;
use mcms\statistic\components\api\LabelStatisticEnable;
use mcms\user\components\events\EventRegistered;
use mcms\user\components\events\EventRegisteredHandActivation;
use mcms\user\components\storage\User as UserStorage;
use mcms\user\models\ProfileForm;
use mcms\user\models\search\UserContactsSearch;
use mcms\user\models\UserContact;
use mcms\user\models\UserForm;
use mcms\user\models\User;
use mcms\user\models\search\User as UserSearch;
use mcms\user\Module;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\ForbiddenHttpException;
use yii\web\IdentityInterface;
use yii\web\Response;
use mcms\common\SystemLanguage;
use yii\widgets\ActiveForm;

/**
 * Class UsersController
 * @package mcms\user\controllers
 * @method \mcms\user\models\User fetch($id)
 */
class UsersController extends AdminBaseController
{
    public $controllerTitle;

    private $userStorage;

    public $layout = '@app/views/layouts/main';

    const EDITABLE_COLUMN_EMPTY_MESSAGE = '';

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => ModelFetcher::class,
                'defaultAction' => $this->defaultAction,
                'storage' => $this->userStorage,
                'controller' => $this
            ]
        ];
    }

    /**
     * @param string $id the ID of this controller.
     * @param Module $module the module that this controller belongs to.
     * @param array $config name-value pairs that will be used to initialize the object properties.
     * @param UserStorage $userStorage
     */
    public function __construct($id, $module, $config = [], UserStorage $userStorage)
    {
        parent::__construct($id, $module, $config);
        $this->userStorage = $userStorage;
        $this->getView()->title = $module->name;
    }

    public function actionProfile()
    {
        $userModule = Yii::$app->getModule('users');
        $user = $userModule->api('getOneUser', [
            'user_id' => Yii::$app->user->id,
        ])->getResult();


        /** @var UserPromoSettings $userPromoSettingsApi */
        $userPromoSettingsApi = Yii::$app->getModule('promo')->api('userPromoSettings');
        $form = new ProfileForm($user);
        $form->grid_page_size = $userPromoSettingsApi->getGridPageSize(Yii::$app->user->id);

        if ($form->load(Yii::$app->request->post()) || Model::loadMultiple($user->activeContacts, Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $validateErrors = array_merge(
                ActiveForm::validate($form),
                ActiveForm::validateMultiple($user->activeContacts)
            );

            if ($validateErrors) {
                return $validateErrors;
            }

            // Валидация
            if (Yii::$app->request->post("submit")) {
                // Сохранение
                $result = $userModule->api('editUser', [
                    'post_data' => array_merge($form->getAttributes(), ['contactModels' => $user->activeContacts]),
                    'user_id' => Yii::$app->user->id,
                ])->getResult();

                $userPromoSettingsApi->saveGridPageSize(
                    Yii::$app->user->id,
                    $form->grid_page_size
                );

                $systemLanguage = new SystemLanguage();
                $systemLanguage->setLang($form->language);
                Yii::$app->language = $form->language;

                return AjaxResponse::set($result);
            }
        }

        if (!$form->language) {
            $form->language = Yii::$app->language;
        }

        return $this->renderAjax('profile', [
            'user' => $user,
            'model' => $form,
            'languagesArray' => SystemLanguage::getLanguangesDropDownArray()
        ]);
    }

    /**
     * @param $id
     * @return array|bool
     */
    public function actionRemoveContact($id)
    {
        $contact = UserContact::findOne(['id' => $id, 'user_id' => Yii::$app->getUser()->id]);
        $result = $contact ? $contact->setDeleted()->save(false) : false;

        return AjaxResponse::set($result);
    }

    /**
     * @return array|bool
     */
    public function actionCreateContact()
    {
        $contact = new UserContact([
            'user_id' => Yii::$app->getUser()->id
        ]);

        return AjaxResponse::set($contact->save(false));
    }

    /**
     * @return string
     */
    public function actionList()
    {
        $model = new UserSearch([
            'canViewUserWithoutManager' => true,
            'orderByFieldStatus' => [
                UserSearch::STATUS_ACTIVATION_WAIT_HAND,
            ]
        ]);
        $model->ignoreIds = [Yii::$app->user->id];
        $this->getView()->title = Yii::_t('controllers.user_list');

        // id виджета экспорта
        $exportWidgetId = 'exportWidget';

        $dataProvider = $model->search(Yii::$app->request->queryParams);
        if (!empty($_POST['exportFull_' . $exportWidgetId])) {
            $dataProvider->setPagination(['pageSize' => Yii::$app->getModule('users')->getExportLimit()]);
        }

        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'model' => $model,
            'statuses' => $model->filterStatuses(),
            'roles' => $model->filterRoles(),
            'online' => $model->filterOnline(),
            'exportWidgetId' => $exportWidgetId,
        ]);
    }

    /**
     * @param $id
     * @return string
     */
    public function actionView($id = null)
    {
        $userId = $id ? $id : (Yii::$app->request->isAjax ? Yii::$app->request->post('expandRowKey') : Yii::$app->request->get('id'));
        /** @var \mcms\user\models\User $model */
        $model = $this->fetch($userId);

        $canViewUser = $this->canViewUser($userId);
        if ($canViewUser !== true) return $canViewUser;

        $this->setNotificationAsViewed(EventRegistered::class);
        $model->scenario = User::SCENARIO_VIEW;
        $model->getAdditionalFieldsModel();
        $this->getView()->title = Yii::_t('controllers.view');

        $modulePayments = Yii::$app->getModule('payments');

        $userContactsSearchModel = new UserContactsSearch(['user_id' => $userId]);
        $userContactsDataProvider = $userContactsSearchModel->search([]);

        $roles = Yii::$app->authManager->getRolesByUser($model->id);
        $promoModule = Yii::$app->getModule('promo');
        $promoPersonalProfit = $promoModule->api('personalProfitForm', [
            'userId' => $userId,
            'renderCreateButton' => false,
            'renderActions' => false,
            'enableSort' => false,
            'enableFilters' => false,
            'emptyHeader' => true,
        ])->getResult();

        $viewParams = [
            'model' => $model,
            'balance' => $modulePayments->api('userBalance', ['userId' => $model->id])->getResult(),
            'paymentSettings' => $modulePayments->api('userSettingsData', ['userId' => $model->id])->getResult(),
            'summaryToPayment' => Yii::$app->getModule('payments')->api('userBalance', ['userId' => $model->id])->getMain(),
            'isPartner' => isset($roles[Module::PARTNER_ROLE]),
            'canUserHaveBalance' => $modulePayments::canUserHaveBalance($model->id),
            'promoPersonalProfit' => $promoPersonalProfit,
            'userContactsSearchModel' => $userContactsSearchModel,
            'userContactsDataProvider' => $userContactsDataProvider,
        ];

        return Yii::$app->request->isAjax ? $this->renderAjax('view', $viewParams) : $this->render('view', $viewParams);
    }

    public function actionEditComment($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->fetch($id);
        if (!$model) {
            return ['output' => self::EDITABLE_COLUMN_EMPTY_MESSAGE, 'message' => Yii::_t('users.forms.cant_find_user')];
        }

        $model->comment = Yii::$app->request->post('comment');
        if ($model->save()) {
            return ['output' => $model->comment, 'message' => self::EDITABLE_COLUMN_EMPTY_MESSAGE];
        }

        return ['output' => $model->comment, 'message' => Yii::_t('users.forms.cant_save_comment')];
    }

    public function actionEditStatus($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = $this->fetch($id);
        if (!$model) {
            return ['output' => self::EDITABLE_COLUMN_EMPTY_MESSAGE, 'message' => Yii::_t('users.forms.cant_find_user')];
        }

        $model->status = Yii::$app->request->post('status');
        $userForm = new UserForm($model);
        if ($userForm->validate()) {
            $userForm->createUser();
            if (!$model->isModerationByHand()) {
                $this->setNotificationAsViewed(EventRegisteredHandActivation::class, $model->id);
            }
            return ['output' => $model->getNamedStatus(), 'message' => self::EDITABLE_COLUMN_EMPTY_MESSAGE];
        }

        return ['output' => $model->getNamedStatus(), 'message' => Yii::_t('users.forms.cant_save_status')];
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     */
    public function actionUpdate($id)
    {
        $user = $this->fetch($id);
        $permission = (new AuthItemsManager)->getRolePermissionName($user->getRole()->one()->name);
        $canEditRole = Yii::$app->user->can($permission);

        $canViewUser = $this->canViewUser($id);
        if ($canViewUser !== true) return $canViewUser;
        if (!$canEditRole) {
            $this->flashFail('app.common.access_denied', ['permission' => $permission]);
            return $this->redirect([$this->defaultAction]);
        }

        $this->getView()->title = Yii::_t('controllers.edit_user');
        return $this->handleForm(new UserForm($user));
    }

    private function handleForm(UserForm $createUserForm, $create = false)
    {
        /** @var LabelStatisticEnable $labelStatisticEnableApi */
        $labelStatisticEnableApi = Yii::$app->getModule('statistic')->api('labelStatisticEnable');
        /** @var UserPromoSettings $userPromoSettingsApi */
        $userPromoSettingsApi = Yii::$app->getModule('promo')->api('userPromoSettings');

        $userId = $createUserForm->getUser()->id;
        $isPartner = Yii::$app->getModule('users')->api('rolesByUserId', ['userId' => $userId])->isPartner();

        $isDisableBuyout = $userPromoSettingsApi->getIsDisableBuyout($userId, false);

        if (Yii::$app->request->isPost
            && $createUserForm->load(Yii::$app->request->post())
            && $createUserForm->validate()
        ) {
            $createUserForm->createUser();
            if (!$createUserForm->getUser()->isModerationByHand()) {
                $this->setNotificationAsViewed(
                    EventRegisteredHandActivation::class,
                    $userId
                );
            }

            // Сохраняем флаг "Статистика по меткам включена"
            if ($labelStatisticEnableApi->getIsEnabledGlobally()) {
                $labelStatisticEnableApi->saveUserFlag($userId, $createUserForm->is_label_stat_enabled);
            }

            if ($isPartner) {
                $userPromoSettingsApi->saveIsAllowedSourceRedirect(
                    $userId,
                    $createUserForm->is_allowed_source_redirect
                );
                if ($isDisableBuyout != $createUserForm->is_disable_buyout) {
                    $userPromoSettingsApi->saveIsDisableBuyout(
                        $userId,
                        $createUserForm->is_disable_buyout
                    );
                }
            }

            // Сохраняем флаг "Оптимизировать конверт"
            if ($userPromoSettingsApi->getIsUserCanEditFakeFlag()) {
                $userPromoSettingsApi->saveIsFakeRevshareEnabled(
                    $userId,
                    $createUserForm->is_fake_revshare_enabled
                );
            }

            if ($isPartner) {
                $userPromoSettingsApi->saveGlobalPostbackUrl(
                    $userId,
                    $createUserForm->postback_url
                );
                $userPromoSettingsApi->saveGlobalComplainsPostbackUrl(
                    $userId,
                    $createUserForm->complains_postback_url
                );
            }

            $this->flashSuccess('app.common.Saved successfully');
            return $this->redirect(['users/update', 'id' => $createUserForm->getUser()->id]);
        }

        $createUserForm->is_label_stat_enabled = $labelStatisticEnableApi->getIsEnabledByUser($userId);

        $createUserForm->is_allowed_source_redirect = $userPromoSettingsApi->getIsAllowedSourceRedirect($userId);

        $createUserForm->is_disable_buyout = $isDisableBuyout;

        $createUserForm->is_fake_revshare_enabled = $userPromoSettingsApi->getUserHasEnabledFakeRevshare($userId);

        return $this->render('form', [
            'model' => $createUserForm,
            'fakeSettingsWidget' => UserFakeWidget::widget(['userId' => $userId]),
            'paymentSettings' => Yii::$app->getModule('payments')->api('userSettings', ['userId' => $userId])->getResult(),
            'paymentSettingsData' => Yii::$app->getModule('payments')->api('userSettingsData', ['userId' => $userId])->getResult(),
            'promoPersonalProfit' => $create ? null : $this->promoPersonalProfit($createUserForm->getUser()),
            'promoTrafficBlock' => $create ? null : $this->promoTrafficBlock($createUserForm->getUser()),
            'promoRebillCorrect' => $create ? null : $this->promoRebillCorrect($createUserForm->getUser()),
            'canUserDelegateResellerHidePromo' => $createUserForm->canUserDelegateResellerHidePromo(),
            'labelStatisticEnableApi' => $labelStatisticEnableApi,
            'userPromoSettingsApi' => $userPromoSettingsApi,
            'isPartner' => $isPartner,
            'notificationModules' => UserForm::getModulesDropDown(),
            'userContacts' => $this->renderUserContacts($createUserForm->user),
        ]);
    }

    public function actionCreate()
    {
        $this->getView()->title = Yii::_t('controllers.create');
        $createUserForm = new UserForm(new User());
        // Делаем статус по-умолчанию активным
        $createUserForm->status = User::STATUS_ACTIVE;
        /** @var LabelStatisticEnable $labelStatisticEnableApi */
        $labelStatisticEnableApi = Yii::$app->getModule('statistic')->api('labelStatisticEnable');
        /** @var UserPromoSettings $userPromoSettingsApi */
        $userPromoSettingsApi = Yii::$app->getModule('promo')->api('userPromoSettings');

        if ($createUserForm->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $createUserForm->validate();

            if (!Yii::$app->request->post("submit")) {
                return ActiveForm::validate($createUserForm);
            }
            $createUserForm->createUser();
            if (!$createUserForm->getUser()->isModerationByHand()) {
                $this->setNotificationAsViewed(
                    EventRegisteredHandActivation::class,
                    $createUserForm->getUser()->id
                );
            }

            // Сохраняем флаг "Статистика по меткам включена"
            if ($labelStatisticEnableApi->getIsEnabledGlobally()) {
                $labelStatisticEnableApi->saveUserFlag($createUserForm->getUser()->id, $createUserForm->is_label_stat_enabled);
            }

            return true;
        }

        $userId = $createUserForm->getUser()->id;

        $createUserForm->is_label_stat_enabled = $labelStatisticEnableApi->getIsEnabledByUser($userId);

        return $this->renderAjax('form_modal', [
            'model' => $createUserForm,
            'paymentSettings' => Yii::$app->getModule('payments')->api('userSettings', ['userId' => $userId])->getResult(),
            'paymentSettingsData' => Yii::$app->getModule('payments')->api('userSettingsData', ['userId' => $userId])->getResult(),
            'promoPersonalProfit' => null,
            'promoRebillCorrect' => null,
            'canUserDelegateResellerHidePromo' => $createUserForm->canUserDelegateResellerHidePromo(),
            'labelStatisticEnableApi' => $labelStatisticEnableApi,
            'userPromoSettingsApi' => $userPromoSettingsApi,
        ]);
    }

    private function promoRebillCorrect(User $user)
    {
        if (!($modulePromo = Yii::$app->getModule('promo'))) return null;
        return $modulePromo->api('rebillConditionsForm', ['partnerId' => $user->id])->getResult();
    }

    /**
     * @param User $user
     * @return null|string
     */
    private function promoPersonalProfit(User $user)
    {
        if (!($modulePromo = Yii::$app->getModule('promo'))) return null;
        return $modulePromo->api('personalProfitForm', ['userId' => $user->id])->getResult();
    }

    /**
     * @param User $user
     * @return null|string
     */
    private function promoTrafficBlock(User $user)
    {
        if (!($modulePromo = Yii::$app->getModule('promo'))) return null;
        return $modulePromo->api('trafficBlockForm', ['userId' => $user->id, 'showAddButton' => true])->getResult();
    }

    public function actionFindUser()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $data = Yii::$app->request->get('data');
        $q = Yii::$app->request->get('q');
        $roles = ArrayHelper::getValue($data, 'roles');
        $format = ArrayHelper::getValue($data, 'format', UserSelect2::USER_ROW_FORMAT);
        $ignoreIds = ArrayHelper::getValue($data, 'ignoreIds', []);
        $sourceIds = ArrayHelper::getValue(Yii::$app->request->get(), 'source_ids', []);
        $streamIds = ArrayHelper::getValue(Yii::$app->request->get(), 'stream_ids', []);
        $skipCurrentUser = ArrayHelper::getValue($data, 'skipCurrentUser', false);

        $params = [
            'id' => $q,
            'namesRoles' => $roles,
            'ignoreIds' => $ignoreIds,
        ];
        if (ArrayHelper::getValue($data, 'isActiveUsers', false)) {
            $params['status'] = User::STATUS_ACTIVE;
        }

        $idDataProviderModels = [];
        $queryDataProviderModels = [];
        $idDataProviderModelsCount = 0;

        if ($params['id']) {
            // Поиск по ид
            $userIdSearch = new UserSearch();

            /** @var ActiveDataProvider $idDataProvider */
            $idDataProvider = $userIdSearch->search([
                $userIdSearch->formName() => $params,
            ]);
            $idDataProvider->getPagination()->setPageSize($userIdSearch::SELECT2_LIMIT);
            $idDataProviderModels = $idDataProvider->getModels();
            /** если не производится поиск по айди, не делаем дополнительный запрос */
            $idDataProviderModelsCount = count($idDataProviderModels);
        }

        if (UserSearch::SELECT2_LIMIT - $idDataProviderModelsCount !== 0) {
            // Поиск по запросу
            $userQuerySearch = new UserSearch();

            /** @var ActiveDataProvider $queryDataProvider */
            $queryDataProvider = $userQuerySearch->search([
                $userQuerySearch->formName() => [
                    'queryName' => $q,
                    'namesRoles' => $roles,
                    'ignoreIds' => $ignoreIds + ArrayHelper::getColumn($idDataProviderModels, 'id'),
                    'sourceIds' => $sourceIds,
                    'streamIds' => $streamIds,
                    'skipCurrentUser' => $skipCurrentUser,
                ],
            ]);
            $queryDataProvider->getPagination()->setPageSize(UserSearch::SELECT2_LIMIT - $idDataProviderModelsCount);
            $queryDataProviderModels = $queryDataProvider->getModels();
        }

        // Сливаем результаты, выводим в первую очередь пользователя с ид, как в запросе
        $users = array_merge($idDataProviderModels, $queryDataProviderModels);

        return ['results' => array_map(function (User $item) use ($format) {
            $currencySymbol = null;
            if (strripos($format, ':currency:') !== false) {
                /** @var \mcms\payments\models\UserPaymentSetting $userSettingsData */
                $userSettingsData = Yii::$app->getModule('payments')
                    ->api('userSettingsData', ['userId' => $item->id])
                    ->getResult();
                $currencySymbol = Yii::$app->formatter->asCurrencyIcon($userSettingsData->getCurrentCurrency());
            }
            return [
                'text' => strtr($format, [
                    ':id:' => $item->id,
                    ':username:' => $item->username,
                    ':email:' => $item->email,
                    ':currency:' => $currencySymbol,
                ]),
                'id' => $item->id,
            ];
        }, $users)];
    }

    public function actionFindReferrals()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $userSearch = new UserSearch();

        $data = Yii::$app->request->get('data');
        $q = Yii::$app->request->get('q');
        $roles = ArrayHelper::getValue($data, 'roles');
        $format = ArrayHelper::getValue($data, 'format');
        /** @var ActiveDataProvider $dataProvider */
        $dataProvider = $userSearch->searchReferrals([
            $userSearch->formName() => [
                'username' => $q,
                'email' => $q,
                'namesRoles' => $roles
            ]
        ], Yii::$app->user->id);
        $dataProvider->getPagination()->setPageSize($userSearch::SELECT2_LIMIT);

        return ['results' => array_map(function (User $item) use ($format) {
            return [
                'text' => strtr($format, [
                    ':id:' => $item->id,
                    ':username:' => $item->username,
                    ':email:' => $item->email
                ]),
                'id' => $item->id,
            ];
        }, $dataProvider->getModels())];
    }

    public function actionLoginByUser($id)
    {
        // проверка разрешено ли логиниться этим юзером
        $permitedUsers = Yii::$app->getModule('users')
            ->api('notAvailableUserIds', [
                'userId' => Yii::$app->user->id,
            ])->getResult();

        if (count($permitedUsers) > 0 && in_array($id, $permitedUsers)) {
            throw new ForbiddenHttpException('Permission denied');
        }

        // Проверка статуса пользователя, если статус не активен, то выдадим сообщение что залогиниться нельзя.
        $user = $this->fetch($id);
        if ($user->status !== User::STATUS_ACTIVE) {
            $this->flashFail('users.login.login_by_user_status_error', ['status' => $user->getNamedStatus()]);
            if (Yii::$app->request->referrer) {
                return $this->redirect(Yii::$app->request->referrer);
            }
            return $this->redirect(Yii::$app->getModule('users')->urlCabinet);
        }
        $userIdentity = Yii::$app->user->identity;

        $systemLanguage = new SystemLanguage();
        $systemLanguage->setLang($user->language, true);

        $cookieValues = Yii::$app->user->getIdentityAndDurationFromCookie(); // в common переопределенный публичный метод

        Yii::$app->user->switchIdentity($user, 0); // <--- тут ещё очищается кука авторизации

        if ($cookieValues) {
            $this->setAuthCookie($cookieValues['identity'], $cookieValues['duration']);
        }

        $backIdentity = Yii::$app->session->get($user::SESSION_BACK_IDENTITY_ID) ?: [];
        $backIdentity[] = $userIdentity->getId();

        Yii::$app->session->set($user::SESSION_BACK_IDENTITY_ID, $backIdentity);
        Yii::$app->session->set($user::SESSION_AUTH_TOKEN_KEY, $user->getAuthKey());

        $redirectUrl = Yii::$app->getModule('users')->urlCabinet;
        return $this->redirect($redirectUrl);
    }

    /**
     * В методе Yii::$app->user->switchIdentity($user, 0); происходит очистка куки
     * Но при входе под другим юзером нам надо сохранить куку самого первого юзера в цепочке.
     * Это сделано для того, чтобы если сессия пользователя истечёт или сбросится по какой-то причине,
     * не происходило полного разлогина, а только возврат к самому первому юзеру от которого осуществлен вход.
     *
     * @param IdentityInterface $identity
     * @param int $duration
     */
    protected function setAuthCookie($identity, $duration)
    {
        if (Yii::$app->user->enableAutoLogin) {
            // в модуле common переопределенный публичный метод
            Yii::$app->user->sendIdentityCookie($identity, $duration);
        }
    }

    public function actionLogoutByUser()
    {
        $backIdentity = Yii::$app->session->get(User::SESSION_BACK_IDENTITY_ID);

        if ($backIdentity === null) {
            $authApi = Yii::$app->getModule('users')->api('auth');
            $authApi->logout();
            return $this->goHome();
        }

        if ($id = array_pop($backIdentity)) {
            $user = $this->fetch($id);

            $cookieValues = Yii::$app->user->getIdentityAndDurationFromCookie(); // в common переопределенный публичный метод

            Yii::$app->user->switchIdentity($user, 0); // <--- тут ещё очищается кука авторизации

            if ($cookieValues) {
                $this->setAuthCookie($cookieValues['identity'], $cookieValues['duration']);
            }

            Yii::$app->session->set(User::SESSION_BACK_IDENTITY_ID, $backIdentity);
            Yii::$app->session->set(User::SESSION_AUTH_TOKEN_KEY, $user->getAuthKey());

            $systemLanguage = new SystemLanguage();
            $systemLanguage->setLang($user->language, true);
        }
        $redirectUrl = Yii::$app->getModule('users')->urlCabinet;
        return $this->redirect($redirectUrl);
    }

    private function canViewUser($userId)
    {
        if (Yii::$app->user->identity->isCurrentUser($userId) || !Yii::$app->user->identity->canViewUser($userId, true)) {
            $this->flashFail('app.common.access_denied', ['permission' => 'UsersUserView']);
            return $this->redirect([$this->defaultAction]);
        }

        return true;
    }

    public function actionActivate($id)
    {
        $user = $this->fetch($id);
        if ($user->status != $user::STATUS_ACTIVATION_WAIT_HAND) {
            return AjaxResponse::error();
        }
        $user->status = $user::STATUS_ACTIVE;
        $userForm = new UserForm($user);
        if ($userForm->validate()) {
            $userForm->createUser();
            return AjaxResponse::success();
        }
        return AjaxResponse::error();
    }

    public function actionMassActivate()
    {
        $ids = Json::decode(Yii::$app->request->post('value'));
        $users = User::findAll(['id' => $ids]);
        $connection = \Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            /** @var User $user */
            foreach ($users as $user) {
                if ($user->status != $user::STATUS_ACTIVATION_WAIT_HAND) {
                    $transaction->rollBack();
                    return AjaxResponse::error();
                }

                $user->status = $user::STATUS_ACTIVE;
                $userForm = new UserForm($user);
                if ($userForm->validate()) {
                    $userForm->createUser();
                } else {
                    $transaction->rollBack();
                    return AjaxResponse::error($userForm->getFirstErrors());
                }
            }

        } catch (\Exception $exception) {
            $transaction->rollBack();
            throw $exception;
        }
        $transaction->commit();
        return AjaxResponse::success();
    }

    public function actionMassDeactivate()
    {
        $ids = Json::decode(Yii::$app->request->post('value'));
        User::updateAll(['status' => User::STATUS_INACTIVE], ['id' => $ids]);
        return AjaxResponse::success();
    }

    /**
     * tricky: переопределено для того, чтобы игнорился вызов метода из AbstractBaseController.
     * Он будет игнориться, т.к. там не передается event, а в методе getNotificationModuleId делается проверка на его наличие
     * @inheritdoc
     */
    protected function setNotificationAsViewed($event = null, $fn = null, $onlyOwner = false)
    {
        $binModuleId = $this->getNotificationModuleId($event, $fn);
        if (!$binModuleId) return null;

        return parent::setNotificationAsViewed($event, $binModuleId, $onlyOwner);
    }

    /**
     * @param User $user
     * @return string
     */
    protected function renderUserContacts($user)
    {
        $searchModel = new UserContactsSearch();
        $searchModel->user_id = $user->id;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->renderPartial('_contacts_grid', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'user' => $user
        ]);
    }
}
