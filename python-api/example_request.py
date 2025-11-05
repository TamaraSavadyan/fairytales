#!/usr/bin/env python3
"""
Пример использования Python API для генерации сказок
"""

import requests
import json
import sys

def generate_story(age, language, characters, api_url="http://localhost:8000"):
    """
    Генерирует сказку через API
    
    Args:
        age: Возраст ребёнка
        language: Язык ('ru' или 'kk')
        characters: Список персонажей
        api_url: URL API сервиса
    """
    url = f"{api_url}/generate_story"
    
    data = {
        "age": age,
        "language": language,
        "characters": characters
    }
    
    print(f"Отправка запроса к {url}")
    print(f"Параметры: {json.dumps(data, ensure_ascii=False, indent=2)}")
    print("\n" + "="*50)
    print("Сказка:\n")
    print("="*50 + "\n")
    
    try:
        response = requests.post(
            url,
            json=data,
            stream=True,
            timeout=300
        )
        
        response.raise_for_status()
        
        for chunk in response.iter_content(chunk_size=None, decode_unicode=True):
            if chunk:
                print(chunk, end='', flush=True)
        
        print("\n" + "="*50)
        print("\nГенерация завершена!")
        
    except requests.exceptions.RequestException as e:
        print(f"Ошибка при запросе: {e}", file=sys.stderr)
        if hasattr(e.response, 'text'):
            print(f"Ответ сервера: {e.response.text}", file=sys.stderr)
        sys.exit(1)


if __name__ == "__main__":
    if len(sys.argv) > 1:
        import argparse
        
        parser = argparse.ArgumentParser(description='Генератор сказок')
        parser.add_argument('--age', type=int, required=True, help='Возраст ребёнка')
        parser.add_argument('--language', choices=['ru', 'kk'], required=True, help='Язык')
        parser.add_argument('--characters', nargs='+', required=True, help='Персонажи')
        parser.add_argument('--api-url', default='http://localhost:8000', help='URL API')
        
        args = parser.parse_args()
        generate_story(args.age, args.language, args.characters, args.api_url)
    else:
        print("Использование без параметров - запуск примера\n")
        generate_story(
            age=6,
            language="kk",
            characters=["Алдар Көсе", "Әйел Арстан"]
        )

