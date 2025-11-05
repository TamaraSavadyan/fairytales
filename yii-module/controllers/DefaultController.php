<?php

namespace app\modules\story\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use app\modules\story\models\StoryForm;

/**
 * Default controller for the `story` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $model = new StoryForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            return $this->redirect(['result', 'age' => $model->age, 'language' => $model->language, 'characters' => $model->characters]);
        }

        return $this->render('index', [
            'model' => $model,
        ]);
    }

    /**
     * Генерация и отображение сказки
     */
    public function actionResult()
    {
        $model = new StoryForm();
        $model->age = (int)Yii::$app->request->get('age', 6);
        $model->language = Yii::$app->request->get('language', 'ru');
        $model->characters = Yii::$app->request->get('characters', []);

        if (!$model->validate()) {
            throw new BadRequestHttpException('Некорректные параметры запроса.');
        }

        return $this->render('result', [
            'model' => $model,
        ]);
    }

    /**
     * Проксирование запроса к Python API с потоковым ответом
     */
    public function actionStream()
    {
        $age = (int)Yii::$app->request->get('age', 0);
        $language = Yii::$app->request->get('language', '');
        $characters = Yii::$app->request->get('characters', []);
        
        if (!is_array($characters)) {
            $characters = [$characters];
        }
        $characters = array_filter($characters, function($char) {
            return !empty($char) && is_string($char);
        });

        if ($age <= 0 || !in_array($language, ['ru', 'kk']) || empty($characters)) {
            Yii::$app->response->statusCode = 400;
            return json_encode(['error' => 'Некорректные параметры запроса']);
        }

        $postData = json_encode([
            'age' => $age,
            'language' => $language,
            'characters' => is_array($characters) ? $characters : [$characters],
        ]);

        $module = $this->module;
        $apiUrl = rtrim($module->pythonApiUrl, '/') . '/generate_story';
        $timeout = $module->timeout ?? 300;

        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', 'text/markdown; charset=utf-8');
        Yii::$app->response->headers->set('X-Accel-Buffering', 'no');
        Yii::$app->response->headers->set('Cache-Control', 'no-cache');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($postData)
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $data) {
            echo $data;
            flush();
            if (ob_get_level() > 0) {
                ob_flush();
            }
            return strlen($data);
        });

        try {
            if (ob_get_level() > 0) {
                ob_end_flush();
            }
            
            $result = curl_exec($ch);
            
            if ($result === false) {
                $error = curl_error($ch);
                throw new \Exception("Ошибка cURL: " . ($error ?: "Неизвестная ошибка"));
            }
            
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);

            if ($curlError) {
                throw new \Exception("Ошибка cURL: " . $curlError);
            }

            if ($httpCode !== 200) {
                throw new \Exception("Python API вернул код: " . $httpCode);
            }

        } catch (\Exception $e) {
            echo "\n\n**Ошибка:** Не удалось получить ответ от Python API. " . htmlspecialchars($e->getMessage()) . "\n";
            Yii::$app->response->statusCode = 500;
        } finally {
            curl_close($ch);
        }

        return '';
    }
}

