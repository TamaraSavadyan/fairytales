<?php

namespace app\modules\story\models;

use yii\base\Model;

/**
 * Модель формы для генерации сказки
 */
class StoryForm extends Model
{
    /**
     * @var int Возраст ребёнка
     */
    public $age;

    /**
     * @var string Язык сказки ('ru' или 'kk')
     */
    public $language;

    /**
     * @var array Список персонажей
     */
    public $characters = [];

    /**
     * Список доступных персонажей
     */
    public static function getAvailableCharacters()
    {
        return [
            'ru' => [
                'Колобок' => 'Колобок',
                'Медведь' => 'Медведь',
                'Заяц' => 'Заяц',
                'Волк' => 'Волк',
                'Лиса' => 'Лиса',
            ],
            'kk' => [
                'Алдар Көсе' => 'Алдар Көсе',
                'Әйел Арстан' => 'Әйел Арстан',
                'Қыдыр' => 'Қыдыр',
                'Жез Тікен' => 'Жез Тікен',
                'Түлкі' => 'Түлкі',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['age', 'language', 'characters'], 'required'],
            ['age', 'integer', 'min' => 1, 'max' => 18],
            ['language', 'in', 'range' => ['ru', 'kk']],
            ['characters', 'each', 'rule' => ['string']],
            ['characters', 'validateCharacters'],
        ];
    }

    /**
     * Валидация списка персонажей
     */
    public function validateCharacters($attribute, $params)
    {
        if (empty($this->characters) || !is_array($this->characters)) {
            $this->addError($attribute, 'Необходимо выбрать минимум одного персонажа.');
            return;
        }

        $availableCharacters = self::getAvailableCharacters();
        $validCharacters = $availableCharacters[$this->language] ?? [];

        foreach ($this->characters as $character) {
            if (!in_array($character, $validCharacters)) {
                $this->addError($attribute, "Персонаж '{$character}' недоступен для выбранного языка.");
                return;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'age' => 'Возраст ребёнка',
            'language' => 'Язык',
            'characters' => 'Персонажи',
        ];
    }

    /**
     * Получить название языка
     */
    public function getLanguageName()
    {
        return $this->language === 'ru' ? 'Русский' : 'Казахский';
    }
}

