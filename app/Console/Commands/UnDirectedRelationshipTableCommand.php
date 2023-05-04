<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Actions\CreateUnDirectedRelationshipMigrationAction;

class UnDirectedRelationshipTableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'undirected_relationship:table {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new undirected relationships table with different type';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        (new CreateUnDirectedRelationshipMigrationAction($this->argument('name')))->run();
    }
}
