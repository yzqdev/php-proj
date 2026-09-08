<?php
declare(strict_types=1);

namespace App\Database;

use Illuminate\Database\Schema\Blueprint;
use RuntimeException;

/**
 * 极简迁移执行器:按文件名顺序执行 database/migrations/*.php,
 * 已执行的迁移记录到 migrations 表;支持逐条回滚(调用迁移的 down 方法)。
 */
final class Migrator
{
    public function __construct(private readonly Manager $manager)
    {
    }

    /**
     * 执行所有尚未运行的迁移。
     *
     * @return list<string> 本次实际执行的迁移名列表
     */
    public function run(string $migrationsPath): array
    {
        $this->ensureMigrationsTable();

        $executed = [];
        foreach ($this->migrationFiles($migrationsPath) as $file) {
            $name = $this->migrationName($file);
            if ($this->hasRun($name)) {
                continue;
            }

            $this->loadMigration($file)->up($this->manager);
            $this->manager->table('migrations')->insert(['migration' => $name]);
            $executed[] = $name;
        }

        return $executed;
    }

    /**
     * 回滚最近一条已执行的迁移。
     *
     * @return string|null 被回滚的迁移名;没有可回滚时返回 null
     */
    public function rollback(string $migrationsPath): ?string
    {
        $this->ensureMigrationsTable();

        $last = $this->manager->table('migrations')->orderByDesc('id')->first();
        if ($last === null) {
            return null;
        }

        $file = $migrationsPath . DIRECTORY_SEPARATOR . $last->migration . '.php';
        if (!is_file($file)) {
            throw new RuntimeException("Migration file not found: {$file}");
        }

        $this->loadMigration($file)->down($this->manager);
        $this->manager->table('migrations')->where('id', $last->id)->delete();

        return $last->migration;
    }

    /** @return list<string> */
    private function migrationFiles(string $migrationsPath): array
    {
        $files = glob($migrationsPath . DIRECTORY_SEPARATOR . '*.php');
        if ($files === false) {
            return [];
        }
        sort($files);

        return $files;
    }

    private function migrationName(string $file): string
    {
        return basename($file, '.php');
    }

    private function hasRun(string $name): bool
    {
        return $this->manager->table('migrations')->where('migration', $name)->exists();
    }

    /** @return object{up: callable, down: callable} */
    private function loadMigration(string $file): object
    {
        $migration = require $file;
        if (!is_object($migration) || !method_exists($migration, 'up') || !method_exists($migration, 'down')) {
            throw new RuntimeException("Migration file must return an object with up() and down(): {$file}");
        }

        return $migration;
    }

    private function ensureMigrationsTable(): void
    {
        $schema = $this->manager->schema();
        if ($schema->hasTable('migrations')) {
            return;
        }

        $schema->create('migrations', static function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('migration', 255)->unique();
            $table->timestamp('run_at')->useCurrent();
        });
    }
}
