<p style="text-align: center">
  <a href="https://github.com/dedermus/open-admin-core">
    <img src="https://open-admin.org/gfx/logo.png" alt="open-admin" style="height:200px;background:transparent;">
  </a>
</p>

<p style="text-align: center">⛵<code>open-admin-core</code> - это конструктор административного интерфейса для Laravel, который поможет вам создать CRUD-функции всего с помощью нескольких строк кода.</p>

<p style="text-align: center">Данный конструктор является форком проекта <a href="https://github.com/open-admin-org/open-admin" target="_blank">https://github.com/open-admin-org/open-admin</a> и адаптирован для Bootstrap 5.3.</p>

<p style="text-align: center">
  <a href="https://github.com/dedermus/open-admin-core">Документация</a> |
  <a href="https://github.com/dedermus/open-admin-core">Расширения</a>
</p>

<p style="text-align: center">
    <a href="https://packagist.org/packages/dedermus/open-admin-core">
        <img src="https://img.shields.io/packagist/l/dedermus/open-admin-core.svg?maxAge=2592000&&style=flat-square" alt="Packagist">
    </a>
    <a href="https://packagist.org/packages/dedermus/open-admin-core">
        <img src="https://img.shields.io/packagist/dt/dedermus/open-admin-core.svg?style=flat-square" alt="Total Downloads">
    </a>
    <a href="https://gitlab.com/dedermus/open-admin-core">
        <img src="https://img.shields.io/badge/Awesome-Laravel-brightgreen.svg?style=flat-square" alt="Awesome Laravel">
    </a>
</p>

---

## Требования

- PHP ^8.2
- Laravel >= 12.0
- Fileinfo PHP Extension
- База данных: MySQL 8.0+ или PostgreSQL 12+

---

## Установка

### 1. Создание нового проекта Laravel

```bash
composer create-project laravel/laravel example-app
cd example-app
```
### 2. Настройка приложения

Отредактируйте файл `config/app.php`:

```php
'url' => env('APP_URL', null),
'asset_url' => env('ASSET_URL', null),
'timezone' => 'Europe/Moscow',
'locale' => 'ru',
```

Создайте символическую ссылку для публичных файлов:
```bash
php artisan storage:link
```

### 3. Настройка базы данных

**PostgreSQL (рекомендуется)**
В файле `.env`:

```ini
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=your_database
DB_USERNAME=postgres
DB_PASSWORD=postgres
```

**Для оптимальной работы установите расширение pg_trgm:**

```sql
CREATE EXTENSION IF NOT EXISTS pg_trgm;
```

Проверить наличие расширения:

```sql
SELECT extname FROM pg_extension;
```

**MySQL**

В файле `.env`:
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=root
DB_PASSWORD=root
```

Для MySQL 8.0+ рекомендуется использовать `utf8mb4_unicode_ci`:
```ini
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

### 4. Установка пакета

```bash
composer require dedermus/open-admin-core
```

### 5. Публикация ресурсов
```bash
php artisan vendor:publish --provider="OpenAdminCore\Admin\AdminServiceProvider"
```
Эта команда опубликует:

- Конфигурацию в `config/admin.php`
- Языковые файлы в `resources/lang`
- Миграции в `database/migrations`
- Ассеты в `public/vendor/open-admin`

### 6. Настройка дисков для загрузки файлов
В файле `config/filesystems.php` добавьте или обновите диски:

```php
'disks' => [
    // ... другие диски

    'uploads' => [
        'driver' => 'local',
        'root' => public_path('uploads'),
        'url' => env('APP_URL').'/uploads',
        'visibility' => 'public',
        'throw' => false,
        'permissions' => [
            'file' => [
                'public' => 0644,
                'private' => 0600,
            ],
            'dir' => [
                'public' => 0755,
                'private' => 0700,
            ],
        ],
    ],

    'admin' => [
        'driver' => 'local',
        'root' => public_path('admin/uploads'),
        'url' => env('APP_URL').'/admin/uploads',
        'visibility' => 'public',
        'throw' => false,
        'permissions' => [
            'file' => [
                'public' => 0644,
                'private' => 0600,
            ],
            'dir' => [
                'public' => 0755,
                'private' => 0700,
            ],
        ],
    ],
],
```

### 7. Настройка HTTPS (опционально)

В файле `config/admin.php`:

```php
'https' => env('ADMIN_HTTPS', true),
```

### 8. Завершение установки

```bash
php artisan admin:install
```

Эта команда выполнит:

- Запуск всех миграций (`php artisan migrate`)
- Заполнение таблиц начальными данными (пользователь admin, роли, разрешения)
- Создание директории `app/Admin` со следующей структурой:

```text
app/Admin/
├── Controllers/
│   ├── HomeController.php
│   ├── AuthController.php
│   └── ExampleController.php
├── bootstrap.php
└── routes.php
```

### 9. Доступ к админ-панели

Откройте в браузере: `http://localhost/admin`

**Учетные данные по умолчанию:**

- Логин: `admin`
- Пароль: `admin`

|⚠️ Важно: После первого входа обязательно смените пароль администратора!

### Восстановление пароля
Пакет поддерживает функционал восстановления пароля через email. Для его работы необходимо:

1. **Настроить отправку email** в файле `.env`:

```ini
MAIL_MAILER=smtp
MAIL_HOST=smtp.mail.ru
MAIL_PORT=465
MAIL_USERNAME=your-email@example.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=your-email@example.com
MAIL_FROM_NAME="Admin Panel"
```

2. Убедиться, что у пользователей заполнен email. Если email отсутствует, пользователь не сможет восстановить пароль.

3. Настроить параметры восстановления в `config/admin.php`:

```php
'auth' => [
    'password_reset' => [
    'enabled' => true,           // Включить/отключить функционал
    'expire' => 60,              // Время жизни ссылки (минут)
    'throttle' => 3,             // Максимум попыток
    'throttle_decay_minutes' => 60, // Время блокировки после превышения
    ],
],
````

4. Настроить логирование в config/logging.php:

```php
'channels' => [
    'password_reset' => [
    'driver' => 'daily',
    'path' => storage_path('logs/password-reset.log'),
    'level' => 'info',
    'days' => 30,
    ],
],
```

5. Очистка устаревших токенов:

```bash
php artisan admin:clear-resets
```

Рекомендуется добавить эту команду в расписание (cron):

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
$schedule->command('admin:clear-resets')->daily();
}
```

**Процесс восстановления пароля**
1. Пользователь нажимает "Забыли пароль?" на странице входа
2. Вводит логин или email
3. Если учетная запись существует и имеет email, отправляется ссылка на сброс
4. Пользователь переходит по ссылке и устанавливает новый пароль
5. После успешного сброса выполняется перенаправление на страницу входа

**Безопасность**

- Ограничение количества попыток (throttle)
- Токены сброса имеют ограниченное время жизни
- Общие сообщения об успехе (не сообщаем, существует ли пользователь)
- Логирование всех попыток в отдельный канал
- Пароль должен соответствовать требованиям безопасности (минимум 8 символов)
---

### Конфигурация
Основные настройки находятся в файле `config/admin.php`:

| **Параметр**                   | 	**Описание**           | 	**Значение по умолчанию** |
|--------------------------------|-------------------------|----------------------------|
| `directory`                    | 	Директория админки     | `app/Admin`                |
| `route.prefix`                 | 	Префикс маршрутов	     | `admin`                    |
| `database.users_table`         | 	Таблица пользователей	 | `admin_users`              |
| `database.roles_table`         | 	Таблица ролей	         | `admin_roles`              |
| `database.permissions_table`   | 	Таблица разрешений	    | `admin_permissions`        | 
| `database.menu_table`          | 	Таблица меню	          | `admin_menu`               |
| `database.operation_log_table` | 	Журнал операций	       | `admin_operation_log`      |
---

### Команды админ-панели
Пакет предоставляет набор Artisan-команд для управления админ-панелью:

| **Команда**                            | **Описание**                                   |
|----------------------------------------|------------------------------------------------|
| `php artisan admin`                    | Список всех доступных команд                   |
| `php artisan admin:install`            | Установка админ-панели                         |
| `php artisan admin:uninstall`          | Удаление админ-панели                          |
| `php artisan admin:create-user`        | Создание нового пользователя                   |
| `php artisan admin:reset-password`     | Сброс пароля пользователя                      |
| `php artisan admin:make {model}`       | Генерация контроллера для модели               |
| `php artisan admin:form {name}`        | Генерация виджета формы                        |
| `php artisan admin:action {name}`      | Генерация действия                             |
| `php artisan admin:permissions`        | Генерация разрешений на основе таблиц          |
| `php artisan admin:generate-menu`      | Генерация меню на основе маршрутов             |
| `php artisan admin:menu`               | Вывод структуры меню                           |
| `php artisan admin:config`             | Сравнение конфигурации с оригиналом            |
| `php artisan admin:minify`             | Минификация CSS и JS ассетов                   |
| `php artisan admin:extend {extension}` | Создание расширения                            |
| `php artisan admin:import {extension}` | Импорт расширения                              |
| `php artisan admin:dev-links`          | Создание символических ссылок для разработки   |
| `php artisan admin:publish`            | Публикация ресурсов                            |
---

### Примеры использования команд

**Создание контроллера для модели Post:**

```bash
php artisan admin:make "App\Models\Post" --title="Статьи"
```

После выполнения команды будет создан контроллер `app/Admin/Controllers/PostController.php` с готовыми методами для CRUD операций.

**Добавление маршрута в** `app/Admin/routes.php`:

```php
$router->resource('posts', PostController::class);
```

**Генерация разрешений для таблицы** `posts`:

```bash
php artisan admin:permissions --tables=posts
```
---

### Обновление

Обновление до новой версии пакета:

```bash
composer update dedermus/open-admin-core
php artisan vendor:publish --tag=open-admin-assets --force
php artisan view:clear
php artisan config:clear
```
---

### Расширения
Пакет поддерживает расширения, адаптированные для Bootstrap 5.3:

| **Расширение** | **Описание**               |
|----------------|----------------------------|
| helpers        | Инструменты для разработки |
| media-manager  | Управление файлами         |
| config         | 	Менеджер конфигурации     |
| grid-sortable  | Сортируемые таблицы        |
| Ckeditor       | Визуальный редактор        |
| api-tester     | Тестирование API           |
| scheduling     | Управление задачами        |
| phpinfo        | Информация о PHP           |
| log-viewer     | Просмотр логов             |
| page-designer  | Конструктор страниц        |
| reporter	      | Отчеты об ошибках          |
| redis-manager  | Управление Redis           |

**Установка расширения**
```bash
composer require dedermus/helpers
php artisan admin:import helpers
```
### Поддержка RTL (справа налево)
Для поддержки языков с направлением письма справа налево:

1. Откройте файл `vendor/dedermus/open-admin-core/src/Traits/HasAssets.php`
2. В массиве `$baseCss` замените:

   - `bootstrap.min.css` → `bootstrap.rtl.min.css`
   - `AdminLTE.min.css` → `AdminLTE.rtl.min.css`

---

### Устранение неполадок
#### Проблема: `Class "OpenAdminCore\Admin\AdminServiceProvider" not found`
**Решение:** Запустите composer dump-autoload:

```bash
composer dump-autoload
```

#### Проблема: Ошибка при миграции с PostgreSQL
**Решение:** Убедитесь, что установлено расширение pg_trgm:

```sql
CREATE EXTENSION IF NOT EXISTS pg_trgm;
```
#### Проблема: Ошибка 404 при доступе к админке
**Решение:** Проверьте файл app/Admin/routes.php и убедитесь, что префикс маршрутов в config/admin.php совпадает с URL.

---

### Лицензия
`open-admin-core` распространяется под лицензией The MIT License (MIT).
