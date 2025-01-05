<?php

namespace mcms\promo\components\provider_instances_sync\api_drivers;

use mcms\promo\components\provider_instances_sync\dto\Error;
use mcms\promo\components\provider_instances_sync\dto\Instance;
use mcms\promo\components\provider_instances_sync\dto\Provider;
use mcms\promo\components\provider_instances_sync\dto\Stream;
use mcms\promo\components\provider_instances_sync\requests\AuthRequest;
use mcms\promo\components\provider_instances_sync\requests\CreateStreamRequest;

interface ApiDriverInterface
{
  public function __construct($instance = null);

  /**
   * Метод авторизации в апи кп
   * @param AuthRequest $request
   * @return string токен авторизации
   */
  public function auth(AuthRequest $request);

  /**
   * Метод для получения потоков юзера из определенного инстанца
   * @return Error|Stream[]
   */
  public function getStreams();

  /**
   * Метод для создания нового потока для юзера
   * @param CreateStreamRequest $request
   * @return Error|Stream
   */
  public function createStream(CreateStreamRequest $request);

  /**
   * Метод для получения провайдеров определенного инстанца
   * @return Error|Provider[]
   */
  public function getProviders();

  /**
   * Метод для получения всех инстанцев, на которые есть доступ у авторизированного в апи пользователя
   * @return Error|Instance[]
   */
  public function getInstances();

  /**
   * Метод для тестирования работоспособности постбеков
   * @param int $streamId Ади тестируемого потока
   * @return boolean
   */
  public function testPostback($streamId);
}