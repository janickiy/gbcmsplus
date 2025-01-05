<?php

namespace mcms\common\helpers;

/**
 *
 * Класс для генерации "безопасной" ссылки. Если на url нет прав, то вернется пустая строка.
 *
 * Class Link
 * @package common\components\html
 */
class Link
{
    /**
     * Генерация ссылки.
     * Если для переданного URL не достаточно прав, будет возвращена пустая строка или только название ссылки
     * @param $path - адрес ссылки
     * @param array $pathParams - GET параметры адреса
     * @param array $attributes - аттрибуты для тега <a>, например аттрибут class.
     * @param string $innerContent - <a>содержимое ссылки</a>
     * @return string
     * @Deprecated
     */
    static public function get($path, $pathParams = [], $attributes = [], $innerContent = '', $returnEmptyString = true)
    {
        return Html::a($innerContent, array_merge([$path], $pathParams), $attributes, [], $returnEmptyString);
    }

    /**
     * Проверка доступа к URL
     * @param $path - адрес ссылки
     * @param array $pathParams - GET параметры адреса
     * @return bool
     */
    static public function hasAccess(array $path, array $pathParams = [])
    {
        return Html::hasUrlAccess(array_merge([$path], $pathParams));
    }
}