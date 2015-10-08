<?php

/**
 * @link http://www.matacms.com/
 * @copyright Copyright (c) 2015 Qi Interactive Limited
 * @license http://www.matacms.com/license/
 */

use yii\db\Schema;
use yii\db\Migration;

class m151005_120503_add_language_mapping extends Migration {

	public function safeUp() {
		$this->createTable('{{%matacms_language_mapping}}', [
			'Model' => Schema::TYPE_STRING . '(64) NOT NULL',
			'ModelId' => Schema::TYPE_INTEGER . '(11) NOT NULL',
			'DocumentId' => Schema::TYPE_STRING . '(64) NOT NULL',
			'Language' => Schema::TYPE_STRING . '(16) NOT NULL',
			'Grouping' => Schema::TYPE_STRING . '(32) NOT NULL',
			]);

	    $this->addPrimaryKey('PK_Key', '{{%matacms_language_mapping}}', ['Model', 'ModelId']);
		$this->createIndex('DocumentId', '{{%matacms_language_mapping}}', 'DocumentId');
		$this->createIndex('Grouping', '{{%matacms_language_mapping}}', 'Grouping');
	}

	public function safeDown() {
        $this->dropTable('{{%matacms_language_mapping}}');
	}
}
