<?php

use mcms\common\AdminFormatter;
use mcms\payments\models\Company;
use mcms\payments\models\PartnerCompany;
use mcms\payments\models\UserPayment;
use mcms\statistic\components\mainStat\DataProvider;
use mcms\common\helpers\ArrayHelper;

/**
 * @var UserPayment $userPayment
 * @var PartnerCompany $partnerCompany
 * @var Company $resellerCompany
 * @var DataProvider $statDataProvider
 * @var array $balancesByCountry
 * @var array $compensations
 * @var array $typeLabels
 * @var array $countries
 */

$logoPath = $resellerCompany->logo ? $resellerCompany->getLogoPath() : null;
$statModels = $statDataProvider->getModels();

$totalGross = $totalVat = 0;

$formatter = new AdminFormatter();

?>
<div class="container">
  <table class="table">
    <tr>
      <td colspan="2">
        &nbsp;
      </td>

      <!-- Organization Name / Image -->
      <td align="right">
        <?php if ($logoPath) { ?>
          <img src="<?= $logoPath ?>" width="170px">
        <?php } ?>
      </td>
    </tr>
    <tr class="border">
      <td colspan="3" class="td-title">
        <div class="title-wrapper">
          <p class="title">Self billing / Invoice</p>
        </div>
      </td>
    </tr>
    <tr class="contacts">
      <td colspan="3" class="no-padding">
        <table class="table">
          <tbody>
          <tr>
            <td width="170px" class="no-padding">
              <table class="table">
                <tbody>
                <tr>
                  <td>To:</td>
                </tr>
                <tr>
                  <td class="border"><strong><?= $resellerCompany->name ?></strong></td>
                </tr>
                <tr>
                  <td>
                    <?= $resellerCompany->address ?><br/>
                    <?= $resellerCompany->city ?><br/>
                    <?= $resellerCompany->post_code ?>
                  </td>
                </tr>
                <tr>
                  <td><?= $resellerCompany->country ?></td>
                </tr>
                <tr>
                  <td>Tax code: <?= $resellerCompany->tax_code ?></td>
                </tr>
                </tbody>
              </table>
            </td>
            <td width="100%"></td>
            <td width="170px" class="no-padding">
              <table class="table">
                <tbody>
                <tr>
                  <td>From:</td>
                </tr>
                <tr>
                  <td class="border"><strong><?= $partnerCompany->name ?></strong></td>
                </tr>
                <tr>
                  <td>
                    <?= $partnerCompany->address ?><br/>
                    <?= $partnerCompany->city ?><br/>
                    <?= $partnerCompany->post_code ?>
                  </td>
                </tr>
                <tr>
                  <td><?= $partnerCompany->country ?></td>
                </tr>
                <tr>
                  <td>Tax code: <?= $partnerCompany->tax_code ?></td>
                </tr>
                </tbody>
              </table>
            </td>
          </tr>
          </tbody>
        </table>
      </td>

    </tr>

    <tr class="title border">
      <td colspan="3">
        <strong>Invoice Details</strong>
      </td>
    </tr>
    <tr class="small">
      <td width="170px" class="no-padding">
        <table class="table">
          <tbody>
          <tr>
            <td width="110px">Invoice Number:</td>
            <td><?= Yii::$app->params['invoiceNumberPrefix'] ?><?= $userPayment->id ?></td>
          </tr>
          <tr>
            <td>Currency</td>
            <td><?= mb_strtoupper($userPayment->invoice_currency) ?></td>
          </tr>
          </tbody>
        </table>
      </td>
      <td></td>
      <td width="170px" class="no-padding">
        <table class="table">
          <tbody>
          <tr>
            <td width="110px">Invoice Date:</td>
            <td><?= $formatter->asDate($userPayment->created_at, 'dd/MM/YYYY') ?></td>
          </tr>
          <tr>
            <td>Due Date:</td>
            <td><?= $formatter->asDate($partnerCompany->getDueDate($userPayment->created_at), 'dd/MM/YYYY') ?></td>
          </tr>
          </tbody>
        </table>
      </td>
    </tr>
    <tr>
      <td colspan="3">&nbsp;</td>
    </tr>

    <tr>
      <td colspan="3" class="no-padding">
        <table class="grid">
          <thead>
          <tr>
            <th align="left">Service</th>
            <th>Market</th>
            <th>From</th>
            <th>To</th>
            <th>Ud</th>
            <th>VAT</th>
            <th>Net</th>
            <th align="right">Gross</th>
          </tr>
          </thead>
          <tbody>
          <?php foreach ($balancesByCountry as $countryId => $item): ?>
            <?php
            /** @var \mcms\partners\components\mainStat\Row $model */
            $gross = $item;
            $net = $gross / (1 + $partnerCompany->vat / 100);
            $vat = $gross - $net;
            $totalGross += $gross;
            $totalVat += $vat;

            $model = ArrayHelper::getValue($statModels, $countryId);
            ?>
            <tr>
              <td align="left">Traffic</td>
              <td><?= ArrayHelper::getValue($countries, $countryId, '-') ?></td>
              <td><?= $formatter->asDate($userPayment->from_date, 'dd/MM/YYYY') ?></td>
              <td><?= $formatter->asDate($userPayment->to_date, 'dd/MM/YYYY') ?></td>
              <td><?= $model ? ($model->getRebills() + $model->getCpaOns()) : 1 ?></td>
              <td><?= $formatter->asPercentSimple($partnerCompany->vat) ?></td>
              <td><?= $formatter->asCurrency($net) ?></td>
              <td align="right"><?= $formatter->asCurrency($gross) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php foreach ($compensations as $item): ?>
            <?php
            $gross = $item['amount'];
            $net = $gross / (1 + $partnerCompany->vat / 100);
            $vat = $gross - $net;
            $totalGross += $gross;
            $totalVat += $vat;
            ?>
            <tr>
              <td align="left"><?= ArrayHelper::getValue($typeLabels, $item['type']) ?></td>
              <td>-</td>
              <td><?= $formatter->asDate($item['date'], 'dd/MM/YYYY') ?></td>
              <td><?= $formatter->asDate($item['date'], 'dd/MM/YYYY') ?></td>
              <td>1</td>
              <td><?= $formatter->asPercentSimple($partnerCompany->vat) ?></td>
              <td><?= $formatter->asCurrency($net) ?></td>
              <td align="right"><?= $formatter->asCurrency($gross) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </td>
    </tr>

    <tr class="border">
      <td colspan="3" class="h-80">&nbsp;</td>
    </tr>

    <tr>
      <td colspan="2"></td>
      <td width="170px" class="no-padding">
        <table class="table totals">
          <tbody>
          <tr class="border">
            <td>Net Total</td>
            <td align="right"><?= $formatter->asCurrency($totalGross - $totalVat) ?></td>
          </tr>
          <tr class="border">
            <td>VAT</td>
            <td align="right"><?= $totalVat ? $formatter->asCurrency($totalVat) : '-' ?></td>
          </tr>
          <tr class="border">
            <td><strong>Gross Total</strong></td>
            <td align="right"><strong><?= $formatter->asCurrency($totalGross) ?></strong></td>
          </tr>
          </tbody>
        </table>
      </td>
    </tr>

    <tr>
      <td colspan="3">&nbsp;</td>
    </tr>
    <tr class="title">
      <td class="border">
        <strong>Payment Details</strong>
      </td>
      <td colspan="2"></td>
    </tr>
    <tr class="small">
      <td width="250px" class="no-padding">
        <table class="table">
          <tbody>
          <tr>
            <td width="100px">Entity:</td>
            <td><?= $partnerCompany->bank_entity ?></td>
          </tr>
          <tr>
            <td>Account / IBAN:</td>
            <td><?= $partnerCompany->bank_account ?></td>
          </tr>
          <tr>
            <td>Swift:</td>
            <td><?= $partnerCompany->swift_code ?></td>
          </tr>
          </tbody>
        </table>
      </td>
      <td></td>
    </tr>
  </table>
</div>
