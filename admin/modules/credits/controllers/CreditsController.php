<?php

namespace admin\modules\credits\controllers;

use admin\modules\credits\models\Credit;
use admin\modules\credits\models\credit\CreditApprove;
use admin\modules\credits\models\credit\CreditDecline;
use admin\modules\credits\models\form\CreditForm;
use admin\modules\credits\models\search\CreditTransactionSearch;
use mcms\common\controller\AdminBaseController;
use rgk\utils\actions\CreateModalAction;
use rgk\utils\actions\IndexAction;
use admin\modules\credits\models\search\CreditSearch;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * Кредитики, хлеб и вода
 */
class CreditsController extends AdminBaseController
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'index' => [
                'class' => IndexAction::class,
                'modelClass' => CreditSearch::class,
            ],
            'create-modal' => [
                'class' => CreateModalAction::class,
                'modelClass' => CreditForm::class,
            ],
        ];
    }

    /**
     * Деталка для кредита
     * @param $id
     * @return string
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $transactionsSearchModel = new CreditTransactionSearch([
            'creditId' => $model->id,
            'paysAndFeesOnly' => true,
        ]);

        $transactionsDataProvider = $transactionsSearchModel->search(Yii::$app->request->get());

        return $this->render('view', [
            'model' => $model,
            'transactionsDataProvider' => $transactionsDataProvider,
            'transactionsSearchModel' => $transactionsSearchModel,
        ]);
    }

    /**
     * @param $id
     * @return Credit
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (!$model = Credit::find()->withTransactionsSum()->andWhere(['id' => $id])->one()) {
            throw new NotFoundHttpException();
        }
        return $model;
    }
}
