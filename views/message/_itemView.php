<?php

use yii\helpers\Html;
use matacms\widgets\ActiveForm;
use matacms\language\models\I18nMessage;

$messageModel = !empty($model->i18nMessages) ? $model->i18nMessages[0] : new I18nMessage;
if($messageModel->isNewRecord)
    $messageModel->language = $language;

?>

<div class="details-view">
    <div class="i18n-message-form item">

        <?php $form = ActiveForm::begin([
          'action' => ['message/save'],
          ]); ?>
          <div class="row">
            <div class="three columns item-label">
                <?= $model->message ?>
            </div>
            <div class="nine columns info">
                <?= $form->field($messageModel, 'translation')->label(''); ?>
                <button> Save </button>
            </div>
        </div>
        <?= Html::hiddenInput(Html::getInputName($messageModel, 'language'), $messageModel->language) ?>

        <?= Html::hiddenInput(Html::getInputName($messageModel, 'id'), $model->id) ?>

        <?php ActiveForm::end(); ?>

        <?php
        $this->registerJs("
            $('#infinite-list-view').on('blur', 'form #" . Html::getInputId($messageModel, 'translation') . "', function(){
                $(this).parents('form').trigger('beforeSubmit');
                return false;
            });

            $('#infinite-list-view').on('click', 'form button', function(){
                $(this).parents('form').trigger('beforeSubmit');
                return false;
            });

            $('#infinite-list-view').on('beforeSubmit', 'form', function(event, jqXHR, settings) {
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
            });
        ");

    ?>
</div>
</div>
