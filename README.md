Простой REST API для управления списком задач, реализованный на PHP 7.4 без использования фреймворков.

## 📋 Требования

| Компонент | Версия | Примечание |
|-----------|--------|------------|
| ОС | CentOS 7 | ⚠️ EOL с июня 2024 |
| PHP | 7.4 | ⚠️ Не поддерживается с ноября 2022 |
| MariaDB | 5.5.68 | ⚠️ Устаревшая версия (ограничение на TIMESTAMP) |
| Git | Любой | Для контроля версий |

> **Внимание:** Данный стек технологий предназначен **только для учебных целей** или работы с устаревшими системами. Для новых проектов используйте актуальные версии (PHP 8.2+, MariaDB 10+).

## 🚀 Установка

### 1. Клонирование репозитория

```bash
cd /var/www/html
git clone git@github.com:esisl/tz_junior.git
```

### 2. Настройка базы данных

Подключитесь к MariaDB от имени root:

```bash
mysql -u root -p
```

Выполните SQL-команды:

```sql
-- Создание базы данных
CREATE DATABASE IF NOT EXISTS tz_junior 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Создание пользователя
CREATE USER 'tz_junior'@'localhost' IDENTIFIED BY 'strong_password';

-- Предоставление прав
GRANT ALL PRIVILEGES ON tz_junior.* TO 'tz_junior'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Создание таблицы

```sql
USE tz_junior;

CREATE TABLE tasks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL,
    
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

> **Примечание:** Поле `updated_at` имеет тип `DATETIME`, а не `TIMESTAMP`, из-за ограничения MariaDB 5.5 (только одно поле TIMESTAMP с авто-обновлением).

## 📡 API Endpoints

Базовый URL: `http://ваш-сервер/tasks`

| Метод | URL | Описание | Статусы |
|-------|-----|----------|---------|
| `GET` | `/tasks` | Получить список всех задач | `200` |
| `GET` | `/tasks/{id}` | Получить одну задачу по ID | `200`, `404` |
| `POST` | `/tasks` | Создать новую задачу | `201`, `400` |
| `PUT` | `/tasks/{id}` | Обновить задачу | `200`, `400`, `404` |
| `DELETE` | `/tasks/{id}` | Удалить задачу | `204`, `404` |

## 📤 Формат запросов и ответов

### Заголовки

```
Content-Type: application/json
```

### Тело запроса (POST / PUT)

```json
{
    "title": "Название задачи",
    "description": "Описание задачи (опционально)",
    "status": "pending"
}
```

**Валидация полей:**

| Поле | Тип | Обязательное | Ограничения |
|------|-----|--------------|-------------|
| `title` | string | ✅ Да | 1-255 символов |
| `description` | string | ❌ Нет | Макс. 65535 символов |
| `status` | enum | ❌ Нет | `pending`, `in_progress`, `completed` |

### Формат ответа

**Успешный ответ:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "title": "Название задачи",
        "description": "Описание",
        "status": "pending",
        "created_at": "2026-04-03 14:30:00",
        "updated_at": "2026-04-03 14:30:00"
    },
    "error": null
}
```

**Ответ с ошибкой:**
```json
{
    "success": false,
    "data": null,
    "error": "Сообщение об ошибке"
}
```

## 🧪 Примеры запросов (curl)

### Получить все задачи
```bash
curl -X GET http://localhost/tasks
```

### Создать задачу
```bash
curl -X POST http://localhost/tasks \
  -H "Content-Type: application/json" \
  -d '{"title":"Купить молоко","status":"pending"}'
```

### Обновить задачу
```bash
curl -X PUT http://localhost/tasks/1 \
  -H "Content-Type: application/json" \
  -d '{"status":"in_progress"}'
```

### Удалить задачу
```bash
curl -X DELETE http://localhost/tasks/1
```

## 🔒 Безопасность

| Мера | Реализация |
|------|------------|
| SQL-инъекции | PDO с подготовленными выражениями |
| XSS | Заголовки `X-Content-Type-Options: nosniff` |
| Утечка конфига | `config/database.php` в `.gitignore` |
| Права на файлы | Владелец `apache`, права `750` на конфиги |
| SELinux | Контекст `httpd_sys_content_t` для веб-корня |

## ⚠️ Известные ограничения

1.  **MariaDB 5.5:** Только одно поле `TIMESTAMP` с авто-обновлением. `updated_at` заполняется через PHP.
2.  **Без фреймворка:** Нет встроенной защиты от CSRF, rate limiting, сложной валидации.
3.  **Деплой:** Обновление через `git pull` может привести к простою. Рекомендуется использовать CI/CD.
4.  **PHP 7.4:** Нет поддержки типовых объявлений PHP 8+, нет JIT-компиляции.

## 📁 Структура проекта

```
tz_junior/
├── config/
│   └── database.php          # Настройки БД (не в git)
├── src/
│   ├── Database.php          # Подключение к БД (Singleton)
│   ├── Task.php              # Модель задач (CRUD + валидация)
│   └── Response.php          # Класс для JSON-ответов
├── public/
│   ├── .htaccess             # URL Rewriting
│   └── index.php             # Front Controller
├── .gitignore
└── README.md
```

## 📝 Лицензия

Учебный проект. Свободное использование.

---

**Автор:** esisl  
**Дата создания:** 2026
**Статус:** Учебный проект (не для продакшена)
