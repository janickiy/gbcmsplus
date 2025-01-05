<?php

namespace mcms\user\models;

use mcms\common\DynamicActiveRecord;

/**
 * переопределили только ради избавления от какашки @see DynamicActiveRecord
 * в котором криво переопределен __get(). Но не можем поправить такое поведение этого метода, т.к.
 * его косячность уже много где используется как данное и я не представляю как найти ВСЕ места, в которых надо исправить
 * такое его использование
 */
class UserStaticAR extends User
{
    protected $additionalFieldsRelationName;
}
