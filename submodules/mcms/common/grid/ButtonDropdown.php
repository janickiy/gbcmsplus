<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace mcms\common\grid;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;


class ButtonDropdown extends \yii\bootstrap\ButtonDropdown
{
    /**
     * Renders the widget.
     */
    public function run()
    {
        // @todo use [[options]] instead of [[containerOptions]] and introduce [[buttonOptions]] before 2.1 release
        Html::addCssClass($this->containerOptions, ['widget' => 'btn-group']);
        $options = $this->containerOptions;
        $tag = ArrayHelper::remove($options, 'tag', 'div');

        return implode("\n", [
            Html::beginTag($tag, $options),
            $this->renderButton(),
            $this->renderDropdown(),
            Html::endTag($tag)
        ]);
    }

}
