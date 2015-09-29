<?php

/**
 * @link http://www.matacms.com/
 * @copyright Copyright (c) 2015 Qi Interactive Limited
 * @license http://www.matacms.com/license/
 */

namespace matacms\language\controllers;

use Yii;
use matacms\language\models\I18nMessage;
use matacms\language\models\I18nMessageSearch;
use matacms\language\models\I18nSourceMessage;
use matacms\language\models\I18nSourceMessageSearch;
use matacms\controllers\module\Controller;
use yii\data\Sort;
use yii\web\Response;
use yii\web\NotFoundHttpException;

/**
 * MessagesController implements the CRUD actions for I18nMessage model.
 */
class MessageController extends Controller {

	public function actionIndex()
	{
		$searchModel = $this->getSearchModel();
		$searchModel = new $searchModel();

		$supportedLanguages = Yii::$app->getModule('language')->getSupportedLanguages();
	    unset($supportedLanguages[Yii::$app->sourceLanguage]);
	    $queryLanguage = \Yii::$app->request->getQueryParam('language', key($supportedLanguages));

        $dataProvider = $searchModel->searchWithMessagesForLanguage(Yii::$app->request->queryParams, $queryLanguage);

		$sort = new Sort([
			'attributes' => $searchModel->filterableAttributes()
		]);

		if(!empty($sort->orders)) {
			$dataProvider->query->orderBy = null;
		}

		return $this->render("index", [
			'searchModel' => $searchModel,
			'dataProvider' => $dataProvider,
			'sort' => $sort
			]);
	}

	public function actionSave()
	{
		if (\Yii::$app->request->isAjax && \Yii::$app->request->isPost) {

			$model = $this->findI18nMessageModel([$_POST['I18nMessage']['id'], $_POST['I18nMessage']['language']]);

			\Yii::$app->response->format = Response::FORMAT_JSON;

			if($model == null)
				$model = $this->getI18nMessageModel();

			if ($model->load(Yii::$app->request->post()) && $model->save()) {
				echo json_encode(['result' => 'OK']);
				\Yii::$app->end();
			} else {
				echo json_encode(['result' => 'ERROR']);
				\Yii::$app->end();
			}

			// use matacms\language\models\I18nMessage;
			//
			// if ($model->load(Yii::$app->request->post()) && $model->save()) {
			// 	$this->trigger(self::EVENT_MODEL_CREATED, new MessageEvent($model));
			//
			// 	return $this->redirect(['index', reset($model->getTableSchema()->primaryKey) => $model->getPrimaryKey()]);
			// } else {
			// 	return $this->render("create", [
			// 		'model' => $model,
			// 		]);
			// }
			//
            // if (is_array($models)) {
            //     $result = [];
            //     foreach ($models as $model) {
            //         if ($model->load(\Yii::$app->request->post())) {
            //             \Yii::$app->response->format = Response::FORMAT_JSON;
            //             $result = array_merge($result, ActiveForm::validate($model));
            //         }
            //     }
            //     echo json_encode($result);
            //     \Yii::$app->end();
            // } else {
            //     if ($models->load(\Yii::$app->request->post())) {
            //         \Yii::$app->response->format = Response::FORMAT_JSON;
            //         echo json_encode(ActiveForm::validate($models));
            //         \Yii::$app->end();
            //     }
            // }
        }
	}


	public function getModel() {
		return new I18nSourceMessage();
	}

	public function getSearchModel() {
		return new I18nSourceMessageSearch();
	}

	public function getI18nMessageModel() {
		return new I18nMessage();
	}

	protected function findI18nMessageModel($pk) {

		$model = $this->getI18nMessageModel();
		return $model::findOne($pk);

	}
}
