<?php

namespace matacms\language\components;

use yii\i18n\MissingTranslationEvent;
use matacms\language\models\I18nSourceMessage;

class TranslationEventHandler
{
    public static function handleMissingTranslation(MissingTranslationEvent $event)
    {
        // var_dump(Yii->sourceLanguage);
        // var_dump($event->category);
        // var_dump($event->message);
        // var_dump($event->language);
        // var_dump($event->sender->sourceLanguage);
        // exit;

        // If the missing translation is for sourceLanguage, then add this translation into matacms_i18n_source_message for specific category
        $sourceMessage = I18nSourceMessage::find()->where(['category' => $event->category, 'message' => $event->message])->one();
        if($sourceMessage == null) {
            $sourceMessage = new I18nSourceMessage;
            $sourceMessage->category = $event->category;
            $sourceMessage->message = $event->message;
            if(!$sourceMessage->save())
                throw new \yii\web\ServerErrorHttpException($sourceMessage->getTopError());
        }
    }
}
