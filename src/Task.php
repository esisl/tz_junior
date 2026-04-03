<?php
// src/Task.php

class Task
{
private PDO $db;

    public function __construct()
    {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }

    /**
     * Получить все задачи
     *
     * @return array Массив задач
     */
    public function getAll(): array
    {
        $sql = "SELECT id, title, description, status, created_at, updated_at 
                FROM tasks 
                ORDER BY created_at DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Получить одну задачу по ID
     *
     * @param int $id ID задачи
     * @return array|null Данные задачи или null если не найдена
     */
    public function getById(int $id): ?array
    {
        $sql = "SELECT id, title, description, status, created_at, updated_at 
                FROM tasks 
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $task = $stmt->fetch();
        return $task ?: null;
    }

    /**
     * Создать новую задачу
     *
     * @param array $data Данные задачи (title, description, status)
     * @return int ID созданной задачи
     * @throws InvalidArgumentException При ошибке валидации
     */
    public function create(array $data): int
    {
        // Валидация данных
        if (empty(trim($data['title'] ?? ''))) {
            throw new InvalidArgumentException('Title is required');
        }

        $title = trim($data['title']);
        $description = trim($data['description'] ?? '');
        $status = $data['status'] ?? 'pending';

        // Валидация статуса
        $validStatuses = ['pending', 'in_progress', 'completed'];
        if (!in_array($status, $validStatuses, true)) {
            throw new InvalidArgumentException('Invalid status value');
        }

        // Ограничение длины title
        if (mb_strlen($title) > 255) {
            throw new InvalidArgumentException('Title must be no more than 255 characters');
        }

        $sql = "INSERT INTO tasks (title, description, status, updated_at) 
                VALUES (:title, :description, :status, :updated_at)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'title'       => $title,
            'description' => $description ?: null,
            'status'      => $status,
            'updated_at'  => date('Y-m-d H:i:s'), // Заполняем DATETIME вручную
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Обновить задачу
     *
     * @param int $id ID задачи
     * @param array $data Данные для обновления
     * @return bool Успешность обновления
     * @throws InvalidArgumentException При ошибке валидации
     */
    public function update(int $id, array $data): bool
    {
        // Проверяем существование задачи
        $existing = $this->getById($id);
        if ($existing === null) {
            throw new InvalidArgumentException('Task not found');
        }

        // Валидация title (если передан)
        if (array_key_exists('title', $data)) {
            if (empty(trim($data['title']))) {
                throw new InvalidArgumentException('Title cannot be empty');
            }
            if (mb_strlen($data['title']) > 255) {
                throw new InvalidArgumentException('Title must be no more than 255 characters');
            }
        }

        // Валидация статуса (если передан)
        if (array_key_exists('status', $data)) {
            $validStatuses = ['pending', 'in_progress', 'completed'];
            if (!in_array($data['status'], $validStatuses, true)) {
                throw new InvalidArgumentException('Invalid status value');
            }
        }

        // Формируем динамический запрос UPDATE
        $fields = [];
        $values = ['id' => $id];

        if (array_key_exists('title', $data)) {
            $fields[] = 'title = :title';
            $values['title'] = trim($data['title']);
        }

        if (array_key_exists('description', $data)) {
            $fields[] = 'description = :description';
            $values['description'] = trim($data['description']);
        }

        if (array_key_exists('status', $data)) {
            $fields[] = 'status = :status';
            $values['status'] = $data['status'];
        }

        // Всегда обновляем updated_at
        $fields[] = 'updated_at = :updated_at';
        $values['updated_at'] = date('Y-m-d H:i:s');

        if (empty($fields)) {
            return true; // Нечего обновлять
        }

        $sql = "UPDATE tasks SET " . implode(', ', $fields) . " WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Удалить задачу
     *
     * @param int $id ID задачи
     * @return bool Успешность удаления
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM tasks WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }
}