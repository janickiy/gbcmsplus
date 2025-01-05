<?php

namespace mcms\api\components;

use mcms\api\components\statFetch\StatFetch;
use mcms\common\helpers\ArrayHelper;
use mcms\common\multilang\LangAttribute;
use mcms\statistic\components\newStat\FormModel;
use mcms\statistic\models\ColumnsTemplate;
use Yii;
use yii\base\Object;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * Class BaseMapper
 */
abstract class BaseMapper extends Object
{
    /**
     * Массив с доступными значениями для модели User
     * указывается в виде [
     *   'название поля для апи' => 'название поля в модели',
     * ]
     *
     * @var array
     */
    public static $availableFields = [];

    /**
     * Массив с доступными связанными мапперами
     * указывается в виде [
     *   'название связи для апи' => 'название связи в модели',
     * ]
     *
     * @var array
     */
    public static $availableRelatedFields = [];

    /**
     * Массив доступных полей для текущего маппера
     * @var string[]
     */
    public static $availableCustomFields = [];

    /**
     * @var string поле, значение которого будет возвращено, если ни одного не выбрали
     */
    public $defaultField;

    /**
     * @var array выбранные поля из доступных
     */
    public $enabledFields;

    /**
     * @var array поля по которым искать
     */
    public $searchFields;

    public $customFields;

    public $orderFields;

    /**
     * @var BaseMapper[] подгруженные связанные мапперы
     */
    public $relatedMappers = [];

    /**
     * @var string
     */
    public $search;

    /**
     * @var int
     */
    public $limit = 10;

    /**
     * Лимит для вложеных записей
     * @var int
     */
    public $relatedLimit = 100;

    /**
     * @var int
     */
    public $offset = 0;

    /**
     * @var int
     */
    public $depth = 0;

    /**
     * @var array
     */
    public $filters;

    /**
     * Преобразует название класса маппера в привычный вид для связи моделей
     * PartnerProgramMapper -> partnerProgram
     *
     * @return string
     */
    public static function getName()
    {
        $id = Inflector::camel2id(StringHelper::basename(get_called_class()), '-');
        if (($pos = mb_strrpos($id, '-')) !== false) {
            $id = mb_substr($id, 0, 0 - (mb_strlen($id) - $pos));
        }

        return lcfirst(Inflector::id2camel($id, '-'));
    }

    /**
     * @return ActiveQuery
     */
    abstract public function getRawQuery();

    /**
     * @param string $alias
     * @return array
     */
    abstract public function getSearchConditions($alias);

    /**
     * @return array
     */
    public function getMappedResult()
    {
        $query = $this->getRawQuery();

        $this->buildQuery($query);

        $result = $query->all();

        return $this->toMap($result);
    }

    /**
     * @param ActiveQuery $query
     */
    public function buildQuery($query)
    {
        if ($this->relatedMappers) {
            $this->processRelations($query);
        }

        $this->applyFilters($query);

        $searchFilters = $this->getRecoursiveSearchConditions([]);

        $query->andFilterWhere($searchFilters);

        $this->addSearchJoins($query);

        $this->applyCustomFieldsJoins($query);

        $this->buildOrderBy($query);

        $query
            ->limit($this->limit)
            ->offset($this->offset);
    }

    /**
     * @param array|ActiveRecord[] $data
     * @return array
     */
    public function toMap($data)
    {
        $result = [];

        $fields = $this->getFilteredEnabledFields() ?: ($this->depth < 0 ? [$this->defaultField] : static::$availableFields);
        $fields = array_flip($fields);

        foreach ($data as $key => $item) {
            if ($this->depth < 0) {
                $result[$key] = $item[array_shift($fields)];
                continue;
            }

            foreach ($fields as $field) {
                if (!$item->canGetProperty($field)) {
                    continue;
                }

                $attribute = $item->$field;
                if ($attribute instanceof LangAttribute) {
                    $attribute = (string)$attribute;
                }

                $result[$key][$field] = $attribute;
            }

            foreach ($this->relatedMappers as $name => $mapper) {
                $models = $item[$name] instanceof ActiveRecord ? [$item[$name]] : $item[$name];  // для связей 1 к 1

                $result[$key][$name] = $mapper->toMap($models);
            }

            foreach ($this->customFields as $customField) {
                if (is_array($customField)) {
                    // это означает кастомфилд в релейшене
                    continue;
                }

                if (!$item->canGetProperty($customField)) {
                    continue;
                }

                $result[$key][$customField] = (float)$item->$customField;
            }
        }

        return $result;
    }

    /**
     * @param ActiveQuery $query
     */
    public function processRelations($query)
    {
        foreach ($this->relatedMappers as $name => $mapper) {
            $query->with([$name => function ($query) use ($mapper) {
                $mapper->applyFilters($query);
                $mapper->applyCustomFieldsJoins($query);
                $mapper->buildOrderBy($query);
                $mapper->processRelations($query);
            }]);
        }
    }

    /**
     * Рекурсивно получаем условия ддля фильтрации по search строке
     * @param array $condition
     * @return array
     */
    protected function getRecoursiveSearchConditions($condition = [])
    {
        $alias = static::getName();
        $filteredConditions = $this->getSearchConditions($alias);

        // добавляем оператор OR
        $condition[0] = 'or';
        $condition = ArrayHelper::merge($condition, array_values($filteredConditions));

        foreach ($this->relatedMappers as $relatedFilterMapper) {
            $condition = $relatedFilterMapper->getRecoursiveSearchConditions($condition);
        }

        return $condition;
    }

    /**
     * @return array
     */
    public function getFilteredEnabledFields()
    {
        return array_intersect_key(static::$availableFields, array_flip($this->enabledFields));
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isFieldAvailable($name)
    {
        return isset(static::$availableFields[$name]);
    }

    /**
     * @param $name
     * @return bool
     */
    public function isCustomFieldAvailable($name)
    {
        return in_array($name, static::$availableCustomFields, true);
    }


    /**
     * Применяем фильтры
     * @param ActiveQuery $query
     */
    public function applyFilters(ActiveQuery $query)
    {
    }

    /**
     * Применяем сортировку
     * @param ActiveQuery $query
     */
    public function buildOrderBy(ActiveQuery $query)
    {
        foreach ($this->orderFields as $field => $orderDirection) {
            if (is_array($orderDirection)) {
                continue;
            }

            if (in_array($field, static::$availableCustomFields, true)) {
                if (!in_array($field, $this->customFields, true)) {
                    // пока разрешили применение сортировки только по тем кастом полям, которые есть в селекте
                    continue;
                }

                // для кастомных полей не надо прописывать алиас
                $query->addOrderBy([$field => $orderDirection]);
                continue;
            }

            // а для обычных надо
            $query->addOrderBy([static::getName() . '.' . $field => $orderDirection]);
        }
    }

    /**
     * Проверка фильтра на пустоту (пустая строка, пустой массив, массив с пустыми значениями, null)
     * @param $filter
     * @return bool
     */
    protected function isEmptyFilterValue($filter)
    {
        $value = ArrayHelper::getValue($this->filters, $filter);

        if ($value === [] || $value === '' || $value === null) {
            return true;
        }

        if (is_array($value)) {
            return empty(array_filter($value));
        }

        return false;
    }

    /**
     * По какому полю группировать текущий маппер в стате
     * @return string
     */
    public static function getStatGroupBy()
    {
        return static::getName();
    }

    /**
     * По какому полю фильтровать текущий маппер в стате
     * @return string
     */
    public static function getStatFilterBy()
    {
        return static::getName();
    }

    /**
     * TRICKY костыль. Для аякс-поиска по search полям требует наличия джойна
     * в соответствующие релейшены. К сожалению при текущей архитектуре больше хз
     * куда запихнуть чтоб джойны выполнялись динамично в зависимости от того по каким
     * серч-полям делаем поиск. Поэтому в дочерних классах переопределяем этот метод и
     * хардкодим джойны
     * @param $query
     */
    protected function addSearchJoins($query)
    {
    }

    /**
     * Применяем фильтр для forceIds. Решается через ORDER BY FIELD и влияет на лимит.
     * @param ActiveQuery $query
     */
    protected function applyForceIdsFilter(ActiveQuery $query)
    {
        if (!$this->isEmptyFilterValue('forceIds')) {
            $query->addOrderBy(new Expression(strtr('FIELD(:mapperName.id, :values) DESC', [
                ':mapperName' => static::getName(),
                ':values' => implode(',', array_map(
                    function ($element) {
                        return (int)$element;
                    },
                    $this->filters['forceIds']
                ))
            ])));
            $this->limit = max(count($this->filters['forceIds']), $this->limit);

            // строка ниже на всякий случай если вдруг applyFilters окажется после установки лимита в базовом классе
            $query->limit($this->limit);
        }
    }

    /**
     * Применяем джойны со статой для customFields
     * @param ActiveQuery $query
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function applyCustomFieldsJoins(ActiveQuery $query)
    {
        if (!$this->customFields) {
            return;
        }

        $formModel = new FormModel();
        $formModel->groups = [static::getStatGroupBy()];

        $filters = $this->filters;
        unset($filters['groups']); // сюда может прилететь поле из формы, поэтому сбрасываем

        $formModel->load($filters, '');

        /** @var StatFetch $fetch */
        $fetch = Yii::createObject(
            ['class' => StatFetch::class, 'customFields' => $this->customFields],
            [$formModel, ColumnsTemplate::SYS_TEMPLATE_ALL]
        );

        $fetch->prepareTmpTable();

        $query->leftJoin(['cf' => $fetch->getTmpTableName()], 'cf.id = ' . static::getName() . '.id');
        $query->addSelect(static::getName() . '.*');

        foreach ($this->customFields as $customField) {
            $sumFields = StatFetch::getCustomFieldRowDtoAttributes($customField);

            if (!$sumFields) {
                continue;
            }

            // добавляем алиас
            $sumFields = array_map(function ($field) {
                return 'cf.' . $field;
            }, $sumFields);

            $query->addSelect([$customField => new Expression(implode('+', $sumFields))]);
        }
    }
}
