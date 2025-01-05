<?php

Yii::$container->set(\mcms\common\form\AjaxActiveForm::class, [
  'usePartnerScripts' => true,
]);
Yii::$container->set(\mcms\common\form\AjaxActiveKartikForm::class, [
  'usePartnerScripts' => true,
]);

Yii::$container->set(
  \mcms\statistic\components\mainStat\mysql\groupFormats\ArbitraryLinks::class,
  \mcms\partners\components\mainStat\groupFormats\ArbitraryLinks::class
);
Yii::$container->set(
  \mcms\statistic\components\mainStat\mysql\groupFormats\WebmasterSources::class,
  \mcms\partners\components\mainStat\groupFormats\WebmasterSources::class
);
Yii::$container->set(
  \mcms\statistic\components\mainStat\mysql\groupFormats\Landings::class,
  \mcms\partners\components\mainStat\groupFormats\Landings::class
);
Yii::$container->set(
  \mcms\statistic\components\mainStat\mysql\groupFormats\Streams::class,
  \mcms\partners\components\mainStat\groupFormats\Streams::class
);
Yii::$container->set(
  \mcms\statistic\components\mainStat\mysql\groupFormats\Platforms::class,
  \mcms\partners\components\mainStat\groupFormats\Platforms::class
);
Yii::$container->set(
  \mcms\statistic\components\mainStat\mysql\groupFormats\Operators::class,
  \mcms\partners\components\mainStat\groupFormats\Operators::class
);
Yii::$container->set(
  \mcms\statistic\components\mainStat\mysql\groupFormats\Dates::class,
  \mcms\partners\components\mainStat\groupFormats\Dates::class
);
