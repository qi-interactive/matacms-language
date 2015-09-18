<?php

/**
 * @link http://www.matacms.com/
 * @copyright Copyright (c) 2015 Qi Interactive Limited
 * @license http://www.matacms.com/license/
 */

namespace matacms\language;

use mata\base\Module as BaseModule;

class Module extends BaseModule {

	public function init() {

		parent::init();

	}

	public function getNavigation() {
		return false;
	}

}
