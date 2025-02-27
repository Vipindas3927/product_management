<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ProjectSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Project setup custom command';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting project setup...');

        Artisan::call('key:generate');
        $this->info('✅ Application key generated.');

        Artisan::call('migrate:fresh --seed');
        $this->info('Database migrated and seeded successfully.');

        Artisan::call('storage:link');
        $this->info('Storage linked successfully.');

        if (strtolower($this->ask('Do you want to start the development server now? (yes/no)')) === 'yes') {
            $this->info('🚀 Starting the Laravel development server...');
            passthru('php artisan serve');
        } else {
            $this->info('❌ Skipping server startup. Setup completed!');
        }
    }
}
