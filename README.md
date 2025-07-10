# Todo List API (Laravel)

# Todo List API (Laravel)

[![Laravel](https://img.shields.io/badge/Laravel-12+-FF2D20?logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?logo=php)](https://php.net)

API для персонального задачника (todo-list) с аутентификацией, управлением задачами, проектами и совместным доступом.

## 📋 Основные возможности

- 🔐 Аутентификация пользователей (регистрация/вход)
- 👤 Управление профилем пользователя
- ✅ Создание и управление задачами
- 📁 Организация задач по проектам
- 🏷️ Тегирование задач
- 👥 Совместный доступ к задачам
- 🔎 Фильтрация и поиск задач

## 🛠️ Установка

### Требования
- PHP 8.1+
- Composer
- MySQL 5.7+
- OpenServer (для разработки)

### Инструкция по установке

1. Клонируйте репозиторий:
   ```bash
    git clone https://github.com/your-repo/todo-api.git
    cd todo-api
   ```

2. Установите зависимости:
   ```bash
    composer install
   ```

3. Настройте окружение:
   ```bash
    cp .env.example .env
    php artisan key:generate
   ```

4. Настройте подключение к БД в файле .env:
   ```bash
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=todo_api
    DB_USERNAME=root
    DB_PASSWORD=
   ```

5. Запустите миграции и сидеры:
   ```bash
    php artisan migrate --seed
   ```

6. Запустите сервер разработки:
   ```bash
    php artisan serve
   ```

7. Запустите модульные тесты:
   ```bash
    php artisan test
   ```

### Настройка для Open Server

В файле .osp/project.ini нужно написать используемую версию PHP.

## API Endpoints

### Аутентификация

POST /api/register - Регистрация нового пользователя

POST /api/login - Вход в систему

GET /api/user - Получение информации о текущем пользователе

### Профиль

GET /api/profile - Получение профиля пользователя

PUT /api/profile - Обновление профиля

### Проекты

GET /api/projects - Список проектов пользователя

### Задачи

GET /api/tasks - Список задач пользователя (с фильтрацией)

Параметры:

project_id - фильтр по проекту

tag - фильтр по тегу

search - поиск по названию/описанию

## Примеры запросов

### Регистрация
   ```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "secret",
    "password_confirmation": "secret"
  }'
   ```

### Вход
   ```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "secret"
  }'
   ```

### Получение информации о пользователе
   ```bash
curl -X GET http://todo-api.local/api/user \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
   ```

### Обновление профиля
   ```bash
curl -X PUT http://todo-api.local/api/profile \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d "{\"bio\":\"This is a test bio\",\"phone\":\"1234567890\",\"address\":\"Test Address\"}"
   ```

## Особенности реализации

- Использованы Eloquent Resources для форматирования ответов

- Реализованы все требуемые связи между моделями

- Поддержка soft delete для задач

- Фильтрация задач по тегам и проектам

- Поиск по названию/описанию задач
