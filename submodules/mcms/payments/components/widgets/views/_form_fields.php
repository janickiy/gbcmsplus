<?php

?>

<?php
  foreach ($model->getForm($form)->createAdminFormFields() as $field) {
    echo $field;
  }
?>

