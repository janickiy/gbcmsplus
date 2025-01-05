<?php

namespace mcms\common\event;

use Yii;

/**
 * Собыьтие добавление/удаления разрешения
 */
class RbacAssignRevoke extends Event
{
    /* @var string название события assign/revoke */
    public $event;
    /* @var array роль или разрешение которой назначено разрешение */
    public $parent;
    /* @var array роль или разрешение назначенная родителю */
    public $child;
    /* @var integer кем было назначено разрешение */
    public $updatedByUserId;

    /**
     * RbacAssignRevoke consctructor.
     * @param $event
     * @param $parent
     * @param $child
     */
    public function __construct($event = null, $parent = null, $child = null)
    {
        $this->event = $event;
        $this->parent = $parent;
        $this->child = $child;
        $this->updatedByUserId = Yii::$app->user->id;
    }

    /**
     * @return string
     */
    public function getEventName()
    {
        return 'Rbac Assign/Revoke';
    }

    /**
     * @return array
     */
    public function getReplacements()
    {
        return [
            'updatedByUserId' => $this->updatedByUserId,
            'event' => $this->event,
            'parent' => $this->parent,
            'child' => $this->child
        ];
    }
}