<?php

use yii\helpers\Html;
use matacms\widgets\ActiveForm;
use matacms\language\models\I18nMessage;

$messageModel = !empty($model->i18nMessages) ? $model->i18nMessages[0] : new I18nMessage;
if($messageModel->isNewRecord)
    $messageModel->language = $language;

?>

<div class="i18n-message-form">

    <?php $form = ActiveForm::begin([
		'action' => ['message/save'],
		]); ?>

    <?= $form->field($messageModel, 'translation')->label($model->message) ?>

    <?= Html::hiddenInput(Html::getInputName($messageModel, 'language'), $messageModel->language) ?>

    <?= Html::hiddenInput(Html::getInputName($messageModel, 'id'), $model->id) ?>

    <?php ActiveForm::end(); ?>

    <?php
    $this->registerJs("
        $('#" . $form->id . " #" . Html::getInputId($messageModel, 'translation') . "').on('blur', function(){
            $('#" . $form->id . "').trigger('submit');
        });
        $('#" . $form->id . "').on('beforeSubmit', function(event, jqXHR, settings) {
            var form = $(this);
            if(form.find('.has-error').length) {
                return false;
            }
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(data) {
                    console.log(data)
                },
                error: function(data) {
                    console.log(data)
                }
            });
            return false;
        });");

    ?>

</div>