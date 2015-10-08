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

class LanguageBehavior extends Behavior {

    private $getLanguageVersionAfterFind = true;

    public $_createVersion = true;

    public $_languageGroup = null;

    public function events() {

        $events = [
            BaseActiveRecord::EVENT_AFTER_FIND => "afterFind",
            BaseActiveRecord::EVENT_AFTER_INSERT => "afterSave",
            BaseActiveRecord::EVENT_AFTER_DELETE => "afterDelete"
        ];

        return $events;
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
            $grouping = Yii::$app->request->get('languageGroup') ?: md5($model->getDocumentId()->getId());

            $languageMapping = new LanguageMapping;
            $languageMapping->attributes = [
                "Model" => $model::className(),
                "ModelId" =>  $model->getDocumentId()->getPk(),
                "DocumentId" => $model->getDocumentId()->getId(),
                "Language" => $model->Language,
                "Grouping" => $grouping
            ];

            if ($languageMapping->save() == false)
              throw new ServerErrorHttpException($languageMapping->getTopError());
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

}
