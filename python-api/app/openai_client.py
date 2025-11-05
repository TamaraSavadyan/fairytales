import os
from openai import OpenAI
from typing import Iterator, Generator
from datetime import datetime


class OpenAIStoryGenerator:
    """Класс для генерации сказок через OpenAI API"""
    
    def __init__(self, api_key: str = None):
        """
        Инициализация клиента OpenAI
        
        Args:
            api_key: API ключ OpenAI. Если не указан, берётся из OPENAI_API_KEY
        """
        self.api_key = api_key or os.getenv('OPENAI_API_KEY')
        if not self.api_key:
            raise ValueError("OPENAI_API_KEY не найден в переменных окружения")
        self.client = OpenAI(api_key=self.api_key)
    
    def generate_prompt(self, age: int, language: str, characters: list[str]) -> str:
        """
        Формирует prompt для OpenAI API
        
        Args:
            age: Возраст ребёнка
            language: Язык ('ru' или 'kk')
            characters: Список персонажей
            
        Returns:
            Сформированный prompt
        """
        language_name = "русском" if language == "ru" else "казахском"
        characters_str = ", ".join(characters)
        
        prompt = (
            f"Напиши сказку на {language_name} языке для ребёнка возраста {age} лет, "
            f"с персонажами: {characters_str}. "
            f"Сказка должна быть подходящей по возрасту, интересной и поучительной. "
            f"Используй формат Markdown с заголовками и абзацами."
        )
        
        return prompt
    
    def generate_story_stream(self, age: int, language: str, characters: list[str]) -> Generator[str, None, None]:
        """
        Генерирует сказку через OpenAI API с потоковым ответом
        
        Args:
            age: Возраст ребёнка
            language: Язык ('ru' или 'kk')
            characters: Список персонажей
            
        Yields:
            Части текста сказки по мере генерации
        """
        prompt = self.generate_prompt(age, language, characters)
        
        language_name = "русский" if language == "ru" else "казахский"
        characters_str = ", ".join(characters)
        
        # Формируем заголовок
        header = f"# Сказка для {age}-летнего ребёнка\n\n"
        header += f"**Язык:** {language_name}\n\n"
        header += f"**Персонажи:** {characters_str}\n\n\n"
        
        yield header
        
        try:
            # Вызов OpenAI API с потоковым ответом
            stream = self.client.chat.completions.create(
                model="gpt-4o-mini",  # Можно изменить на gpt-4 или другой модель
                messages=[
                    {"role": "system", "content": "Ты опытный рассказчик детских сказок. Пиши увлекательные, добрые и поучительные истории, подходящие для указанного возраста."},
                    {"role": "user", "content": prompt}
                ],
                stream=True,
                temperature=0.7,
                max_tokens=2000
            )
            
            # Читаем поток
            for chunk in stream:
                if chunk.choices[0].delta.content is not None:
                    yield chunk.choices[0].delta.content
            
            # Добавляем подвал
            footer = f"\n\n---\n_Сказка сгенерирована: {datetime.utcnow().isoformat()}Z_\n"
            yield footer
            
        except Exception as e:
            error_msg = f"\n\n**Ошибка генерации:** {str(e)}\n"
            yield error_msg
            raise

