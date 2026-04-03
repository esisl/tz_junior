<?php
// public/test_response.php

require_once __DIR__ . '/../src/Response.php';

// Тест 1: Успешный ответ
Response::json(['message' => 'Всё работает'], 200);

// Тест 2: Ошибка валидации (раскомментируйте для проверки)
// Response::validationError('Поле title обязательно');

// Тест 3: Не найдено (раскомментируйте для проверки)
// Response::notFound('Задача не найдена');