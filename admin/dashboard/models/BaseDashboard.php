<?php

namespace admin\dashboard\models;

use admin\dashboard\common\base\BaseBlock;
use Yii;
use yii\base\Exception;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * @property integer $id
 * @property integer $user_id
 * @property string $code
 * @property bool $status
 */
abstract class BaseDashboard extends \yii\db\ActiveRecord
{
    const ACTIVE = 1;
    const INACTIVE = 0;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'code'], 'required'],
            [['user_id'], 'integer'],
            [['status'], 'boolean'],
            [['code'], 'string', 'max' => 32]
        ];
    }

    /**
     * Синхронизация набора пользователя с доступными виджетами/гаджетами
     */
    public static function initItems($userId)
    {
        // Удаляем несуществующие гаджеты/виджеты
        $itemCodes = static::getItemCodes();
        static::deleteAll(['and', ['user_id' => $userId], ['not in', 'code', $itemCodes]]);

        // Определяем оставшиеся гаджеты/виджеты
        $userItemCodes = static::findUserItems($userId)->select('code')->column();

        // Добавляем недостающие гаджеты/виджеты
        foreach (array_diff($itemCodes, $userItemCodes) as $itemCode) {
            $itemModel = new static(['user_id' => $userId, 'code' => $itemCode, 'status' => static::ACTIVE]);
            $itemModel->insert();
        }
    }

    /**
     * Все виджеты/гаджеты
     * @return \array[]
     * @throws Exception
     */
    protected static function getItemsInternal()
    {
        throw new Exception('Implement');
    }

    /**
     * Коды всех доступных виджетов/гаджетов
     * @return string[]
     */
    public static function getItemCodes()
    {
        $items = static::getItemsInternal();

        return array_keys($items);
    }

    /**
     * Создаем коллекцию гаджетов/виджетов для пользователя
     * @param int $userId
     * @return array[]
     * @throws Exception
     */
    public static function getItems($userId)
    {
        // получаем список ['code' => '\SomeGadgetClass'] чтобы дальше создать инстансы
        $items = static::getItemsInternal();
        $instances = static::getUserItems($userId);

        $result = [];
        foreach ($items as $code => $config) {
            if (!static::getItemInstance($userId, $code)->hasAccess()) {
                unset($items[$code]);
                continue;
            }
            // включен ли виджет
            $isInstance = (bool)ArrayHelper::getValue($instances, $code);
            $result[$code] = $config;
            $result[$code]['userId'] = $isInstance ? $userId : null;
            $result[$code]['itemId'] = $isInstance ? $instances[$code]->id : null;
            $result[$code]['emptyData'] = true;
        }

        return $result;
    }

    /**
     * Префикс.
     * Используется для различения типов виджетов/гаджетов в селекте
     * @return string Одна буква
     * @throws Exception
     */
    public static function getPrefix()
    {
        throw new Exception('Implement');
    }

    /**
     * Получить данные учитывая префикс.
     * Например: коды виджетов/гаджетов.
     * @param string[] $data
     * @return \string[]
     */
    public static function filterByPrefix($data)
    {
        $filteredData = [];
        foreach ((array)$data as $value) {
            if ($value[0] == static::getPrefix()) $filteredData[] = substr($value, 1, strlen($value));
        }

        return $filteredData;
    }

    /**
     * Виджеты/гаджеты для селекта
     * @param bool $usePrefix
     * @return \string[]
     */
    public static function getSelectItems($userId, $usePrefix = false)
    {
        $items = static::getItems($userId);
        $selectItems = [];
        foreach ($items as $code => &$config) {
            $selectItems[($usePrefix ? static::getPrefix() : null) . $code] = static::getItemInstance($userId, $code)->getTitle();
        }

        return $selectItems;
    }

    /**
     * Виджеты/гаджеты пользователя для селекта
     * @param bool $usePrefix
     * @return \string[]
     */
    public static function getSelectedItems($userId, $usePrefix = false)
    {
        $selectedItems = static::findUserItems($userId)->andWhere(['status' => static::ACTIVE])->select('code')->column();

        if ($usePrefix) {
            foreach ($selectedItems as &$code) $code = static::getPrefix() . $code;
        }

        return $selectedItems;
    }

    /**
     * Поиск виджетов/гаджетов пользователя
     * @return ActiveQuery
     */
    public static function findUserItems($userId)
    {
        return static::find()->andWhere(['user_id' => $userId]);
    }

    /**
     * Сохранить новый набор виджетов/гаджетов пользователя
     * @param string[] $codes Коды виджетов/гаджетов
     */
    public static function saveUserItems($userId, $codes)
    {
        static::updateAll(['status' => static::INACTIVE], ['user_id' => $userId]);
        static::updateAll(['status' => static::ACTIVE], ['user_id' => $userId, 'code' => $codes]);
    }

    protected static $_userItemModels = [];

    /**
     * Получение моделей виджетов гаджетов которые храняться в базе
     * @param $userId
     * @return mixed
     */
    private static function getUserItems($userId)
    {
        if (!isset(static::$_userItemModels[static::getPrefix()][$userId])) {
            static::$_userItemModels[static::getPrefix()][$userId] = static::findUserItems($userId)->andWhere(['status' => static::ACTIVE])->indexBy('code')->all();
        }
        return static::$_userItemModels[static::getPrefix()][$userId];
    }

    protected static $_userItemInstances = [];

    /**
     * Создание объекта конкретного гаджета/виджета
     * @param string $userId
     * @param string $code
     * @return BaseBlock|object
     */
    protected static function getItemInstance($userId, $code)
    {
        if (!isset(static::$_userItemInstances[static::getPrefix()][$userId][$code])) {
            $items = static::getItemsInternal();

            $instance = null;
            if (isset($items[$code])) {
                $items[$code]['userId'] = $userId;
                static::$_userItemInstances[static::getPrefix()][$userId][$code] = Yii::createObject($items[$code]);
            }
        }

        return static::$_userItemInstances[static::getPrefix()][$userId][$code];
    }
}
