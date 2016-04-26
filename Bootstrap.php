<?php

/**
* @link http://www.matacms.com/
* @copyright Copyright (c) 2015 Qi Interactive Limited
* @license http://www.matacms.com/license/
*/

namespace matacms\language;

use Yii;
use yii\base\Event;
use yii\db\Query;
use yii\db\BaseActiveRecord;
use mata\base\MessageEvent;
use mata\db\ActiveQuery;
use yii\web\HttpException;
use mata\helpers\BehaviorHelper;
use mata\language\models\LanguageMapping;

class Bootstrap extends \mata\base\Bootstrap {

	public function bootstrap($app) {

		if (!is_a(\Yii::$app, "yii\console\Application")) {
			$module = \Yii::$app->getModule("language");

			if(!$module)
				return;

			$supportedLanguages = array_keys($module->getSupportedLanguages());

			$preferredLanguage = isset($app->request->cookies['language']) ? (string)$app->request->cookies['language'] : null;

			if (empty($preferredLanguage)) {
				$preferredLanguage = $app->request->getPreferredLanguage($supportedLanguages);
			}
			$app->language = $preferredLanguage;
		}

		Event::on(BaseActiveRecord::class, BaseActiveRecord::EVENT_INIT, function(Event $event) {
			$model = $event->sender;
			if ($model->hasAttribute('Language') && !is_a($model, "matacms\language\models\LanguageMapping")) {
				$model->attachBehavior('language', [
					'class' => \matacms\language\behaviors\LanguageBehavior::className()
				]);
			}
		});

		Event::on(ActiveQuery::class, ActiveQuery::EVENT_BEFORE_PREPARE_STATEMENT, function(Event $event) {

			$activeQuery = $event->sender;
			$modelClass = $activeQuery->modelClass;

			// Handle requests coming from Search models
			$isSearchModel = substr_compare($modelClass, 'Search', -6, 6) === 0;
			if ($isSearchModel) {
				$modelClass = substr($modelClass, 0, -6);
			}

			$sampleModelObject = new $modelClass;

			if(BehaviorHelper::hasBehavior($sampleModelObject, \matacms\language\behaviors\LanguageBehavior::class)) {
				if (!is_a(\Yii::$app, "yii\console\Application") && !is_a(\Yii::$app, "matacms\web\Application")) {

					$modelId = $sampleModelObject->getDocumentId()->getPk();
					$documentIdBase = $sampleModelObject->getDocumentId()->getId();
					$tableAlias = $activeQuery->getQueryTableName($activeQuery)[0];

					if (count($modelClass::primaryKey()) > 1) {
						throw new HttpException(500, sprintf("Composite keys are not handled yet. Table alias is %s", $tableAlias));
					}

					$activeQuery = $this->addLanguageQuery($activeQuery, $modelClass, $tableAlias);

				}

				else if(is_a(\Yii::$app, "matacms\web\Application") && $isSearchModel) {
					$activeQuery = $this->partitionByLanguage($activeQuery);
				}
			}


		});

	}

	private function addLanguageQuery($activeQuery, $modelClass, $tableAlias) {

		$tablePrimaryKey = $modelClass::primaryKey()[0];

		if($activeQuery->where != null) {


			$modelClass = str_replace("\\", "\\\\",  $modelClass);

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

	private function partitionByLanguage($query) {

        $englishSubquery = new Query();
        $englishInnerQuery = new Query();

        $parentClass = $query->modelClass;
        $parentObj = new $parentClass;

        $parentClassModelPath = $parentObj->getDocumentId()->getIdNoPk();
        $parentClassModelPath = str_replace("\\", "\\\\", $parentClassModelPath);



        $tableAlias = $query->getQueryTableName($query);
        $tableAlias = is_string($tableAlias[0]) ? $tableAlias[0] : $tableAlias[1];




        $englishInnerQuery->select("mapping.Grouping, l1.Id")
        ->from(sprintf("%s as l1", $tableAlias))
        ->innerJoin("matacms_language_mapping mapping", "mapping.ModelId = l1.Id and mapping.Model = '" . $parentClassModelPath . "'")
        ->where("l1.Language = 'en-US'");


        $englishSubquery->select(sprintf("%s.*,  x.Grouping", $tableAlias))
        ->from($tableAlias)
        ->innerJoin(["x" => $englishInnerQuery], sprintf('x.Id = %s.Id', $tableAlias));


        $foreignLanguageSubquery = new Query();
        $foreignLanguageInnerQuery = new Query();

        $foreignLanguageInnerQuery->select("mapping.Grouping, l2.Id")
        ->from(sprintf("%s as l2", $tableAlias))
        ->innerJoin("matacms_language_mapping mapping", "mapping.ModelId = l2.Id and mapping.Model = '" . $parentClassModelPath . "'")
        ->where("l2.Language <> 'en-US'");



        $foreignLanguageSubquery->select(sprintf("%s.*,  y.Grouping", $tableAlias))
        ->from($tableAlias)
        ->innerJoin(["y" => $englishInnerQuery], sprintf('y.Id = %s.Id', $tableAlias));


        $englishSubquery->union = [["query" => $foreignLanguageSubquery, "all" => true]];

        $availableLanguages = new Query();
        $availableLanguages->select("GROUP_CONCAT(Language) availableLanguages, availableLanguages.Grouping availableGrouping")
        ->from("matacms_language_mapping availableLanguages")
        ->groupBy("availableLanguages.Grouping");

        $query->select(sprintf("%s.*, availableLanguages.availableLanguages", $tableAlias))
        ->from([$tableAlias => $englishSubquery])
        ->leftJoin(["availableLanguages" => $availableLanguages], sprintf("AvailableGrouping = %s.Grouping", $tableAlias));
// ->groupBy("Grouping");

        // echo $query->createCommand()->sql;
        // exit;

		return $query;

    }

	private function isAssociativeArray($array)
	{
		return (array_values($array) !== $array);
	}

}
