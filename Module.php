<?php

/**
 * @link http://www.matacms.com/
 * @copyright Copyright (c) 2015 Qi Interactive Limited
 * @license http://www.matacms.com/license/
 */

namespace matacms\language;

use mata\base\Module as BaseModule;
use matacms\settings\models\Setting;

class Module extends BaseModule {

	public function init() {

		parent::init();

	}

	public function getNavigation() {
		return false;
	}

	public function getSupportedLanguages()
	{
		$supportedLanguages = Setting::findValue('SUPPORTED_LANGUAGES');
		return $supportedLanguages != null ? unserialize($supportedLanguages) : [\Yii::$app->language];
	}

}
