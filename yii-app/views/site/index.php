<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Генератор сказок';
?>
<div class="site-index">
    <div class="jumbotron">
        <h1>Добро пожаловать!</h1>
        <p class="lead">Генератор сказок с использованием искусственного интеллекта</p>
        <p>
            <?= Html::a('Создать сказку', ['story/default/index'], ['class' => 'btn btn-lg btn-success']) ?>
        </p>
    </div>
</div>

