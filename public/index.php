<?php
// public/index.php

// Отключаем вывод ошибок в браузер (в продакшене ошибки нужно логировать в файл)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Заголовки для CORS и безопасности
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// Обработка preflight-запросов (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Подключаем классы
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Task.php';
require_once __DIR__ . '/../src/Response.php';

// Получаем метод запроса и URI
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

// Убираем query-параметры из URI
$path = parse_url($requestUri, PHP_URL_PATH);

// Убираем базовый путь (если API находится в поддиректории)
// Для корня сайта оставляем как есть
$basePath = '/tz_junior/public'; // Измените на пустую строку '', если проект в корне
$path = str_replace($basePath, '', $path);

// Нормализуем путь (убираем двойные слеши, конечные слеши)
$path = rtrim($path, '/');
$path = preg_replace('/\/+/', '/', $path);

// Разбираем путь на части
$segments = explode('/', trim($path, '/'));

// Ожидаем, что первый сегмент - это 'tasks' или пустой (корень API)
$resource = $segments[0] ?? '';
$id = $segments[1] ?? null;

// Валидация ID (должен быть числом, если указан)
if ($id !== null && !is_numeric($id)) {
    Response::validationError('Invalid task ID');
}

$id = $id !== null ? (int) $id : null;

// Инициализируем модель
$taskModel = new Task();

try {
    // Маршрутизация
    switch ($method) {
        case 'GET':
            if ($resource === 'tasks') {
                if ($id !== null) {
                    // GET /tasks/{id} - получить одну задачу
                    $task = $taskModel->getById($id);
                    if ($task === null) {
                        Response::notFound('Task not found');
                    }
                    Response::json($task, 200);
                } else {
                    // GET /tasks - получить все задачи
                    $tasks = $taskModel->getAll();
                    Response::json($tasks, 200);
                }
            } else {
                Response::notFound('Unknown resource');
            }
            break;

        case 'POST':
            if ($resource === 'tasks' && $id === null) {
                // POST /tasks - создать задачу
                $data = getJsonInput();

                // Проверка, что title передан
                if (!isset($data['title'])) {
                    Response::validationError('Title is required');
                }

                $newId = $taskModel->create($data);
                $newTask = $taskModel->getById($newId);
                Response::json($newTask, 201);
            } else {
                Response::validationError('Invalid POST request');
            }
            break;

        case 'PUT':
            if ($resource === 'tasks' && $id !== null) {
                // PUT /tasks/{id} - обновить задачу
                $data = getJsonInput();

                if (empty($data)) {
                    Response::validationError('Request body cannot be empty');
                }

                $taskModel->update($id, $data);
                $updatedTask = $taskModel->getById($id);
                Response::json($updatedTask, 200);
            } else {
                Response::validationError('Invalid PUT request');
            }
            break;

        case 'DELETE':
            if ($resource === 'tasks' && $id !== null) {
                // DELETE /tasks/{id} - удалить задачу
                $deleted = $taskModel->delete($id);
                if (!$deleted) {
                    Response::notFound('Task not found');
                }
                Response::noContent();
            } else {
                Response::validationError('Invalid DELETE request');
            }
            break;

        default:
            Response::validationError('Method not allowed');
    }
} catch (InvalidArgumentException $e) {
    // Ошибки валидации из модели
    Response::validationError($e->getMessage());
} catch (Exception $e) {
    // Остальные ошибки
    error_log('API Error: ' . $e->getMessage());
    Response::serverError('Internal server error');
}

/**
 * Получить JSON из тела запроса
 *
 * @return array
 */
function getJsonInput(): array
{
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    // Если JSON не валидный
    if (json_last_error() !== JSON_ERROR_NONE) {
        Response::validationError('Invalid JSON format');
    }

    return $data ?? [];
}