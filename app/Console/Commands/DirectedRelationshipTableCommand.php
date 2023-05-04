<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Actions\CreateDirectedRelationshipMigrationAction;

class DirectedRelationshipTableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'directed_relationship:table {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new directed relationships table with different type';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        (new CreateDirectedRelationshipMigrationAction($this->argument('name')))->run();
    }
}
