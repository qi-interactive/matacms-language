<?php \yii\bootstrap\Modal::begin([
    'header' => '<h3>Select Language</h3>',
    'id' => 'language-selector-modal'
    ]);

    $originalCreateURL = $createURL;

    $supportedLanguages = Yii::$app->getModule('language')->getSupportedLanguages();
    foreach($supportedLanguages as $locale => $language):
        $createURL = strpos($originalCreateURL, '?') ? $originalCreateURL . '&language=' . $locale : $originalCreateURL . '?language=' . $locale;
    ?>
    <a class="hi-icon-effect-2" href="<?= $createURL ?>">
        <div class="inner-container row">
            <div class="five columns">
                <div class="hi-icon hi-icon-cog"></div>
            </div>
            <div class="seven columns">
                <span><?= $language ?></span>
            </div>
        </div>
    </a>
    <?php
    endforeach;
    ?>



<?php \yii\bootstrap\Modal::end(); ?>
