<?php

namespace mcms\common\event;

use Yii;

class ReplacementHelp
{
    public $class;
    public $label;
    protected $help;

    /**
     * ReplacementHelp constructor.
     * @param $class
     * @param $label
     */
    public function __construct($class, $label)
    {
        $this->class = $class;
        $this->label = $label;
    }

    public function getHelp()
    {
        if ($this->class !== null) {
            $classInstance = Yii::createObject($this->class);
            if (method_exists($classInstance, 'getReplacementsHelp')) {
                $this->help = $classInstance->getReplacementsHelp();
            }
        }

        return $this;
    }

    public function asArray()
    {
        if ($this->help !== null) {

            $help = [];
            foreach ($this->help as $key => $value) {
                $help[$key] = $value->getHelp()->asArray();
            }

            return $help;
        }

        return $this->label;
    }
}