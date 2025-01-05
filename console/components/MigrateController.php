<?php

namespace console\components;

use mcms\modmanager\models\Module;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use mcms\common\helpers\Console;
use yii\console\Exception;

/**
 * Class MigrateController
 *
 * https://www.wrike.com/open.htm?id=65085819
 * https://www.wrike.com/open.htm?id=66148374
 *
 * Для того, чтобы создать миграцию нужно выполнить `php yii migrate/create <название миграции> --migrationPath="@mcms/<модуль>/migrations"`
 *
 * Примеры накатывания и откатывания миграций:
 *
 * `php yii migrate/up` или `php yii migrate` - поиск новых миграций из папки по-умолчанию ('@common/migrations')
 *
 * `php yii migrate/down` - откат одной миграции из папки по-умолчанию ('@common/migrations')
 *
 * `php yii migrate/up --migrationPath="@mcms/<модуль>/migrations"` - поиск  и выполнение новых миграций модуля <модуль>
 *
 * `php yii migrate/up --all` - поиск новых миграций всех модулей, включая папку миграций по-умолчанию
 *
 * `php yii migrate/down --migrationPath="@mcms/<модуль>/migrations"` - откатит последнюю миграцию модуля <модуль>
 *
 * `php yii migrate/down --all` - откатит одну последнюю миграцию из всех модулей, включая папку миграций по-умолчанию
 *
 * Флаг `--all` нужен для того, чтобы осуществлять рекурсивный поиск миграций по следующим местам:
 * папки активных модулей
 * папки из настройки `Yii::$app->params['migrationLookup']`
 * папка migrationPath по-умолчанию ('@common/migrations')
 */
class MigrateController extends \yii\console\controllers\MigrateController
{

    public $migrationPath = '@common/migrations';

    /**
     * @inheritdoc наш шаблончик
     */
    public $templateFile = '@console/views/migration.php';

    public $migrationLookup = [];

    public $sm;

    /**
     * @var bool Искать миграции во всех модулях? Если передать 0, то будетпоиск только по
     * @see \console\components\MigrateController::$migrationPath
     */
    public $all = true;

    protected $migrationRefs = [];

    public function beforeAction($action)
    {
        if (!$this->interactive) {
            Console::$interactive = false;
        }

        if ($action->id === 'up') {
            $this->handleInitDump();
        }

        if (!empty($this->sm)) {
            $this->migrationPath = $this->migrationLookup[$this->sm];
        }

        if (parent::beforeAction($action)) {
            $this->getAllMigrations();
            return true;
        }
        return false;
    }

    protected function getAllMigrations()
    {
        $paths = [$this->migrationPath];

        foreach ($this->migrationLookup as $additionalLookup) {
            if (($additionalPath = Yii::getAlias($additionalLookup)) && is_dir($additionalPath)) {
                $paths[] = $additionalPath;
            }
        }

        foreach ($paths as $path) {
            $migrations = $this->getPathMigrations($path);

            foreach ($migrations as $migration) {
                $this->migrationRefs[$migration] = $path;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return array_merge(
            parent::options($actionID),
            (in_array($actionID, ['up', 'down', 'redo'])) ? ['all'] : [],
            (in_array($actionID, ['up', 'down', 'create', 'redo'])) ? ['sm'] : []
        );
    }

    /**
     * Downgrades the application by reverting old migrations.
     * For example,
     *
     * ~~~
     * yii migrate/down     # revert the last migration
     * yii migrate/down 3   # revert the last 3 migrations
     * yii migrate/down all # revert all migrations
     * ~~~
     *
     * @param integer $limit the number of migrations to be reverted. Defaults to 1,
     * meaning the last applied migration will be reverted.
     * @return integer the status of the action execution. 0 means normal, other values mean abnormal.
     * @throws Exception if the number of the steps specified is less than 1.
     *
     */

    public function actionDown($limit = 1)
    {
        if ($limit === 'all') {
            $limit = null;
        } else {
            $limit = (int)$limit;
            if ($limit < 1) {
                throw new Exception("The step argument must be greater than 0.");
            }
        }

        $migrations = $this->getMigrationHistory($limit, !$this->all);

        if (empty($migrations)) {
            $this->stdout("No migration has been done before.\n", Console::FG_YELLOW);

            return self::EXIT_CODE_NORMAL;
        }

        $migrations = array_keys($migrations);

        $n = count($migrations);
        $this->stdout("Total $n " . ($n === 1 ? 'migration' : 'migrations') . " to be reverted:\n", Console::FG_YELLOW);
        foreach ($migrations as $migration) {
            $this->stdout("\t$migration\n");
        }
        $this->stdout("\n");

        if ($this->confirm('Revert the above ' . ($n === 1 ? 'migration' : 'migrations') . "?")) {
            foreach ($migrations as $migration) {
                if (!$this->migrateDown($migration)) {
                    $this->stdout("\nMigration failed. The rest of the migrations are canceled.\n", Console::FG_RED);

                    return self::EXIT_CODE_ERROR;
                }
            }
            $this->stdout("\nMigrated down successfully.\n", Console::FG_GREEN);
        }
    }

    /**
     * Redoes the last few migrations.
     *
     * This command will first revert the specified migrations, and then apply
     * them again. For example,
     *
     * ```
     * yii migrate/redo     # redo the last applied migration
     * yii migrate/redo 3   # redo the last 3 applied migrations
     * yii migrate/redo all # redo all migrations
     * ```
     *
     * @param integer $limit the number of migrations to be redone. Defaults to 1,
     * meaning the last applied migration will be redone.
     * @return integer the status of the action execution. 0 means normal, other values mean abnormal.
     * @throws Exception if the number of the steps specified is less than 1.
     *
     */
    public function actionRedo($limit = 1)
    {
        if ($limit === 'all') {
            $limit = null;
        } else {
            $limit = (int)$limit;
            if ($limit < 1) {
                throw new Exception('The step argument must be greater than 0.');
            }
        }

        $migrations = $this->getMigrationHistory($limit, !$this->all);

        if (empty($migrations)) {
            $this->stdout("No migration has been done before.\n", Console::FG_YELLOW);

            return self::EXIT_CODE_NORMAL;
        }

        $migrations = array_keys($migrations);

        $n = count($migrations);
        $this->stdout("Total $n " . ($n === 1 ? 'migration' : 'migrations') . " to be redone:\n", Console::FG_YELLOW);
        foreach ($migrations as $migration) {
            $this->stdout("\t$migration\n");
        }
        $this->stdout("\n");

        if ($this->confirm('Redo the above ' . ($n === 1 ? 'migration' : 'migrations') . '?')) {
            foreach ($migrations as $migration) {
                if (!$this->migrateDown($migration)) {
                    $this->stdout("\nMigration failed. The rest of the migrations are canceled.\n", Console::FG_RED);

                    return self::EXIT_CODE_ERROR;
                }
            }
            foreach (array_reverse($migrations) as $migration) {
                if (!$this->migrateUp($migration)) {
                    $this->stdout("\nMigration failed. The rest of the migrations migrations are canceled.\n", Console::FG_RED);

                    return self::EXIT_CODE_ERROR;
                }
            }
            $this->stdout("\n$n " . ($n === 1 ? 'migration was' : 'migrations were') . " redone.\n", Console::FG_GREEN);
            $this->stdout("\nMigration redone successfully.\n", Console::FG_GREEN);
        }
    }

    protected function getMigrationHistory($limit, $onlyMigrationPath = false)
    {
        if ($this->db->schema->getTableSchema($this->migrationTable, true) === null) {
            $this->createMigrationHistoryTable();
        }
        $query = new Query;
        $query->select(['version', 'apply_time'])
            ->from($this->migrationTable);

        // Если нужны миграции только из папки migrationPath, то дополняем условие
        if ($onlyMigrationPath) {
            $pathMigrations = $this->getPathMigrations($this->migrationPath);
            $query->where(['in', 'version', $pathMigrations]);
        }

        $rows = $query->orderBy('apply_time DESC, version DESC')
            ->limit($limit)
            ->createCommand($this->db)
            ->queryAll();

        $history = ArrayHelper::map($rows, 'version', 'apply_time');
        unset($history[self::BASE_MIGRATION]);

        return $history;
    }

    /*
     * Получает список миграций из папки migrationPath
     * */
    protected function getPathMigrations($inputPath)
    {
        $migrations = [];
        $handle = opendir($inputPath);
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $path = $inputPath . DIRECTORY_SEPARATOR . $file;
            if (preg_match('/^(m(\d{6}_\d{6})_.*?)\.php$/', $file, $matches) && is_file($path)) {
                $migrations[] = $matches[1];
            }
        }
        closedir($handle);
        sort($migrations);
        return $migrations;
    }

    /**
     * Returns the migrations that are not applied.
     * @return array list of new migrations
     */
    protected function getNewMigrations()
    {
//    $this->stdout(":::DEBUG::: --all parameter = [" . $this->all . "]\n", Console::FG_YELLOW);

        // Если ищем миграции не рекурсивно,то используем стандартный механизм
        if (!$this->all) return parent::getNewMigrations();

        $applied = $this->getMigrationHistory(null);

        $migrations = $this->migrationRefs;
        foreach ($migrations as $migration => $path) {
            if (isset($applied[$migration])) unset($migrations[$migration]);
        }
        $migrations = array_keys($migrations);
        sort($migrations);

        return $migrations;
    }


    protected function createMigration($class)
    {
        // Если ищем миграции не рекурсивно,то используем стандартный механизм
        if (!$this->all) return parent::createMigration($class);

        // Ищем папку соответствующей миграции
        $file = $this->migrationRefs[$class] . DIRECTORY_SEPARATOR . $class . '.php';

        require_once($file);

        return new $class();
    }

    private function handleInitDump()
    {
        $isInitSqlExecuted = (bool)Yii::$app->db->createCommand("SHOW TABLES LIKE 'users';")->queryScalar();

        if ($isInitSqlExecuted) {
            return;
        }

        $confirmed = $this->interactive ? Console::confirm('Apply init migration?', true) : true;

        if (!$confirmed) {
            $this->stderr('Without initial dump no need to apply other migrations' . PHP_EOL, Console::FG_RED);
            die();
        }
        $this->stdout('executing initial sql dump...' . PHP_EOL);
        (new RestoreInitDump())->run();
        $this->stdout('DONE' . PHP_EOL, Console::FG_GREEN);
    }
}
