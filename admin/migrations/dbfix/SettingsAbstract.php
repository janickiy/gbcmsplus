<?php

namespace admin\migrations\dbfix;

use kartik\builder\Form;
use Yii;
use yii\helpers\ArrayHelper;
use mcms\common\widget\SettingsDependency;

/**
 * Class SettingsAbstract
 */
abstract class SettingsAbstract implements SettingsInterface
{
    const OTHER_GROUP = 'app.common.group_other';
    const NO_FORM_GROUP = 'no_form_group';
    const DEFAULT_SORT = 1000000000;

    protected $name;
    protected $type;
    protected $value;
    protected $key;
    protected $hint;

    //массив с разрешениями, которые надо проверить
    //если массив пустой и $checkPermission == true,
    //то подставит будет проверять на EditModuleSettings{$moduleId}
    protected $permissions = [];

    protected $group;
    protected $formGroup;
    protected $widgetClass;
    protected $options;
    protected $container;
    protected $sort;

    private $validators = [];

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return Yii::_t($this->name);
    }

    /**
     * @return StringObject
     */
    public function getHint()
    {
        return $this->hint ? Yii::_t($this->hint) : $this->hint;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param $hint
     * @return $this
     */
    public function setHint($hint)
    {
        $this->hint = $hint;
        return $this;
    }

    /**
     * @param $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return StringObject
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return array
     */
    protected function getObjectVars()
    {
        return [
            'value' => $this->getValue(),
            'key' => $this->getKey(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return serialize($this->getObjectVars());
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized) ?: [];
        $this->setObjectVars($data);
    }

    /**
     * @param array $objectVars
     */
    protected function setObjectVars(array $objectVars)
    {
        $this->setKey(ArrayHelper::getValue($objectVars, 'key'));
        $this->setValue(ArrayHelper::getValue($objectVars, 'value'));
    }

    /**
     * @inheritDoc
     */
    public function getFormAttributes()
    {
        return [
            'name' => $this->getName(),
            'type' => $this->getType(),
            'label' => $this->getName(),
            'hint' => $this->getHint(),
            'group' => $this->getGroup(),
            'formGroup' => $this->getFormGroup(),
            'widgetClass' => $this->getWidgetClass(),
            'options' => $this->getOptions(),
            'container' => $this->container,
            'sort' => $this->sort,
        ];
    }

    /**
     * @param $value
     */
    public function beforeValue(&$value)
    {
    }

    /**
     * @return array
     */
    public function getBehaviors()
    {
        return [];
    }

    /**
     * @param array $permissions
     * @return $this
     */
    public function setPermissions(array $permissions)
    {
        $this->permissions = $permissions;
        return $this;
    }

    /**
     * @return array
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @param $sort int Положение настройки в группе или подгруппе
     * @return $this
     */
    public function setSort($sort)
    {
        $this->sort = $sort;
        return $this;
    }

    /**
     * @return int
     */
    public function getSort()
    {
        return $this->sort ?: self::DEFAULT_SORT;
    }

    /**
     * @param array $group Массив с названием группы настройки и сортировкой группы
     * @return $this
     */
    public function setGroup(array $group)
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @return array
     */
    public function getGroup()
    {
        return $this->group ?: ['name' => self::OTHER_GROUP, 'sort' => self::DEFAULT_SORT];
    }

    /**
     * @param array $formGroup Массив с названием подгруппы настройки и сортировкой подгруппы
     * @return $this
     */
    public function setFormGroup(array $formGroup)
    {
        $this->formGroup = $formGroup;
        return $this;
    }

    /**
     * @return array
     */
    public function getFormGroup()
    {
        return $this->formGroup ?: ['name' => self::NO_FORM_GROUP, 'sort' => self::DEFAULT_SORT];
    }

    /**
     * @param $widgetClass
     * @return $this
     */
    public function setWidgetClass($widgetClass)
    {
        $this->widgetClass = $widgetClass;
        return $this;
    }

    /**
     * @return null
     */
    public function getWidgetClass()
    {
        return $this->widgetClass ?: null;
    }

    /**
     * @param array $options Массив настроек kartik\form\ActiveField
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options ?: [];
    }

    /**
     * Устанавливает зависимость от другой настройки
     * @param array $dependencyOptions Массив с ключом настройки, от которой будет зависеть данная настройка, и значением, при которой данная настройка будет обязательна
     * @return $this
     */
    public function setDependency(array $dependencyOptions)
    {
        //Меняем тип на Widget, чтобы можно было задать виджет для этого поля
        $this->type = Form::INPUT_WIDGET;
        $this->widgetClass = SettingsDependency::class;
        //Настройки для виджета
        $this->options['dependencyAttribute'] = $dependencyOptions['attribute'];
        $this->options['dependencyValue'] = $dependencyOptions['value'];

        //Оборочием поле в контейнер
        $this->container['class'] = 'dependent-container';

        $validators = $this->getValidator();
        foreach ($this->getValidator() as $key => $validator) {
            if ($validator[0] === 'required') {
                unset($validators[$key]);
            }
        }

        $dependencyId = isset($dependencyOptions['id']) ? $dependencyOptions['id'] : $dependencyOptions['attribute'];

        $validators[] = ['required', ['skipOnEmpty' => true, 'whenClient' => "function (attribute, value) {
      var dependencyElement = $('[id*=\"{$dependencyId}\"]');
      var value = dependencyElement.prop('type') === 'checkbox' ? dependencyElement.prop('checked') : dependencyElement.val();
      return value == {$dependencyOptions['value']};
    }"]];
        $this->setValidators($validators);
        return $this;
    }

    /**
     * @param array $validators
     * @return $this
     */
    public function setValidators(array $validators)
    {
        $this->validators = $validators;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getValidator()
    {
        return $this->validators;
    }
}
