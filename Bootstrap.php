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

class Bootstrap extends \mata\base\Bootstrap {

	public function bootstrap($app) {

		if (!is_a(\Yii::$app, "yii\console\Application")) {
			$module = \Yii::$app->getModule("language");

			$supportedLanguages = $module->getSupportedLanguages();

			$preferredLanguage = isset($app->request->cookies['language']) ? (string)$app->request->cookies['language'] : null;

	        if (empty($preferredLanguage)) {
	            $preferredLanguage = $app->request->getPreferredLanguage($supportedLanguages);
	        }
			$app->language = $preferredLanguage;
		}

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

		Event::on(ActiveQuery::class, ActiveQuery::EVENT_BEFORE_PREPARE_STATEMENT, function(Event $event) {

			$activeQuery = $event->sender;
			$tableAlias = $activeQuery->getQueryTableName($activeQuery)[0];
			$hasLanguageColumn = $this->hasLanguageColumn($tableAlias);

			if($hasLanguageColumn)
				$this->addLanguageCondition($activeQuery, $tableAlias);
		});

	}

	private function hasLanguageColumn($tableAlias)
	{
		return Yii::$app->getDb()->getTableSchema($tableAlias)->getColumn('Language');
	}

	private function addLanguageCondition($activeQuery, $tableAlias)
	{

		$activeQuery->andWhere("$tableAlias.`Language` = '" . Yii::$app->language . "'");

	}

	private function setLanguage($model)
	{

		if (is_object($model) == false || !$model->hasAttribute('Language'))
			return;

		$language = Yii::$app->language;

		if(empty($model->Language))
			$model->Language = $language;
	}
}
