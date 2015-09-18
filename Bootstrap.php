<?php

/**
 * @link http://www.matacms.com/
 * @copyright Copyright (c) 2015 Qi Interactive Limited
 * @license http://www.matacms.com/license/
 */

namespace matacms\language;

use Yii;
use yii\base\Event;
use mata\base\MessageEvent;
use yii\db\BaseActiveRecord;
use mata\db\ActiveQuery;
use yii\web\HttpException;

class Bootstrap extends \mata\base\Bootstrap {

	public function bootstrap($app) {

		// Event::on(BaseActiveRecord::class, BaseActiveRecord::EVENT_BEFORE_INSERT, function(Event $event) {
		// 	$this->setLanguage($event->sender);
		// });
		//
		// Event::on(BaseActiveRecord::class, BaseActiveRecord::EVENT_BEFORE_UPDATE, function(Event $event) {
		// 	$this->setLanguage($event->sender);
		// });

		Event::on(ActiveQuery::class, ActiveQuery::EVENT_BEFORE_PREPARE_STATEMENT, function(Event $event) {

			$activeQuery = $event->sender;

			Yii::info('activeQuery: ' . \yii\helpers\VarDumper::dumpAsString($activeQuery));
			// $modelClass = $activeQuery->modelClass;
			// $sampleModelObject = new $modelClass;
			// $documentIdBase = $sampleModelObject->getDocumentId()->getId();
			// $tableAlias = $activeQuery->getQueryTableName($activeQuery)[0];
			//
			// if (count($modelClass::primaryKey()) > 1) {
			// 	throw new HttpException(500, sprintf("Composite keys are not handled yet. Table alias is %s", $tableAlias));
			// }
			//
			// $tablePrimaryKey = $modelClass::primaryKey()[0];
			//
			// $this->addLanguageCondition($activeQuery, "CONCAT('" . $documentIdBase . "', " . $tableAlias . "." . $tablePrimaryKey . ")", $documentIdBase);
		});

	}

	private function addItemEnvironmentJoin($activeQuery, $documentId, $documentIdBase) {

		$hasEnvironmentRecords = ItemEnvironment::find()->where(['like', 'DocumentId', $documentIdBase])->limit(1)->one();

		if ($hasEnvironmentRecords == null)
			return;

		$alias = $this->getTableAlias();

		// TODO This encoding happens in Yii, use what they're offering. E.g. it is used in the call on line 91
		$documentId = str_replace("\\", "\\\\\\",  $documentId);

		if ($activeQuery->select == null)
			$activeQuery->addSelect(["*", $alias . ".Revision"]);
		else
			$activeQuery->addSelect($alias . ".Revision");

		$activeQuery->innerJoin("matacms_itemenvironment " . $alias, "`" . $alias . "`.`DocumentId` = " . $documentId);

		 // TODO: refactor and use Query!
		 if (Yii::$app->user->isGuest) {
		 	$liveEnvironment = $module->getLiveEnvironment();

		 	$activeQuery->andWhere($alias . ".Revision = (SELECT Revision FROM matacms_itemenvironment " . $alias . "rev WHERE . " . $alias . "rev.`DocumentId` = " . $alias . ".DocumentId
		 	 			 AND " . $alias . "rev.`Status` = '" . $liveEnvironment . "' ORDER BY " . $alias . ".Revision DESC LIMIT 1)");
	 	} else {
	 		$activeQuery->andWhere($alias . ".Revision = (SELECT Revision FROM matacms_itemenvironment " . $alias . "rev WHERE " . $alias . "rev.`DocumentId` = " . $alias . ".DocumentId
	 			 			 ORDER BY " . $alias . ".Revision DESC LIMIT 1)");

	 	}
	}

	private function setLanguage($model) {

		if (is_object($model) == false || !$model->hasAttribute('Language'))
			return;

		$language = Yii::$app->language;

		$model->Language = $language;
	}
}
