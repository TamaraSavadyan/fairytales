<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\story\models\StoryForm;

$this->title = 'Результат генерации сказки';
$this->params['breadcrumbs'][] = ['label' => 'Генератор сказок', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Преобразуем массив characters в формат для URL
$charactersParam = is_array($model->characters) ? $model->characters : [$model->characters];
$charactersUrl = '';
foreach ($charactersParam as $char) {
    $charactersUrl .= '&characters[]=' . urlencode($char);
}
$streamUrl = Url::to(['stream', 'age' => $model->age, 'language' => $model->language]) . $charactersUrl;
?>

<div class="story-default-result">
    <div class="row">
        <div class="col-md-12">
            <h1><?= Html::encode($this->title) ?></h1>

            <div class="alert alert-info">
                <strong>Параметры:</strong><br>
                Возраст: <?= Html::encode($model->age) ?> лет<br>
                Язык: <?= Html::encode($model->getLanguageName()) ?><br>
                Персонажи: <?= Html::encode(implode(', ', $model->characters)) ?>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Сказка</h3>
                </div>
                <div class="panel-body">
                    <div id="story-content" style="min-height: 400px; font-size: 16px; line-height: 1.8;">
                        <div class="text-center">
                            <i class="glyphicon glyphicon-refresh spinning"></i>
                            <p>Генерация сказки...</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <?= Html::a('Создать новую сказку', ['index'], ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    </div>
</div>

<?php
// JavaScript для потоковой загрузки и отображения
$script = <<<JS
(function() {
    var storyContent = document.getElementById('story-content');
    var streamUrl = '{$streamUrl}';
    
    // Используем Fetch API для потокового чтения
    fetch(streamUrl, {
        method: 'GET',
        headers: {
            'Accept': 'text/markdown',
        }
    })
    .then(function(response) {
        if (!response.ok) {
            throw new Error('HTTP error! status: ' + response.status);
        }
        
        var reader = response.body.getReader();
        var decoder = new TextDecoder('utf-8');
        var buffer = '';
        
        function readChunk() {
            return reader.read().then(function(result) {
                if (result.done) {
                    // Убираем индикатор загрузки если есть
                    var loadingDiv = storyContent.querySelector('.text-center');
                    if (loadingDiv) {
                        loadingDiv.remove();
                    }
                    return;
                }
                
                buffer += decoder.decode(result.value, { stream: true });
                
                // Разбиваем на строки для обработки
                var lines = buffer.split('\\n');
                buffer = lines.pop(); // Последняя строка может быть неполной
                
                // Добавляем содержимое
                lines.forEach(function(line) {
                    if (line.trim()) {
                        appendMarkdown(line + '\\n');
                    }
                });
                
                // Продолжаем чтение
                return readChunk();
            });
        }
        
        return readChunk();
    })
    .catch(function(error) {
        storyContent.innerHTML = '<div class="alert alert-danger"><strong>Ошибка:</strong> ' + 
            escapeHtml(error.message) + '</div>';
    });
    
    function appendMarkdown(text) {
        // Убираем индикатор загрузки при первом появлении контента
        var loadingDiv = storyContent.querySelector('.text-center');
        if (loadingDiv && text.trim()) {
            loadingDiv.remove();
            storyContent.innerHTML = '';
        }
        
        // Простое преобразование Markdown в HTML
        var html = convertMarkdownToHtml(text);
        
        // Создаём временный элемент для вставки
        var temp = document.createElement('div');
        temp.innerHTML = html;
        
        while (temp.firstChild) {
            storyContent.appendChild(temp.firstChild);
        }
        
        // Скроллим вниз
        window.scrollTo(0, document.body.scrollHeight);
    }
    
    function convertMarkdownToHtml(text) {
        // Простое преобразование Markdown (можно заменить на библиотеку)
        var html = escapeHtml(text);
        html = html.replace(/^# (.*)$/gm, '<h1>$1</h1>');
        html = html.replace(/^## (.*)$/gm, '<h2>$1</h2>');
        html = html.replace(/^### (.*)$/gm, '<h3>$1</h3>');
        html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
        html = html.replace(/^---$/gm, '<hr>');
        html = html.replace(/^_/gm, '<em>');
        html = html.replace(/_$/gm, '</em>');
        html = html.replace(/\\n/g, '<br>');
        return html;
    }
    
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
})();
JS;

$this->registerJs($script, \yii\web\View::POS_READY);
?>

<style>
.story-default-result {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}
.spinning {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
#story-content {
    font-family: 'Georgia', serif;
}
#story-content h1 {
    color: #2c3e50;
    border-bottom: 2px solid #3498db;
    padding-bottom: 10px;
}
#story-content h2 {
    color: #34495e;
    margin-top: 30px;
}
#story-content p {
    margin-bottom: 15px;
}
#story-content strong {
    color: #27ae60;
}
</style>

