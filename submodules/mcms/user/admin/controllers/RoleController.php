<?php

namespace mcms\user\admin\controllers;

use mcms\common\rbac\AuthItemsManager;
use mcms\modmanager\models\Module;
use mdm\admin\models\AuthItem;
use Yii;
use yii\rbac\Item;
use mdm\admin\components\MenuHelper;


class RoleController extends \mdm\admin\controllers\RoleController
{
    /**
     * Search role
     * @param string $id
     * @param string $target
     * @param string $term
     * @return array
     */
    public function actionSearch($id, $target, $term = '')
    {
        $result = [
            'Roles' => [],
            'Permissions' => [],
            'Routes' => [],
        ];
        $authManager = Yii::$app->authManager;
        if ($target == 'avaliable') {
            $children = array_keys($authManager->getChildren($id));
            $children[] = $id;
            foreach ($authManager->getRoles() as $name => $role) {
                if (in_array($name, $children)) {
                    continue;
                }
                if (empty($term) or strpos($name, $term) !== false) {
                    $result['Roles'][$name] = $name;
                }
            }
            $permissions = $authManager->getPermissions();

            $locator = \Yii::$container->get('admin\components\module\LocatorInterface');
            $allModules = $locator->getLocatedModules();

            $modulesEnabled = Module::find()->where(['is_disabled' => 0])->all();

            foreach ($allModules as $module) {
                foreach ($modulesEnabled as $me) {
                    /* @var $me \mcms\modmanager\models\Module */
                    if ($me->module_id == $module['id']) {

                        $reflect = new \ReflectionClass($module['class']);
                        $name = new $module['class']($module['id']);
                        $controllerNamespace = $reflect->getProperty('controllerNamespace')->getValue($name);

                        foreach (glob($name->basePath . "/controllers/*Controller.php") as $controller) {
                            $class = basename($controller, ".php");
                            $reflection = new \ReflectionClass($controllerNamespace . '\\' . $class);
                            $methods = $reflection->getMethods();
                            foreach ($methods as $method) {
                                if (preg_match('/^action+(\w{2,})/', $method->name, $match)) {
                                    $perm = ucfirst($module['id']) . str_replace('Controller', '', $class) . $match[1];
                                    $permissions[$perm] = $perm;
                                }
                            }
                        }
                    }
                }
            }

            foreach ($permissions as $name => $role) {
                if (in_array($name, $children)) {
                    continue;
                }
                if (empty($term) or strpos($name, $term) !== false) {
                    $result[$name[0] === '/' ? 'Routes' : 'Permissions'][$name] = $name;
                }
            }
        } else {
            foreach ($authManager->getChildren($id) as $name => $child) {
                if (empty($term) or strpos($name, $term) !== false) {
                    if ($child->type == Item::TYPE_ROLE) {
                        $result['Roles'][$name] = $name;
                    } else {
                        $result[$name[0] === '/' ? 'Routes' : 'Permissions'][$name] = $name;
                    }
                }
            }
        }
        Yii::$app->response->format = 'json';

        return array_filter($result);
    }

    public function actionCreate()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $itemsManager = new AuthItemsManager;
            $model = new AuthItem(null);
            $model->type = Item::TYPE_ROLE;
            if ($model->load(Yii::$app->getRequest()->post()) && $model->save()) {
                $itemsManager->createPermission(
                    $itemsManager->getRolePermissionName($model->name),
                    'Редактирование роли ' . $model->name,
                    'UserPermissions',
                    array_unique(array_merge(['root'], Yii::$app->user->identity->getRoles()->select(['name'])->column()))
                );
                $transaction->commit();
                MenuHelper::invalidate();

                return $this->redirect(['view', 'id' => $model->name]);
            } else {
                return $this->render('create', ['model' => $model,]);
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function actionDelete($id)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $itemsManager = new AuthItemsManager;

            $model = $this->findModel($id);
            if (Yii::$app->getAuthManager()->remove($model->item)) {
                $itemsManager->removePermission($itemsManager->getRolePermissionName($model->name));
            }
            $transaction->commit();

            MenuHelper::invalidate();

            return $this->redirect(['index']);
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }


    /**
     * Assign or remove items
     * @param string $id
     * @param string $action
     * @return array
     */
    public function actionAssign()
    {
        $post = Yii::$app->getRequest()->post();
        $id = $post['id'];
        $action = $post['action'];
        $roles = $post['roles'];
        $manager = Yii::$app->getAuthManager();
        $parent = $manager->getRole($id);
        $error = [];
        if ($action == 'assign') {
            foreach ($roles as $role) {
                $child = $manager->getRole($role);
                if (!$child) {
                    $child = $manager->getPermission($role);
                    if (!$child) {
                        $perm = $manager->createPermission($role);
                        $manager->add($perm);
                        $child = $manager->getPermission($role);
                    }
                }

                try {
                    $manager->addChild($parent, $child);
                } catch (\Exception $e) {
                    $error[] = $e->getMessage();
                }
            }
        } else {
            foreach ($roles as $role) {
                $child = $manager->getRole($role);
                $child = $child ?: $manager->getPermission($role);
                try {
                    $manager->removeChild($parent, $child);
                } catch (\Exception $e) {
                    $error[] = $e->getMessage();
                }
            }
        }
        MenuHelper::invalidate();
        Yii::$app->response->format = 'json';

        return [
            'type' => 'S',
            'errors' => $error,
        ];
    }

}