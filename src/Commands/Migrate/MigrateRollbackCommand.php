<?php

namespace Sellmate\Laravel\MultiTenant\Commands\Migrate;

use Illuminate\Database\Console\Migrations\RollbackCommand;
use Illuminate\Support\Facades\DB;
use Sellmate\Laravel\MultiTenant\Commands\TenantCommand;
use Sellmate\Laravel\MultiTenant\DatabaseManager;
use Symfony\Component\Console\Input\InputOption;

class MigrateRollbackCommand extends RollbackCommand
{
    use TenantCommand;
    
    /**
     * Create a new migration rollback command instance.
     *
     * @param  \Illuminate\Database\Migrations\Migrator  $migrator
     * @return void
     */
    public function __construct(\Illuminate\Database\Migrations\Migrator $migrator)
    {
        parent::__construct($migrator);

        $this->manager = new DatabaseManager();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        DB::setDefaultConnection($this->manager->systemConnectionName);

        if ($this->option('tenant')) {
            $tenants = $this->getTenants();
            $progressBar = $this->output->createProgressBar(count($tenants));
            $this->setTenantDatabase();
            foreach ($tenants as $tenant) {
                $this->manager->setConnection($tenant);
                $this->info("Rolling back '{$tenant->name}'...");
                $progressBar->advance();
                parent::handle();
            }
        } else {
            $this->setSystemDatabase();
            parent::handle();
        }
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge([
            ['tenant', 'T', InputOption::VALUE_NONE, "Rollback the last database migration for tenant database."],
            ['domain', NULL, InputOption::VALUE_OPTIONAL, "The domain for tenant. 'all' or null value for all tenants."]
        ], parent::getOptions());
    }
}
