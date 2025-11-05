from fastapi import FastAPI, HTTPException
from fastapi.responses import StreamingResponse
from fastapi.middleware.cors import CORSMiddleware
import os
from typing import Generator

from .models import StoryRequest
from .openai_client import OpenAIStoryGenerator

app = FastAPI(
    title="Story Generator API",
    description="API для генерации сказок с использованием OpenAI",
    version="1.0.0"
)

# Настройка CORS для работы с фронтендом
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # В продакшене указать конкретные домены
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)


@app.get("/")
async def root():
    """Корневой endpoint для проверки работоспособности"""
    return {
        "message": "Story Generator API",
        "version": "1.0.0",
        "endpoints": {
            "generate_story": "POST /generate_story"
        }
    }


@app.get("/health")
async def health():
    """Проверка здоровья сервиса"""
    api_key = os.getenv('OPENAI_API_KEY')
    return {
        "status": "healthy",
        "openai_configured": api_key is not None and len(api_key) > 0
    }


@app.post("/generate_story")
async def generate_story(request: StoryRequest):
    """
    Генерирует сказку на основе входных параметров
    
    Args:
        request: StoryRequest с параметрами age, language, characters
        
    Returns:
        StreamingResponse с текстом сказки в формате Markdown
    """
    # Создаём генератор
    try:
        generator = OpenAIStoryGenerator()
    except ValueError as e:
        raise HTTPException(status_code=500, detail=f"Ошибка конфигурации: {str(e)}")
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Ошибка инициализации: {str(e)}")
    
    # Создаём функцию-генератор для StreamingResponse
    def generate() -> Generator[str, None, None]:
        error_occurred = False
        try:
            for chunk in generator.generate_story_stream(
                age=request.age,
                language=request.language,
                characters=request.characters
            ):
                yield chunk
        except ValueError as e:
            error_occurred = True
            yield f"\n\n**Ошибка валидации:** {str(e)}\n"
        except Exception as e:
            error_occurred = True
            error_msg = f"\n\n**Ошибка при генерации сказки:** {str(e)}\n"
            yield error_msg
            # Не можем поднять HTTPException из генератора, поэтому просто возвращаем ошибку в потоке
    
    return StreamingResponse(
        generate(),
        media_type="text/markdown; charset=utf-8",
        headers={
            "X-Accel-Buffering": "no",  # Отключаем буферизацию в Nginx
            "Cache-Control": "no-cache",
            "Connection": "keep-alive"
        }
    )


if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)

