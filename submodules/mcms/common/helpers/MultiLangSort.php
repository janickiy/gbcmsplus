<?php

namespace mcms\common\helpers;

use mcms\common\multilang\LangAttribute;

/**
 * Class MultiLangSort
 * @package mcms\common\helpers
 */
class MultiLangSort
{
    /**
     *  Сортировка массива с мультиязычными значаениями
     *  $data = [
     *    [1] => mcms\common\multilang\LangAttribute Object
     *        (
     *             [ru] => Название
     *             [en] => Name
     *        )
     *  ];
     *
     * @param \mcms\common\multilang\LangAttribute[] $data
     * @param integer $sort
     * @return array
     */
    public static function sort($data, $sort = SORT_ASC)
    {
        $data = array_map(function ($item) {
            /* @var $item LangAttribute */
            return $item->getCurrentLangValue();
        }, $data);

        $sort == SORT_ASC ? asort($data) : arsort($data);
        return $data;
    }

}