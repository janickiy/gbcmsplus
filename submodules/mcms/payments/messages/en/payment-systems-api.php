<?php

return [
  'available-recipients' => 'Available recipients',
  'require-settings' => 'Payment system for payments is not configured',
  'system-is-configured' => 'Is configured',
  'paysystems-api-not-available-for-currency' => 'Automatic payments are not available for this currency',

  'attribute-id' => 'ID',
  'attribute-name' => 'Name',
  'attribute-code' => 'Code',
  'attribute-currency' => 'Currency',
  'attribute-balance' => 'Balance',

  'attribute-pursesrc' => 'Purse number',
  'attribute-WMKwmFile' => ' File with keys WM Keeper',
  'attribute-WMKwmFilePassword' => 'Password for file with keys WM Keeper',
  'attribute-WMCapitallerId' => 'WM Capitaller WMID (for the balance of purses)',

  'attribute-card' => 'Sender card number',
  'attribute-exp_date' => 'Expiration date',

  'attribute-certificateFile' => 'Certificate file',
  'attribute-certificateKey' => 'Certificate key',
  'attribute-certificatePassword' => 'Key password',

  'get-credentials-paypal' => 'Manual for obtaining the connection parameters to the API {link}',
  'get-api-credentials-paypal' => 'Manual for obtaining the connection parameters to the balance API {link}',
  'get-credentials-paxum' => 'To obtain the connection parameters to the API you need:<br/>
   - register at paxum https://www.paxum.com/payment/register.php?view=views/register.xsl<br>
   - to enable API access (click "Enable API") https://www.paxum.com/payment/apiSettings.php?view=views/apiSettings.xsl<br>
   - will be sent to email code to confirm<br>
   - to copy the code from email and enter it on the page where you clicked "Enable API" and click "Confirm enable"<br>
   - on this page click "Generate New Shared Secret" -> "Continue"<br>
   - by email will be sent to the secret key<br>
   - enter an email address and the received secret key in the form above<br>
   - on the page where is the button "Generate New Shared Secret" is the "Available IPs"; it is necessary to enter the IP address of the server<br>
Ready!',
  'attribute-yandex-money-wallet' => 'Wallet',
  'attribute-yandex-money-client-id' => 'Client ID',
  'attribute-yandex-money-client-secret' => 'OAuth2 client_secret',
  'attribute-yandex-money-access-token' => 'Access token',
  'attribute-yandex-money-scope' => 'Permissions list',
  'attribute-yandex-money-redirect-uri' => 'Redirect URI',

  'get-access-token' => 'Get access token',
  'fill-and-save-settings' => 'Fill and save settings',

  'download-manual' => 'Download manual',

  'settings-apply-to-group' => 'Apply to all currencies API',
  'settings-apply-to-group_hint' => 'If checked, other currencies API settings should be erased by the current currency API settings',
  'settings-apply-to-group_confirm' => 'Other currencies API settings should be erased by the current currency API settings. Continue?',

  // Epayments
  'attribute-partnerId' => 'Partner account ID',
  'attribute-partnerSecret' => 'Secret API access key',
  'attribute-sourcePurse' => 'The purse, from which payments will be made',
  'attribute-payPass' => 'Payment password',

  // Paxum
  'attribute-email' => 'E-mail which was registered on the access to the service',
  'attribute-secretCode' => 'The secret code to access the service',

  // Paypal
  'attribute-clientId' => 'ID for OAuth authorization',
  'attribute-clientSecret' => 'The secret key',
  'attribute-userName' => 'API login',
  'attribute-password' => 'API password',
  'attribute-signature' => 'The signature for connection',
];