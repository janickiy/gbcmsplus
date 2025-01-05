<?php
use mcms\common\SystemLanguage;

$currentLang = (new SystemLanguage())->getCurrent();
echo $data[0]->text->{$currentLang};
