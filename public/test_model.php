<?php
// public/test_model.php

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Task.php';

try {
    $task = new Task();

    // Тест 1: Получить все задачи (должен вернуть пустой массив)
    $all = $task->getAll();
    echo "<h3>Все задачи:</h3>";
    echo "<pre>" . json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

    // Тест 2: Создать задачу
    echo "<h3>Создаём задачу:</h3>";
    $newId = $task->create([
        'title'       => 'Тестовая задача',
        'description' => 'Описание для теста',
        'status'      => 'pending'
    ]);
    echo "Создана задача с ID: $newId<br>";

    // Тест 3: Получить созданную задачу
    echo "<h3>Получаем задачу по ID:</h3>";
    $one = $task->getById($newId);
    echo "<pre>" . json_encode($one, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

    // Тест 4: Обновить задачу
    echo "<h3>Обновляем задачу:</h3>";
    $task->update($newId, ['status' => 'in_progress', 'title' => 'Обновлённая задача']);
    $updated = $task->getById($newId);
    echo "<pre>" . json_encode($updated, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

    // Тест 5: Удалить задачу
    echo "<h3>Удаляем задачу:</h3>";
    $deleted = $task->delete($newId);
    echo "Удалено: " . ($deleted ? 'да' : 'нет') . "<br>";

    // Проверка, что задача удалена
    $check = $task->getById($newId);
    echo "Задача существует: " . ($check ? 'да' : 'нет') . "<br>";

} catch (Exception $e) {
    echo "<strong>Ошибка:</strong> " . $e->getMessage();
}