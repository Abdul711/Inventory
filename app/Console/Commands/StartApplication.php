<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StartApplication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Application Start';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
         $this->info('Starting Laravel application...');

        return $this->call('serve');
    }
}