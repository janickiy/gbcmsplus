<?php

namespace mcms\common\helpers;

use Yii;
use yii\helpers\BaseInflector;
use yii\helpers\Url;
use yii\console\Application as ConsoleApplication;

class Html extends \yii\helpers\Html
{
    public static $uniqueIdPrefix = 'block-uq-id-';

    /**
     * Генерирует уникальный идентификатор в рамках сессии.
     * Сделан для создания идентификаторов элементов в DOM.
     * @return string
     */
    public static function getUniqueId()
    {
        return static::$uniqueIdPrefix . Yii::$app->security->generateRandomString(5);
    }

    /**
     * @param string $text
     * @param null $url
     * @param array $options
     * @param string[]|bool $permissions
     * true - не проверять наличие прав, отображать ссылку в любом случае
     * @param bool $returnEmptyString
     * @return string
     */
    public static function a($text, $url = null, $options = [], $permissions = [], $returnEmptyString = true)
    {
        $isAccessGrunted = $permissions === true ? true : static::hasUrlAccess($url, $permissions);

        if (!empty($options['target']) && trim($options['target']) == '_blank') {
            $options['rel'] = 'nofollow noopener';
        }

        if ($isAccessGrunted) {
            return parent::a($text, $url, $options);
        } else {
            return $returnEmptyString ? '' : $text;
        }
    }

    /**
     * @param null $url
     * @param array $permissions
     * @return bool
     */
    public static function hasUrlAccess($url = null, array $permissions = [])
    {
        // В консольных приложениях все запрещаем
        // Понадобилось при генерации выгрузки статы через консольную команду
        if (Yii::$app instanceof ConsoleApplication) {
            return false;
        }
        $isAccessGrunted = true;
        if (count($permissions)) foreach ($permissions as $permission => $params) {
            $isAccessGrunted &= is_numeric($permission)
                ? Yii::$app->getUser()->can($params)
                : Yii::$app->getUser()->can($permission, $params);
        }
        if (!$isAccessGrunted) return false;

        if (self::isIgnoredUrl($url)) return true;

        $checkUrlPart = is_array($url) ? current($url) : $url;
        $checkUrl = Url::to(is_array($checkUrlPart) ?: [$checkUrlPart]);


        if (Yii::$app->urlManager->baseUrl) {
            $baseUrlPos = strpos($checkUrl, Yii::$app->urlManager->baseUrl);
            if ($baseUrlPos !== false) {
                // простой str_replace() не подойдёт, т.к. заменяет все вхождения baseUrl в строку, а нам надо только 1ое совпадение.
                $checkUrl = substr_replace($checkUrl, '', $baseUrlPos, strlen(Yii::$app->urlManager->baseUrl));
            }
        }

        $checkUrl = explode('?', $checkUrl)[0]; // убираем из условия GET параметры

        return Yii::$app->getUser()->can(BaseInflector::camelize($checkUrl));
    }

    public static function getBooleanDropdown()
    {
        return [
            0 => Yii::_t('app.common.No'),
            1 => Yii::_t('app.common.Yes')
        ];
    }

    /**
     * Булева иконка
     * @param bool $value
     * @return string
     */
    public static function booleanIcon($value): string
    {
        return $value ? static::trueIcon() : static::falseIcon();
    }

    /**
     * true иконка
     * @return string
     */
    public static function trueIcon()
    {
        return '<span class="glyphicon glyphicon-ok text-success"></span>';
    }

    /**
     * false иконка
     * @return string
     */
    public static function falseIcon()
    {
        return '<span class="glyphicon glyphicon-remove text-danger"></span>';
    }

    /**
     * @param $url
     * @return bool
     */
    private static function isIgnoredUrl(string $url): bool
    {
        if (!is_string($url)) return false;
        if ($url == '#') return true;
        if (strpos($url, 'javascript:') === 0) return true;
        if (strpos($url, 'http://') === 0) return true;
        return false;
    }

    /**
     * @param string $name
     * @param array $options
     * @return string
     */
    public static function icon(string $name, array $options = []): string
    {
        if (!$name) {
            return '';
        }
        $options['class'] = 'fa fa-' . $name . (!empty($options['class']) ? ' ' . $options['class'] : '');

        return Html::tag('i', '', $options);
    }
}