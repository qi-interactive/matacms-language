<?php

/**
 * @link http://www.matacms.com/
 * @copyright Copyright (c) 2015 Qi Interactive Limited
 * @license http://www.matacms.com/license/
 */

namespace matacms\language\models;

use Yii;
use mata\behaviors\BlameableBehavior;
use mata\behaviors\IncrementalBehavior;

/**
 * This is the model class for table "{{%matacms_language_mapping}}".
 *
 * @property string $Model
 * @property string $ModelId
 * @property string $DocumentId
 * @property string $Language
 * @property string $Grouping
 */
class LanguageMapping extends \mata\db\ActiveRecord {

    public function behaviors() {
       return [];
    }

    public static function tableName()
    {
        return '{{%matacms_language_mapping}}';
    }

    public function rules()
    {
        return [
            [['Model', 'ModelId', 'DocumentId', 'Language', 'Grouping'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'Model' => 'Model',
            'ModelId' => 'Model ID',
            'DocumentId' => 'Document ID',
            'Language' => 'Language',
            'Grouping' => 'Grouping'
        ];
    }
}
