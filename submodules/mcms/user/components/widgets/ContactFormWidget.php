<?php

namespace mcms\user\components\widgets;

use mcms\user\models\ContactForm;
use yii\base\Widget;

class ContactFormWidget extends Widget
{
    public $landing;

    public $options;

    public function run()
    {
        $form = new ContactForm;

        return $this->render('contact', [
            'model' => $form
        ]);
    }
}