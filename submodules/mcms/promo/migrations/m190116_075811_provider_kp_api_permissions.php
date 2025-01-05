<?php

use console\components\Migration;
use rgk\utils\traits\PermissionTrait;

/**
*/
class m190116_075811_provider_kp_api_permissions extends Migration
{
  use PermissionTrait;

  /**
   * @return bool|void
   * @throws \yii\base\Exception
   */
  public function up()
  {
    $this->createPermission('PromoProvidersGetInstances', 'Получение всех инстанцев по апи', 'PromoProvidersController', ['root', 'admin']);
    $this->createPermission('PromoProvidersGetProviders', 'Получение провайдеров в инстанце', 'PromoProvidersController', ['root', 'admin']);
    $this->createPermission('PromoProvidersGetStreams', 'Получение списка потоков юзера в инстанце', 'PromoProvidersController', ['root', 'admin']);
    $this->createPermission('PromoProvidersCreateStream', 'Создание потока в инстанце для юезар', 'PromoProvidersController', ['root', 'admin']);
    $this->createPermission('PromoProvidersCollectKpFormData', 'Формирование значений для формы провайдера KP', 'PromoProvidersController', ['root', 'admin']);
    $this->createPermission('PromoProvidersTestProvider', 'Тестирование провайдера', 'PromoProvidersController', ['root', 'admin']);
  }

  /**
  */
  public function down()
  {
    $this->removePermission('PromoProvidersGetInstances');
    $this->removePermission('PromoProvidersGetProviders');
    $this->removePermission('PromoProvidersGetStreams');
    $this->removePermission('PromoProvidersCreateStream');
    $this->removePermission('PromoProvidersCollectKpFormData');
    $this->removePermission('PromoProvidersTestProvider');
  }
}
