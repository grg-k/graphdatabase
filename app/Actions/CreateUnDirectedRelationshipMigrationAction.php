<?php

namespace App\Actions;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;

class CreateUnDirectedRelationshipMigrationAction implements ActionInterface
{
    public function __construct(protected string $name)
    {  
    }

    public function run()
    {
        $tableName = $this->name;
        $columns = ['first_node', 'second_node', 'properties'];
        
        // i called another command to create migration class
        Artisan::call('make:migration', [
            'name' => 'create_' . $tableName . '_table',
            '--create' => $tableName,
            '--table' => $tableName,
        ]);
    
        // Get the latest migration file path
        $path = database_path('migrations/' . collect(scandir(database_path('migrations')))
            ->last(function ($file) {
                return Str::endsWith($file, '.php');
            }));
    
        // Add the columns to the migration file
        $content = file_get_contents($path);
        $content = str_replace('$table->id();', '$table->id();' . PHP_EOL . $this->generateColumns($columns), $content);
        file_put_contents($path, $content);

        Artisan::call('migrate');
    }

    /**
     * Generate the columns string for the migration file.
     *
     * @param  array  $columns
     * @return string
     */
    private function generateColumns(array $columns): string
    {
        $columnsString = '';
        foreach ($columns as $column) {
            if ($column === 'first_node') {
                $columnsString .= "\t\t\t" . '$table->unsignedBigInteger(\'first_node_id\');' . PHP_EOL;
                $columnsString .= "\t\t\t" . '$table->string(\'first_node_type\');' . PHP_EOL;
            } elseif ($column === 'second_node') {                
                $columnsString .= "\t\t\t" . '$table->unsignedBigInteger(\'second_node_id\');' . PHP_EOL;
                $columnsString .= "\t\t\t" . '$table->string(\'second_node_type\');' . PHP_EOL;
            } else {
                $columnsString .= "\t\t\t" . '$table->string(\'' . $column . '\');' . PHP_EOL;
            }
        }
        return $columnsString;
    }
}