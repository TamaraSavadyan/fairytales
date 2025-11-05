<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\modules\story\models\StoryForm;

$this->title = 'Генератор сказок';
$this->params['breadcrumbs'][] = $this->title;

$availableCharacters = StoryForm::getAvailableCharacters();
?>

<div class="story-default-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <p class="lead">Заполните форму ниже, чтобы сгенерировать персональную сказку для ребёнка.</p>

    <?php $form = ActiveForm::begin([
        'id' => 'story-form',
        'options' => ['class' => 'form-horizontal'],
    ]); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'age')->textInput([
                'type' => 'number',
                'min' => 1,
                'max' => 18,
                'placeholder' => 'Введите возраст от 1 до 18'
            ]) ?>

            <?= $form->field($model, 'language')->dropDownList([
                'ru' => 'Русский',
                'kk' => 'Казахский'
            ], [
                'prompt' => 'Выберите язык',
                'id' => 'language-select'
            ]) ?>

            <?php
            $script = "
            $('#language-select').on('change', function() {
                var language = $(this).val();
                var characters = {
                    'ru': " . json_encode($availableCharacters['ru']) . ",
                    'kk': " . json_encode($availableCharacters['kk']) . "
                };
                var html = '';
                if (characters[language]) {
                    $.each(characters[language], function(key, value) {
                        html += '<div class=\"checkbox\"><label><input type=\"checkbox\" name=\"StoryForm[characters][]\" value=\"' + value + '\"> ' + value + '</label></div>';
                    });
                }
                $('#characters-container').html(html);
            });
            
            // Инициализация при загрузке страницы
            $(document).ready(function() {
                $('#language-select').trigger('change');
            });
            ";
            $this->registerJs($script, \yii\web\View::POS_READY);
            ?>

            <div class="form-group">
                <label class="control-label"><?= $model->getAttributeLabel('characters') ?></label>
                <div id="characters-container" class="well" style="max-height: 200px; overflow-y: auto;">
                    <p class="text-muted">Сначала выберите язык</p>
                </div>
                <?php if ($model->hasErrors('characters')): ?>
                    <div class="help-block text-danger">
                        <?= implode('<br>', $model->getErrors('characters')) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <?= Html::submitButton('Сгенерировать сказку', ['class' => 'btn btn-primary btn-lg']) ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">Информация</h3>
                </div>
                <div class="panel-body">
                    <p><strong>Как это работает:</strong></p>
                    <ul>
                        <li>Выберите возраст ребёнка (от 1 до 18 лет)</li>
                        <li>Выберите язык сказки</li>
                        <li>Выберите одного или нескольких персонажей</li>
                        <li>Нажмите "Сгенерировать сказку"</li>
                    </ul>
                    <p class="text-muted">
                        <small>Сказка будет сгенерирована с использованием искусственного интеллекта и будет подходить по возрасту и языку.</small>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<style>
.story-default-index {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}
.form-horizontal .control-label {
    text-align: left;
}
#characters-container .checkbox {
    margin-top: 5px;
    margin-bottom: 5px;
}
</style>

