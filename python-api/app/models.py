from pydantic import BaseModel, Field, field_validator
from typing import List


class StoryRequest(BaseModel):
    age: int = Field(..., gt=0, description="Возраст ребёнка (должен быть больше 0)")
    language: str = Field(..., description="Язык сказки: 'ru' или 'kk'")
    characters: List[str] = Field(..., min_length=1, description="Список персонажей (минимум 1)")

    @field_validator('language')
    @classmethod
    def validate_language(cls, v: str) -> str:
        if v not in ['ru', 'kk']:
            raise ValueError('Язык должен быть либо "ru", либо "kk"')
        return v

    @field_validator('characters')
    @classmethod
    def validate_characters(cls, v: List[str]) -> List[str]:
        if not v or len(v) == 0:
            raise ValueError('Список персонажей должен содержать минимум один элемент')
        # Фильтруем пустые строки
        filtered = [char.strip() for char in v if char.strip()]
        if not filtered:
            raise ValueError('Список персонажей должен содержать минимум один непустой элемент')
        return filtered

