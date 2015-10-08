<?php

/**
 * @link http://www.matacms.com/
 * @copyright Copyright (c) 2015 Qi Interactive Limited
 * @license http://www.matacms.com/license/
 */

namespace matacms\language\controllers;

use Yii;
use matacms\controllers\base\AuthenticatedController;

/**
 * MessagesController implements the CRUD actions for I18nMessage model.
 */
class LanguageController extends AuthenticatedController {

	public function actions()
    {
        return \yii\helpers\ArrayHelper::merge([
			'switch-language' => [
				'class' => 'matacms\language\actions\SwitchLanguageAction'
			]
        ], parent::actions());
    }

	public function getModel() {
		return null;
	}

	public function getSearchModel() {
		return null;
	}

}
