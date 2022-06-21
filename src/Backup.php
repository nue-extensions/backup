<?php

namespace Nue\Backup;

use Novay\Nue\Nue;
use Novay\Nue\Extension;
use Spatie\Backup\Commands\ListCommand;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatus;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatusFactory;

class Backup extends Extension
{
    public $name = 'backup';

    public $views = __DIR__.'/../resources/views';

    public function getExists()
    {
        $statuses = BackupDestinationStatusFactory::createForMonitorConfig(config('backup.monitor_backups'));

        $listCommand = new ListCommand();

        $rows = $statuses->map(function (BackupDestinationStatus $backupDestinationStatus) use ($listCommand) {
            return $listCommand->convertToRow($backupDestinationStatus);
        })->all();

        foreach ($statuses as $index => $status) {
            $name = $status->backupDestination()->backupName();

            $files = array_map('basename', $status->backupDestination()->disk()->allFiles($name));

            $rows[$index]['files'] = array_slice(array_reverse($files), 0, 30);
        }

        return $rows;
    }

    /**
     * Bootstrap this package.
     *
     * @return void
     */
    public static function boot()
    {
        static::registerRoutes();

        Nue::extend('backup', __CLASS__);
    }

    /**
     * Register routes for Nue.
     *
     * @return void
     */
    protected static function registerRoutes()
    {
        parent::routes(function ($router) {
            /* @var \Illuminate\Routing\Router $router */
            $router->get('backup', 'Nue\Backup\Http\Controllers\BackupController@index')->name('backup-list');
            $router->get('backup/download', 'Nue\Backup\Http\Controllers\BackupController@download')->name('backup-download');
            $router->post('backup/run', 'Nue\Backup\Http\Controllers\BackupController@run')->name('backup-run');
            $router->delete('backup/delete', 'Nue\Backup\Http\Controllers\BackupController@delete')->name('backup-delete');
        });
    }

    /**
     * {@inheritdoc}
     */
    public static function import()
    {
        parent::createMenu('Backup', 'backup', 'fa-copy');

        parent::createPermission('Backup', 'ext.backup', 'backup*');
    }
}