<?php

/**
* @link http://www.matacms.com/
* @copyright Copyright (c) 2015 Qi Interactive Limited
* @license http://www.matacms.com/license/
*/

namespace matacms\language\behaviors;

use Yii;
use yii\base\Event;
use yii\base\Behavior;
use yii\db\BaseActiveRecord;
use matacms\language\models\LanguageMapping;
use yii\web\ServerErrorHttpException;
use \mata\base\MessageEvent;
use mata\helpers\BehaviorHelper;
use yii\helpers\VarDumper;

class LanguageBehavior extends Behavior {

    private $getLanguageVersionAfterFind = true;

    public $_createVersion = true;

    public $_languageGroup = null;

    public $availableLanguages = "";

    public function events() {

        $events = [
            BaseActiveRecord::EVENT_BEFORE_INSERT => "beforeInsert",
            BaseActiveRecord::EVENT_BEFORE_UPDATE => "beforeUpdate",
            BaseActiveRecord::EVENT_AFTER_FIND => "afterFind",
            BaseActiveRecord::EVENT_AFTER_INSERT => "afterSave",
            BaseActiveRecord::EVENT_AFTER_DELETE => "afterDelete"
        ];

        return $events;
    }

    public function beforeInsert(Event $event)
    {
        $model = $event->sender;
        if($model->hasAttribute('Language'))
            $this->setLanguage($event->sender);
    }

    public function beforeUpdate(Event $event)
    {
        $model = $event->sender;
        if($model->hasAttribute('Language'))
            $this->setLanguage($event->sender);
    }

    public function afterFind(Event $event) {

        // if ((!is_a(Yii::$app, "yii\console\Application") && !is_a(Yii::$app, "matacms\web\Application")) && $this->getLanguageVersionAfterFind && $event->sender->Language != Yii::$app->language) {
        //     $this->getLanguageVersion(Yii::$app->language);
        //     // $this->owner = null;
        //     // $event->sender = null;
        //
        // }
        //
        // if(is_a(Yii::$app, "yii\console\Application") && $event->sender->Language != Yii::$app->language) {
        //     $this->getLanguageVersion(Yii::$app->language);
        // }

        // $languageMapping =  $this->getLanguageMapping();
        //
        // $languageGroup = $languageMapping != null ? $languageMapping->Grouping : null;
        //
        // $this->owner->_languageGroup = $languageGroup;

        // if (is_a(Yii::$app, "matacms\web\Application")) {
        //     $languageVersion = $this->getLanguageVersion(Yii::$app->language);
        //     // $this->owner->_languageVersions = $this->getLanguageVersions();
        // }

    }

    public function afterSave(Event $event) {

        if (is_a(Yii::$app, "matacms\web\Application") && $this->_createVersion) {
            $model = $event->sender;

            if(isset($_GET[$model->formName()]) && isset($_GET[$model->formName()]['languageGroup'])) {
                $grouping = $_GET[$model->formName()]['languageGroup'];
            }
            else {
                $grouping = md5($model->getDocumentId()->getId());
            }

            $languageMapping = new LanguageMapping;
            $languageMapping->attributes = [
                "Model" => $model::className(),
                "ModelId" =>  $model->getDocumentId()->getPk(),
                "DocumentId" => $model->getDocumentId()->getId(),
                "Language" => $model->Language,
                "Grouping" => $grouping
            ];

            if ($languageMapping->save() == false)
                throw new ServerErrorHttpException(VarDumper::dumpAsString($languageMapping->attributes));
        }

    }

    public function afterDelete(Event $event) {
        if(is_a(Yii::$app, "matacms\web\Application")) {
            $model = $event->sender;

            $languageMapping = LanguageMapping::find()->where(['DocumentId' => $model->getDocumentId()->getId(), 'Grouping' => $model->_languageGroup])->one();

            if($languageMapping != null)
                $languageMapping->delete();
        }

    }

    public function noLanguageVersion() {
        $this->getLanguageVersionAfterFind = false;
    }

    public function getLanguageMapping() {

      return LanguageMapping::find()->where([
        "DocumentId" => $this->owner->getDocumentId()->getId()
        ])->one();

    }

    private function setLanguage($model)
	{

		if(empty($model->Language)) {
			$model->Language = Yii::$app->request->get('language') ?: Yii::$app->language;
		}

	}

    public function getAvailableLanguages() {
        if(!empty($this->availableLanguages)) {
            return explode(",", $this->availableLanguages);
        }
        else {
            $mappings = LanguageMapping::find()->where(['Grouping' => $this->getLanguageGrouping()->Grouping])->all();

            $retVal = [];

            foreach($mappings as $mapping)
            $retVal[] = $mapping->Language;

            $this->availableLanguages = implode(",", $retVal);
            return $retVal;
        }

    }

    public function getLanguageVersionId($language)
    {
        return LanguageMapping::find()->where(['Grouping' => $this->getLanguageGrouping()->Grouping, 'Language' => $language])->one();
    }

    public function getLanguageGrouping()
    {
        return LanguageMapping::find()->where(['DocumentId' => $this->owner->getDocumentId()->getId(), 'Language' => $this->owner->Language])->one();
    }


    // TODO TEST THIS!
    public function getLanguageMappings($grouping = null) {
        $grouping = $grouping != null ? $grouping : $this->getLanguageGrouping()->Grouping;
        // TODO: to be refactored with take english if exists or first one available
        return LanguageMapping::find()->where('Grouping = :grouping', [':grouping' => $grouping])->all();
        // return $this->hasMany(LanguageMapping::className(), ['ModelId' => 'Id'])->where(['Grouping' => $this->getLanguageGrouping()->Grouping])->all();
    }

}
