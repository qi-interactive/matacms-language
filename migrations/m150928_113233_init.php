<?php

/**
 * @link http://www.matacms.com/
 * @copyright Copyright (c) 2015 Qi Interactive Limited
 * @license http://www.matacms.com/license/
 */

use yii\db\Schema;
use yii\db\Migration;

class m150928_113233_init extends Migration {

	public function safeUp() {
		$this->createTable('{{%matacms_i18n_source_message}}', [
			'id' => Schema::TYPE_PK,
			'category' => Schema::TYPE_STRING . '(32) NOT NULL',
			'message' => Schema::TYPE_TEXT . ' NOT NULL',
			]);

		$this->createTable('{{%matacms_i18n_message}}', [
			'id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'language' => Schema::TYPE_STRING . '(16) NOT NULL',
			'translation' => Schema::TYPE_TEXT . ' NOT NULL',
			]);

		$this->addPrimaryKey('matacms_i18n_message_pk', '{{%matacms_i18n_message}}', ['id', 'language']);
	    $this->addForeignKey('fk_matacms_i18n_source_message', '{{%matacms_i18n_message}}', 'id', '{{%matacms_i18n_source_message}}', 'id', 'cascade', 'restrict');
	}

	public function safeDown() {
		$this->dropForeignKey('fk_matacms_i18n_source_message', '{{%matacms_i18n_message}}');
        $this->dropTable('{{%matacms_i18n_message}}');
        $this->dropTable('{{%matacms_i18n_source_message}}');
	}
}
