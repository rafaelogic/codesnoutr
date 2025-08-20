<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExampleCommand extends Command
{
    /**
     * The name and signature of the console command.
     * This property is used by Laravel framework and should NOT be flagged as unused.
     */
    protected $signature = 'example:process {--queue} {--batch=}';

    /**
     * The console command description.
     * This property is used by Laravel framework and should NOT be flagged as unused.
     */
    protected $description = 'Process example data with optional queue and batch parameters';

    /**
     * Indicates whether the command should be shown in the Artisan command list.
     * This property is used by Laravel framework and should NOT be flagged as unused.
     */
    protected $hidden = false;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing example data...');
        
        // Enhanced scanner should recognize $signature, $description, and $hidden as legitimate framework properties
        // and NOT flag them as unused variables
        
        return Command::SUCCESS;
    }
}
