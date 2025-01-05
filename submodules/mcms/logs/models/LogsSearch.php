<?php

namespace mcms\logs\models;

use kak\clickhouse\data\SqlDataProvider as ClickhouseDataProvider;
use kartik\daterange\DateRangeBehavior;
use mcms\common\event\Event;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;


class LogsSearch extends Logs
{
  
  
  public $label;
  public $EventTimeRange;
  public $EventTimeStart;
  public $EventTimeEnd;
  
  public static function primaryKey()
  {
    return 'EventTime';
  }
  
  public function behaviors()
  {
    return [
      [
        'class' => DateRangeBehavior::class,
        'attribute' => 'EventTimeRange',
        'dateStartAttribute' => 'EventTimeStart',
        'dateEndAttribute' => 'EventTimeEnd',
      ]
    ];
  }
  
  /**
   * @inheritdoc
   */
  public function rules()
  {
    return [
      [['EventTimeRange'], 'match', 'pattern' => '/^.+\s\-\s.+$/'],
      [['label'], 'each', 'rule' => ['filter', 'filter' => 'strip_tags']],
      [['label'], 'each', 'rule' => ['filter', 'filter' => 'trim']],
    ];
  }
  
  /**
   * @inheritdoc
   */
  public function scenarios()
  {
    // bypass scenarios() implementation in the parent class
    return Model::scenarios();
  }
  
  /**
   * Creates data provider instance with search query applied
   *
   * @param array $params
   *
   * @return ClickhouseDataProvider
   */
  public function search($params)
  {
    
    $query = Logs::find();
    
    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'key' => 'EventTime',
      'pagination' => [
        'pageSize' => 20,
      ],
      'sort' => [
        'defaultOrder' => ['EventTime' => SORT_DESC]
      ]
    ]);
    
    $this->load($params);
    
    if (!$this->validate()) {
      
      return $dataProvider;
    }
    
    $query->andFilterWhere(['EventLabel' => $this->label]);
    $query->andFilterWhere(['>=', 'EventTime', $this->EventTimeStart]);
    
    if ($this->EventTimeEnd) {
      $this->EventTimeEnd = strtotime("tomorrow", $this->EventTimeEnd) - 1;
      $query->andFilterWhere(['<', 'EventTime', $this->EventTimeEnd]);
    }
    
    return $dataProvider;
    
  }
  
  public function getFilterLabels()
  {
    $labels = Logs::find()->select('EventLabel')->distinct()->indexBy('EventLabel')->asArray()->column();
    
    foreach ($labels as $label) {
      if (!class_exists($label)) {
        $labels[$label] = $label;
        continue;
      }
      
      $eventClassInstance = Yii::$container->get($label);
      if (!$eventClassInstance instanceof Event) continue;
      $labels[$label] = $eventClassInstance->getEventName();
    }
    
    return $labels;
  }
}