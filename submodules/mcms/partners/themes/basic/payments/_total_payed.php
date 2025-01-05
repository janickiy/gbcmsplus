<?php

use rgk\utils\components\CurrenciesValues;

/**
 * @var array $currencyIcons [rub => Р, usd => $ ...]
 * @var CurrenciesValues $values
 */
?>

<?php foreach (['rub', 'usd', 'eur'] as $currency) { ?>
  <?php if ($values->getValue($currency)) { ?>
    <p><?= Yii::$app->formatter->asDecimal($values->getValue($currency)) . ' ' . $currencyIcons[$currency];?></p>
  <?php } ?>
<?php } ?>