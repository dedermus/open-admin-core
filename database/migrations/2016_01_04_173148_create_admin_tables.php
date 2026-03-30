<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

final class CreateAdminTables extends Migration
{
    /**
     * Получить соединение для миграции.
     */
    public function getConnection(): ?string
    {
        return config('admin.database.connection') ?: config('database.default');
    }

    /**
     * Получить список всех таблиц админ-панели.
     *
     * @return array<int, string>
     */
    private function getAdminTables(): array
    {
        return [
            config('admin.database.operation_log_table'),
            config('admin.database.role_menu_table'),
            config('admin.database.user_permissions_table'),
            config('admin.database.role_permissions_table'),
            config('admin.database.role_users_table'),
            config('admin.database.menu_table'),
            config('admin.database.permissions_table'),
            config('admin.database.roles_table'),
            config('admin.database.users_table'),
            config('admin.database.password_reset_tokens'),
        ];
    }

    /**
     * Запуск миграции - создание таблиц админ-панели.
     *
     * @throws \RuntimeException
     */
    public function up(): void
    {
        // Проверяем наличие конфигурации пакета
        if (!config('admin.database')) {
            $errorMessage = 'Admin configuration not loaded. Please run: php artisan vendor:publish --provider="OpenAdminCore\Admin\AdminServiceProvider"';
            Log::error($errorMessage);
            throw new RuntimeException($errorMessage);
        }

        $this->ensureTablesRemoved();
        $this->createUsersTable();
        $this->createRolesTable();
        $this->createPermissionsTable();
        $this->createMenuTable();
        $this->createRoleUsersTable();
        $this->createRolePermissionsTable();
        $this->createUserPermissionsTable();
        $this->createRoleMenuTable();
        $this->createOperationLogTable();
        $this->createPasswordResetTokens();

        // Создаем специализированные индексы для PostgreSQL
        $this->createPostgresSpecificIndexes();
    }

    /**
     * Откат миграции - удаление всех таблиц админ-панели.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ($this->getAdminTables() as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Удалить существующие таблицы перед созданием.
     */
    private function ensureTablesRemoved(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ($this->getAdminTables() as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Создание таблицы пользователей.
     */
    private function createUsersTable(): void
    {
        Schema::create(config('admin.database.users_table'), function (Blueprint $table): void {
            $table->comment('Справочник пользователей системы');

            $table->id()->comment('Уникальный идентификатор пользователя');
            $table->string('username', 190)->unique()->comment('Логин пользователя (уникальный)');
            $table->string('email', 320)->nullable()->unique()->comment('Адрес электронной почты (уникальный)')
                ->after('username');
            $table->string('password', 60)->comment('Хеш пароля пользователя (bcrypt)');
            $table->string('name')->comment('Полное имя пользователя');
            $table->string('avatar')->nullable()->comment('Путь к файлу аватара пользователя');
            $table->string('locale', 10)->default(config('app.locale', 'en'))
                ->comment('Языковая локаль в формате IETF (RFC 5646), например: ru, en, ru-RU, zh-CN');
            $table->string('remember_token', 100)->nullable()
                ->comment('Токен для функционала "Запомнить меня"');

            $table->timestamps()->comment('Временные метки создания и обновления');

            // Индексы для оптимизации поиска и сортировки
            $table->index('name', 'idx_users_name');
            $table->index('locale', 'idx_users_locale');
            $table->index('created_at', 'idx_users_created_at');
            // Индекс для remember_token не требуется - поле используется только для аутентификации
            // Индекс для email уже создан через unique()
        });
    }

    /**
     * Создание таблицы ролей.
     */
    private function createRolesTable(): void
    {
        Schema::create(config('admin.database.roles_table'), function (Blueprint $table): void {
            $table->comment('Справочник ролей доступа');

            $table->id()->comment('Уникальный идентификатор роли');
            $table->string('name', 50)->unique()->comment('Название роли (для отображения)');
            $table->string('slug', 50)->unique()->comment('Системный идентификатор роли (slug)');

            $table->timestamps()->comment('Временные метки создания и обновления');

            $table->index('slug', 'idx_roles_slug');
            $table->index('created_at', 'idx_roles_created_at');
        });
    }

    /**
     * Создание таблицы разрешений.
     */
    private function createPermissionsTable(): void
    {
        Schema::create(config('admin.database.permissions_table'), function (Blueprint $table): void {
            $table->comment('Справочник разрешений (прав доступа)');

            $table->id()->comment('Уникальный идентификатор разрешения');
            $table->string('name', 50)->unique()->comment('Название разрешения (для отображения)');
            $table->string('slug', 50)->unique()->comment('Системный идентификатор разрешения (slug)');
            $table->string('http_method')->nullable()->comment('HTTP-методы (GET, POST, PUT, DELETE и т.д.)');
            $table->text('http_path')->nullable()->comment('HTTP-пути, на которые распространяется разрешение');

            $table->timestamps()->comment('Временные метки создания и обновления');

            $table->index('slug', 'idx_permissions_slug');
            $table->index(['http_path' => 191], 'idx_permissions_http_path');
        });
    }

    /**
     * Создание таблицы меню.
     */
    private function createMenuTable(): void
    {
        Schema::create(config('admin.database.menu_table'), function (Blueprint $table): void {
            $table->comment('Структура административного меню');

            $table->id()->comment('Уникальный идентификатор пункта меню');
            $table->unsignedBigInteger('parent_id')->default(0)
                ->comment('ID родительского пункта меню (0 - корневой уровень)');
            $table->integer('order')->default(0)->comment('Порядок сортировки (ASC)');
            $table->string('title', 50)->comment('Заголовок пункта меню');
            $table->string('icon', 100)->comment('CSS-класс иконки (Font Awesome, SVG и т.д.)');
            $table->string('uri')->nullable()->comment('URI или маршрут для перехода');
            $table->string('permission')->nullable()
                ->comment('Необходимое разрешение для доступа (slug из permissions)');

            $table->timestamps()->comment('Временные метки создания и обновления');

            // Индексы для построения дерева меню и быстрого доступа
            $table->index('parent_id', 'idx_menu_parent_id');
            $table->index(['parent_id', 'order'], 'idx_menu_parent_order');
            $table->index('permission', 'idx_menu_permission');
            $table->index(['uri' => 191], 'idx_menu_uri');
        });
    }

    /**
     * Создание связующей таблицы ролей и пользователей.
     */
    private function createRoleUsersTable(): void
    {
        $usersTable = config('admin.database.users_table');
        $rolesTable = config('admin.database.roles_table');

        Schema::create(config('admin.database.role_users_table'), function (Blueprint $table) use ($usersTable, $rolesTable): void {
            $table->comment('Связь пользователей с ролями (многие ко многим)');

            $table->unsignedBigInteger('role_id')->comment('ID роли');
            $table->unsignedBigInteger('user_id')->comment('ID пользователя');

            $table->timestamps()->comment('Временные метки создания и обновления связи');

            // Составные индексы для оптимизации JOIN-запросов
            $table->unique(['role_id', 'user_id'], 'uniq_role_user');
            $table->index(['role_id', 'user_id'], 'idx_role_user');

            // Отдельные индексы для каждой колонки
            $table->index('role_id', 'idx_role_users_role_id');
            $table->index('user_id', 'idx_role_users_user_id');

            $table->foreign('role_id', 'fk_role_users_role_id')
                ->references('id')
                ->on($rolesTable)
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('user_id', 'fk_role_users_user_id')
                ->references('id')
                ->on($usersTable)
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Создание связующей таблицы ролей и разрешений.
     */
    private function createRolePermissionsTable(): void
    {
        $rolesTable = config('admin.database.roles_table');
        $permissionsTable = config('admin.database.permissions_table');

        Schema::create(config('admin.database.role_permissions_table'), function (Blueprint $table) use ($rolesTable, $permissionsTable): void {
            $table->comment('Связь ролей с разрешениями (многие ко многим)');

            $table->unsignedBigInteger('role_id')->comment('ID роли');
            $table->unsignedBigInteger('permission_id')->comment('ID разрешения');

            $table->timestamps()->comment('Временные метки создания и обновления связи');

            $table->unique(['role_id', 'permission_id'], 'uniq_role_permission');
            $table->index(['role_id', 'permission_id'], 'idx_role_permission');

            $table->index('role_id', 'idx_role_permissions_role_id');
            $table->index('permission_id', 'idx_role_permissions_permission_id');

            $table->foreign('role_id', 'fk_role_permissions_role_id')
                ->references('id')
                ->on($rolesTable)
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('permission_id', 'fk_role_permissions_permission_id')
                ->references('id')
                ->on($permissionsTable)
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Создание связующей таблицы пользователей и разрешений.
     */
    private function createUserPermissionsTable(): void
    {
        $usersTable = config('admin.database.users_table');
        $permissionsTable = config('admin.database.permissions_table');

        Schema::create(config('admin.database.user_permissions_table'), function (Blueprint $table) use ($usersTable, $permissionsTable): void {
            $table->comment('Прямые связи пользователей с разрешениями (многие ко многим)');

            $table->unsignedBigInteger('user_id')->comment('ID пользователя');
            $table->unsignedBigInteger('permission_id')->comment('ID разрешения');

            $table->timestamps()->comment('Временные метки создания и обновления связи');

            $table->unique(['user_id', 'permission_id'], 'uniq_user_permission');
            $table->index(['user_id', 'permission_id'], 'idx_user_permission');

            $table->index('user_id', 'idx_user_permissions_user_id');
            $table->index('permission_id', 'idx_user_permissions_permission_id');

            $table->foreign('user_id', 'fk_user_permissions_user_id')
                ->references('id')
                ->on($usersTable)
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('permission_id', 'fk_user_permissions_permission_id')
                ->references('id')
                ->on($permissionsTable)
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Создание связующей таблицы ролей и меню.
     */
    private function createRoleMenuTable(): void
    {
        $rolesTable = config('admin.database.roles_table');
        $menuTable = config('admin.database.menu_table');

        Schema::create(config('admin.database.role_menu_table'), function (Blueprint $table) use ($rolesTable, $menuTable): void {
            $table->comment('Связь ролей с пунктами меню (видимость меню по ролям)');

            $table->unsignedBigInteger('role_id')->comment('ID роли');
            $table->unsignedBigInteger('menu_id')->comment('ID пункта меню');

            $table->timestamps()->comment('Временные метки создания и обновления связи');

            $table->unique(['role_id', 'menu_id'], 'uniq_role_menu');
            $table->index(['role_id', 'menu_id'], 'idx_role_menu');

            $table->index('role_id', 'idx_role_menu_role_id');
            $table->index('menu_id', 'idx_role_menu_menu_id');

            $table->foreign('role_id', 'fk_role_menu_role_id')
                ->references('id')
                ->on($rolesTable)
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('menu_id', 'fk_role_menu_menu_id')
                ->references('id')
                ->on($menuTable)
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Создание таблицы журнала операций.
     */
    private function createOperationLogTable(): void
    {
        $usersTable = config('admin.database.users_table');
        $connection = $this->getConnection();

        Schema::create(config('admin.database.operation_log_table'), function (Blueprint $table) use ($usersTable, $connection): void {
            $table->comment('Журнал действий пользователей в административной панели');

            $table->id()->comment('Уникальный идентификатор записи в журнале');
            $table->unsignedBigInteger('user_id')->comment('ID пользователя, выполнившего действие');
            $table->string('path')->comment('Путь (URI) запроса');
            $table->string('method', 10)->comment('HTTP-метод запроса (GET, POST, PUT, DELETE)');
            $table->ipAddress('ip')->comment('IP-адрес пользователя');

            // Драйвер-зависимый тип поля для input
            if ($connection === 'pgsql') {
                $table->jsonb('input')->comment('Входные параметры запроса в формате JSONB');
            } else {
                $table->longText('input')->comment('Входные параметры запроса в формате JSON (сериализованный)');
            }

            $table->timestamps()->comment('Временные метки создания и обновления');

            // Индексы для эффективной работы с логами
            $table->index('user_id', 'idx_operation_log_user_id');
            $table->index('created_at', 'idx_operation_log_created_at');
            $table->index(['user_id', 'created_at'], 'idx_operation_log_user_created');
            $table->index(['path' => 191], 'idx_operation_log_path');
            $table->index('method', 'idx_operation_log_method');
            $table->index('ip', 'idx_operation_log_ip');

            $table->foreign('user_id', 'fk_operation_log_user_id')
                ->references('id')
                ->on($usersTable)
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Создание таблицы восстановления пароля
     */
    private function createPasswordResetTokens(): void
    {
        Schema::create(config('admin.database.password_reset_tokens'), function (Blueprint $table) {
            $table->comment('Таблица токенов сброса паролей');
            $table->string('email')->index()->comment('Email пользователя');
            $table->string('token')->comment('Уникальный токен сброса');
            $table->timestamp('created_at')->nullable()->comment('Время создания токена');
        });
    }

    /**
     * Создание специализированных индексов для PostgreSQL.
     */
    private function createPostgresSpecificIndexes(): void
    {
        // Проверяем, используется ли PostgreSQL
        if (config('database.default') !== 'pgsql') {
            return;
        }

        $permissionsTable = config('admin.database.permissions_table');
        $operationLogTable = config('admin.database.operation_log_table');

        // Создаем GIN-индекс для полнотекстового поиска по http_path
        if ($permissionsTable) {
            $this->createTrigramIndex($permissionsTable, 'http_path', 'idx_permissions_http_path_trgm');
        }

        // Создаем GIN-индекс для JSONB поля input в operation_log
        if ($operationLogTable) {
            $this->createJsonbIndex($operationLogTable, 'input', 'idx_operation_log_input_jsonb');
        }
    }

    /**
     * Создание GIN-индекса с поддержкой триграмм для полнотекстового поиска.
     */
    private function createTrigramIndex(string $table, string $column, string $indexName): void
    {
        try {
            // Проверяем, установлено ли расширение pg_trgm
            $hasTrgm = DB::select("SELECT extname FROM pg_extension WHERE extname = 'pg_trgm'");

            if (!empty($hasTrgm)) {
                DB::statement("CREATE INDEX IF NOT EXISTS {$indexName} ON {$table} USING gin ({$column} gin_trgm_ops)");
                Log::info("Создан GIN-индекс для полнотекстового поиска на {$table}.{$column}");
            } else {
                Log::warning(
                    "Расширение pg_trgm не установлено в PostgreSQL. " .
                    "Полнотекстовый поиск по полю {$table}.{$column} будет медленным. " .
                    "Установите расширение: CREATE EXTENSION IF NOT EXISTS pg_trgm;"
                );
            }
        } catch (\Exception $e) {
            Log::error("Ошибка при создании триграмм-индекса для {$table}.{$column}: " . $e->getMessage());
        }
    }

    /**
     * Создание GIN-индекса для JSONB поля.
     */
    private function createJsonbIndex(string $table, string $column, string $indexName): void
    {
        try {
            // Создаем GIN-индекс с jsonb_path_ops для эффективного поиска по ключам
            DB::statement("CREATE INDEX IF NOT EXISTS {$indexName} ON {$table} USING GIN ({$column} jsonb_path_ops)");
            Log::info("Создан GIN-индекс для JSONB поля {$table}.{$column}");
        } catch (\Exception $e) {
            Log::error("Ошибка при создании JSONB-индекса для {$table}.{$column}: " . $e->getMessage());
        }
    }
}
