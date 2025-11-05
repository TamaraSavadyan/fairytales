<?php

namespace app\modules\story;

/**
 * Story module definition class
 */
class StoryModule extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\story\controllers';

    /**
     * URL Python API сервиса
     * @var string
     */
    public $pythonApiUrl = 'http://localhost:8000';

    /**
     * Таймаут для запросов к Python API (в секундах)
     * @var int
     */
    public $timeout = 300;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // Кастомная инициализация модуля
    }
}

