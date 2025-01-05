<?php

return [
  'illegal_params' => 'Required payment parameters are either missing or have invalid values.',
  'illegal_param_label' => 'Invalid value for the label parameter.',
  'illegal_param_to' => 'Invalid value for the to parameter.',
  'illegal_param_amount' => 'Invalid value for the amount parameter.',
  'illegal_param_amount_due' => 'Invalid value for the amount_due parameter.',
  'illegal_param_comment' => 'Invalid value for the comment parameter.',
  'illegal_param_message' => 'Invalid value for the message parameter.',
  'illegal_param_expire_period' => 'Invalid value for the expire_period parameter.',
  'not_enough_funds' => 'The payer\'s account does not have sufficient funds to make the payment. Additional funds should be credited to the account, and a new payment will need to be processed.',
  'payment_refused' => 'The merchant refused to accept the payment (for example, the user tried to purchase an item that is not in stock).',
  'payee_not_found' => 'The transfer recipient was not found. The specified account does not exist, or a phone number or email address was specified that is not linked to a user account or payee.',
  'authorization_reject' => 'Authorization of the payment was refused. Possible reasons:
A transaction with the current parameters is forbidden for this user.
The user did not accept the User Agreement for the Yandex.Money service.',
  'limit_exceeded' => 'One of the operation limits was exceeded:
For the total amount of operations for the access token granted.
For the total amount of operations over a period of time for the access token granted.
Yandex.Money restrictions for various types of operations.',
  'account_blocked' => 'The user\'s account has been blocked. In order to unblock the account, the user must be redirected to the address specified in the account_unblock_uri field.',
  'ext_action_required' => 'This type of payment cannot be made at this time. To be able to make these types of payments, the user must go to the page with the ext_action_uri address and follow the instructions on that page. This may be any of the following actions:
Entering identification data.
Accepting the offer.
Performing other actions according to the instructions.',
  'technical_error' => 'Technical error; repeat the operation again later.',
];
?>
