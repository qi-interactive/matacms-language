<?php

/**
* @link http://www.matacms.com/
* @copyright Copyright (c) 2015 Qi Interactive Limited
* @license http://www.matacms.com/license/
*/

namespace matacms\language;

use Yii;
use yii\base\Event;
use yii\db\BaseActiveRecord;
use mata\base\MessageEvent;
use mata\db\ActiveQuery;
use yii\web\HttpException;
use yii\helpers\VarDumper;
use mata\helpers\BehaviorHelper;
use mata\language\models\LanguageMapping;

class Bootstrap extends \mata\base\Bootstrap {

	public function bootstrap($app) {

		if (!is_a(\Yii::$app, "yii\console\Application")) {
			$module = \Yii::$app->getModule("language");

			$supportedLanguages = array_keys($module->getSupportedLanguages());

			$preferredLanguage = isset($app->request->cookies['language']) ? (string)$app->request->cookies['language'] : null;

			if (empty($preferredLanguage)) {
				$preferredLanguage = $app->request->getPreferredLanguage($supportedLanguages);
			}
			$app->language = $preferredLanguage;
		}

		Event::on(ActiveQuery::class, ActiveQuery::EVENT_BEFORE_PREPARE_STATEMENT, function(Event $event) {

			$activeQuery = $event->sender;
			$modelClass = $activeQuery->modelClass;

			$sampleModelObject = new $modelClass;

			if (!is_a(\Yii::$app, "yii\console\Application") && !is_a(\Yii::$app, "matacms\web\Application") && BehaviorHelper::hasBehavior($sampleModelObject, \matacms\language\behaviors\LanguageBehavior::class)) {

				$modelId = $sampleModelObject->getDocumentId()->getPk();
				$documentIdBase = $sampleModelObject->getDocumentId()->getId();
				$tableAlias = $activeQuery->getQueryTableName($activeQuery)[0];

				if (count($modelClass::primaryKey()) > 1) {
					throw new HttpException(500, sprintf("Composite keys are not handled yet. Table alias is %s", $tableAlias));
				}

				$activeQuery = $this->addLanguageQuery($activeQuery, $modelClass, $tableAlias);


			}
		});

		Event::on(BaseActiveRecord::class, BaseActiveRecord::EVENT_BEFORE_INSERT, function(Event $event) {
			$model = $event->sender;
			$hasLanguageColumn = $this->hasLanguageColumn($model::tableName());
			if($hasLanguageColumn)
			$this->setLanguage($event->sender);
		});

		Event::on(BaseActiveRecord::class, BaseActiveRecord::EVENT_BEFORE_UPDATE, function(Event $event) {
			$model = $event->sender;
			$hasLanguageColumn = $this->hasLanguageColumn($model::tableName());
			if($hasLanguageColumn)
			$this->setLanguage($event->sender);
		});

	}

	private function addLanguageQuery($activeQuery, $modelClass, $tableAlias) {

	    $tablePrimaryKey = $modelClass::primaryKey()[0];

	    if($activeQuery->where != null) {


	        $modelClass = str_replace("\\", "\\\\\\",  $modelClass);

	        $languageQuery = new \yii\db\Query();
	        $languageQuery->from = [$tableAlias];
	        $languageQuery->select = ['target.ModelId'];
	        $languageQuery->join = [
	            ['INNER JOIN', 'matacms_language_mapping', 'matacms_language_mapping.ModelId = ' . $tableAlias . '.' . $tablePrimaryKey],
	            ['INNER JOIN', 'matacms_language_mapping target', "target.Grouping = matacms_language_mapping.Grouping and target.Language = '" . Yii::$app->language . "' and target.Model = '" . $modelClass . "'"]
	        ];

			$activeQueryWhere = $activeQuery->initialWhere;

			// Yii::info('INITIAL WHERE:: ' . \yii\helpers\VarDumper::dumpAsString($activeQueryWhere));

			if(is_array($activeQueryWhere)) {
				$activeQueryWhere = $this->isAssociativeArray($activeQueryWhere) ? $activeQueryWhere : $activeQueryWhere;
			}

	        $languageQuery->where = $activeQueryWhere;

			// var_dump($languageQuery->where);

			// exit;

			if(is_array($languageQuery->where) && $this->isAssociativeArray($languageQuery->where)) {
				$newWhere = [];
				foreach($languageQuery->where as $column => $param) {
					$newWhere[$tableAlias . '.' . $column] = $param;
				}
				$languageQuery->where = $newWhere;
			}



	        // Yii::info(\yii\helpers\VarDumper::dumpAsString($activeQuery->where[1]));
	        // Yii::info('WHERE:: ' . \yii\helpers\VarDumper::dumpAsString($languageQuery->where));

	        $langaugeQuerySql = $languageQuery->createCommand()->sql;

	        // Yii::info(\yii\helpers\VarDumper::dumpAsString($languageQuery->createCommand()->params));

	        // Yii::info(\yii\helpers\VarDumper::dumpAsString($langaugeQuerySql));

	        $activeQuery->params = array_merge($activeQuery->params, $languageQuery->createCommand()->params);
	        if (is_array($activeQuery->where)) {

				if($this->isAssociativeArray($activeQuery->where)) {
					$activeQuery->where = $tablePrimaryKey . " IN (" . $langaugeQuerySql . ")";
				}
				else {
					$activeQuery->where[1] = $tablePrimaryKey . " IN (" . $langaugeQuerySql . ")";
				}

	        } else {

	            $activeQuery->where = $tablePrimaryKey . " IN (" . $langaugeQuerySql . ")";
	        }

			// var_dump($activeQuery->where);

			// exit;

	    }

		return $activeQuery;
	}

	private function isAssociativeArray($array)
	{
		return (array_values($array) !== $array);
	}

	private function hasLanguageColumn($tableAlias)
	{
		return Yii::$app->getDb()->getTableSchema($tableAlias)->getColumn('Language');
	}


	private function setLanguage($model)
	{

		if (is_object($model) == false || !$model->hasAttribute('Language'))
			return;

		$language = Yii::$app->language;

		if(empty($model->Language)) {
			$model->Language = $language;
		}

	}
}
