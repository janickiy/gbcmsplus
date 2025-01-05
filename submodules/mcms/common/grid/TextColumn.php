<?php

namespace mcms\common\grid;

use kartik\grid\DataColumn;
use mcms\common\helpers\Html;
use Yii;
use yii\bootstrap\Modal;

/**
 * Колонка с текстом.
 *
 * Возможности:
 * - укорачивание текста
 * - просмотр текста в модальном окне
 */
class TextColumn extends DataColumn
{
    /** @var int Длина текста для отображения */
    public $maxSize = 100;
    /** @var bool Просмотр в модальном окне */
    public $isShowModal = true;
    /** @var array Конфигурация модального окна */
    public $modalConfig = [];
    private $mergedModalConfig = [];

    public function init()
    {
        // Скрипт не поддерживает отображение укороченного html текста
        $this->format = 'text';
    }

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $this->mergedModalConfig = array_merge([
            'header' => $model->getAttributeLabel($this->attribute),
            'toggleButton' => ['tag' => 'a', 'label' => Yii::_t('commonMsg.main.full-text'), 'class' => 'btn btn-default btn-xs'],
            'options' => ['id' => Html::getUniqueId()],
        ], $this->modalConfig);

        $text = strip_tags($model->{$this->attribute});

        if ($this->maxSize && mb_strlen($text, 'UTF-8') > $this->maxSize) {
            $text = $this->trimText($text);

            if ($this->isShowModal) {
                $text .= '<br>' . $this->generateModal($model->{$this->attribute});
            }
        }

        return $text;
    }

    /**
     * Сгенерировать модальное окно для просмотра текста
     * @param $originalText
     * @return string Кнопка для открытия модального окна
     */
    protected function generateModal($originalText)
    {
        ob_start();
        Modal::begin($this->mergedModalConfig);
        echo $originalText;
        Modal::end();
        return ob_get_clean();
    }

    /**
     * Обрезать текст
     * @param string $text
     * @return string
     */
    protected function trimText($text)
    {
        return mb_substr($text, 0, $this->maxSize, 'UTF-8') . '...';
    }
}