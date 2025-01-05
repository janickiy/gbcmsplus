<?php


namespace mcms\common\module\api;


use mcms\common\helpers\ArrayHelper;
use yii\base\Widget;
use yii\data\DataProviderInterface;
use yii\db\ActiveRecord;
use yii\helpers\HtmlPurifier;


abstract class ApiResult
{

    protected $errors = [];
    /** @var  DataProviderInterface */
    protected $dataProvider;
    protected $resultType;
    protected $mapParams = ['id', 'name'];
    /** @var  Widget */
    protected $widget;

    const RESULT_TYPE_ARRAY = 0;
    const RESULT_TYPE_DATAPROVIDER = 1;
    const RESULT_TYPE_MAP = 2;
    const RESULT_TYPE_WIDGET = 3;

    public function __construct($params = [])
    {
        $this->setResultTypeArray();
        $this->init($params);
    }

    abstract function init($params = []);

    public function getResult()
    {
        switch ($this->resultType) {
            case self::RESULT_TYPE_DATAPROVIDER:
                return $this->getDataProvider();
                break;
            case self::RESULT_TYPE_ARRAY:
                return $this->getArray();
                break;
            case self::RESULT_TYPE_MAP:
                return $this->getMap();
                break;
            case self::RESULT_TYPE_WIDGET:
                return $this->getWidget();
                break;
        }

        return false;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function addError($error)
    {
        $this->errors[] = $error;
    }

    public function setDataProvider(DataProviderInterface $dataProviderInterface)
    {
        $this->dataProvider = $dataProviderInterface;

        return $this;
    }

    public function prepareDataProvider(ActiveRecord $searchModel, $params = [])
    {

        $searchParams = ArrayHelper::getValue($params, 'conditions', []);
        $paginationParams = ArrayHelper::getValue($params, 'pagination', ['pageSize' => 100]);

        $searchModel->setAttributes($searchParams);

        $this->dataProvider = $searchModel->search($searchParams);
        $this->dataProvider->setSort(ArrayHelper::getValue($params, 'sort', $this->dataProvider->getSort()));
        $this->dataProvider->setPagination($paginationParams);

    }

    private function setResultType($type)
    {
        $this->resultType = $type;

        if (!in_array($type, [
            self::RESULT_TYPE_ARRAY,
            self::RESULT_TYPE_DATAPROVIDER,
            self::RESULT_TYPE_MAP,
            self::RESULT_TYPE_WIDGET
        ])) {
            $this->addError('Unsupported result type');
        }

        return $this;
    }

    public function setResultTypeArray()
    {
        $this->setResultType(self::RESULT_TYPE_ARRAY);
        return $this;
    }

    public function setResultTypeDataProvider()
    {
        $this->setResultType(self::RESULT_TYPE_DATAPROVIDER);
        return $this;
    }

    public function setResultTypeMap()
    {
        $this->setResultType(self::RESULT_TYPE_MAP);
        return $this;
    }

    public function setResultTypeWidget()
    {
        $this->setResultType(self::RESULT_TYPE_WIDGET);
        return $this;
    }

    protected function getDataProvider()
    {
        if (!$this->dataProvider) {
            $this->addError('dataProvider is not prepared');
            return false;
        }

        return $this->dataProvider;
    }

    protected function getArray()
    {
        if (!$this->dataProvider) {
            $this->addError('dataProvider is not prepared');
            return false;
        }

        return ArrayHelper::toArray($this->dataProvider->getModels());
    }

    public function setMapParams(array $params)
    {
        $this->mapParams = $params;
        return $this;
    }

    public function getMap()
    {
        return ArrayHelper::map(
            $this->dataProvider->getModels(),
            ArrayHelper::getValue($this->mapParams, 0, null),
            ArrayHelper::getValue($this->mapParams, 1, null),
            ArrayHelper::getValue($this->mapParams, 2, null)
        );
    }

    public function prepareWidget($widgetClass, array $params)
    {
        $this->setResultTypeWidget();
        /** @var Widget widget */
        $this->widget = \Yii::createObject([
            'class' => $widgetClass,
            'options' => $params
        ]);
        if (!$this->widget instanceof Widget) {
            $this->addError('Invalid Widget');
            return false;
        }
    }

    protected function getWidget()
    {
        if (!$this->widget) {
            $this->addError('Widget is not prepared');
            return false;
        }

        return $this->widget->run();
    }

    static function className()
    {
        return get_called_class();
    }

    /**
     * @param ActiveRecord $model
     * @param $linkClass
     * @param array $link
     * @return \yii\db\ActiveQuery
     */
    protected function hasOneRelation(ActiveRecord $model, $linkClass, array $link)
    {
        return $model->hasOne($linkClass, $link);
    }

    /**
     * @param $data
     */
    protected function filterData(&$data)
    {
        if (!is_array($data)) $data = HtmlPurifier::process($data);

        array_walk_recursive($data, function (&$item, $key) {
            $item = HtmlPurifier::process($item, [
                'Attr.AllowedFrameTargets' => ['_blank'],
            ]);
        });
    }
}