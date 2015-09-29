<?php

/**
 * @link http://www.matacms.com/
 * @copyright Copyright (c) 2015 Qi Interactive Limited
 * @license http://www.matacms.com/license/
 */

namespace matacms\language\actions;

use Yii;


class SwitchLanguageAction extends \yii\base\Action
{

	public function run()
	{

		$language = $this->getLanguage();
		$this->setLanguage($language);

		return $this->controller->redirect(Yii::$app->request->referrer);

	}

	private function getLanguage()
	{
		if(!Yii::$app->request->getIsPost())
			throw new \yii\web\MethodNotAllowedHttpException('Method not allowed');

		$language = Yii::$app->request->getBodyParam('language');

		if($language == null)
			throw new \yii\web\HttpException('Languge has to be set');

		$module = \Yii::$app->getModule("language");

		$supportedLanguages = $module->getSupportedLanguages();

		if(!array_key_exists($language, $supportedLanguages))
			throw new \yii\web\HttpException('Languge is not supported');

		return $language;

	}

	private function setLanguage($language)
	{
		$languageCookie = new \yii\web\Cookie([
			'name' => 'language',
			'value' => $language,
			'expire' => time() + 60 * 60 * 24 * 30, // 30 days
		]);
		Yii::$app->response->cookies->add($languageCookie);
		Yii::$app->language = $language;
	}

}
