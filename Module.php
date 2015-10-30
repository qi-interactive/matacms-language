<?php

/**
 * @link http://www.matacms.com/
 * @copyright Copyright (c) 2015 Qi Interactive Limited
 * @license http://www.matacms.com/license/
 */

namespace matacms\language;

use Yii;
use mata\base\Module as BaseModule;
use matacms\settings\models\Setting;
use matacms\language\models\I18nSourceMessage;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;

class Module extends BaseModule {

	public function init() {

		parent::init();

	}

	public function getNavigation() {

		// $categories = ArrayHelper::getColumn(
        //     I18nSourceMessage::find()->select('category')->distinct()->all(),
        //     'category'
        // );
		//
		// $navigation = [];
		//
        // foreach($categories as $category) {
		// 	$navigation[] = [
		// 		'label' => Inflector::humanize($category),
		// 		'url' => "/mata-cms/language/message?category=" . $category,
		// 		'icon' => "/images/module-icon.svg"
		// 	];
        // }

		return "/mata-cms/language/message";
	}

	public function getSupportedLanguages()
	{
		$supportedLanguages = Setting::findValue('SUPPORTED_LANGUAGES');
		return $supportedLanguages != null ? unserialize($supportedLanguages) : [\Yii::$app->language => \Yii::$app->language];
	}

    public static function t($message, $params = [], $language = null)
    {
        return Yii::t('frontend', $message, $params, $language);
    }

}
