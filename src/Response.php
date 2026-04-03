<?php
// src/Response.php

class Response
{
    /**
     * Отправить JSON-ответ
     *
     * @param mixed $data Данные для ответа
     * @param int $statusCode HTTP статус код
     * @param string|null $error Сообщение об ошибке (если есть)
     * @return void
     */
    public static function json($data, int $statusCode = 200, ?string $error = null): void
    {
        // Очищаем буфер вывода на случай, если что-то было выведено раньше
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Устанавливаем HTTP статус
        http_response_code($statusCode);

        // Устанавливаем заголовки
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff'); // Защита от MIME-sniffing

        // Формируем тело ответа
        $body = [
            'success' => $statusCode >= 200 && $statusCode < 300,
            'data'    => $data,
            'error'   => $error,
        ];

        // Выводим JSON
        echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Отправить ответ об ошибке валидации
     *
     * @param string $message Сообщение об ошибке
     * @return void
     */
    public static function validationError(string $message): void
    {
        self::json(null, 400, $message);
    }

    /**
     * Отправить ответ "Не найдено"
     *
     * @param string $message Сообщение (опционально)
     * @return void
     */
    public static function notFound(string $message = 'Resource not found'): void
    {
        self::json(null, 404, $message);
    }

    /**
     * Отправить ответ об ошибке сервера
     *
     * @param string $message Сообщение об ошибке
     * @return void
     */
    public static function serverError(string $message = 'Internal server error'): void
    {
        self::json(null, 500, $message);
    }

    /**
     * Отправить ответ "Успешно удалено" (без тела ответа)
     *
     * @return void
     */
    public static function noContent(): void
    {
        http_response_code(204);
        exit;
    }
}