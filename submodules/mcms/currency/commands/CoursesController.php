<?php

namespace mcms\currency\commands;

use mcms\currency\components\UpdateCourses;
use yii\console\Controller;

class CoursesController extends Controller
{
  /**
   * Обновляет курсы
   */
  public function actionIndex()
  {
    (new UpdateCourses())->execute();
  }


}