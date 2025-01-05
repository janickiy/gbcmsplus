<?php

namespace common\components;


use yii\rest\UrlRule;

/**
 * Class RestUrlRule
 *
 * Реализует ту же логику, что и yii\rest\UrlRule, но с динамическими контроллерами
 *
 * В итоге получается паттерн такого вида 'module/version/<controller:[\w\d-]+>',
 * а экшены подставляются автоматически, в зависимости от http метода
 *
 * @package common\components
 */
class RestUrlRule extends UrlRule
{
    /**
     * @var string
     */
    public $module;

    /**
     * @var string
     */
    public $version;

    /**
     * @var string
     */
    public $controllerPattern = '[\w\d-]+';

    /**
     * @var string|array
     * TRICKY в конструкторе родительского класса контроллер должен быть задан, но здесь не используется
     */
    public $controller = true;

    /**
     * @var string
     */
    public $ruleSuffix = '';

    /**
     * @inheritdoc
     */
    protected function createRules()
    {
        $only = array_flip($this->only);
        $except = array_flip($this->except);
        $patterns = $this->extraPatterns + $this->patterns;
        $rules = [];

        $prefix = implode('/', [$this->module, $this->version]); // общий префикс для правил
        $rulePattern = implode('/', [$prefix, '<controller:' . $this->controllerPattern . '>']); // паттерн для текущего пути
        $actionPrefix = implode('/', [$prefix, '<controller>']); // паттерн для роутинга

        foreach ($patterns as $pattern => $action) {
            if (!isset($except[$action]) && (empty($only) || isset($only[$action]))) {
                /** @var \yii\web\UrlRule $rule */
                $rule = $this->createRule($pattern, $rulePattern, $actionPrefix . '/' . $action);
                $rule->suffix = $this->ruleSuffix; // заменяем на строку, чтобы убрать / с конца урла
                $rules[$prefix][] = $rule;
            }
        }

        return $rules;
    }
}