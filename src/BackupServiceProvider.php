<?php

namespace Nue\Backup;

use Illuminate\Support\ServiceProvider;

class BackupServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot(Backup $extension)
    {
        if ($views = $extension->views()) {
            $this->loadViewsFrom($views, 'nue-backup');
        }

        Backup::boot();
    }
}