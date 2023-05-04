<?php

namespace App\Console\Commands;

use App\Actions\CreateNodeMigrationAction;
use Illuminate\Console\Command;
use App\Actions\CreateNodeModelAction;

class NodeTableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'node:table {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new nodes table with different type';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        (new CreateNodeMigrationAction($this->argument('name')))->run();
    }
}
