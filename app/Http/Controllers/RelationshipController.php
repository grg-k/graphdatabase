<?php

namespace App\Http\Controllers;

use Exception;
use App\Http\Requests\HandleStoreRelationshipRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;

class RelationshipController extends Controller
{
    public function store(HandleStoreRelationshipRequest $handleStoreRelationshipRequest)
    {
        $directed = $handleStoreRelationshipRequest->get('directed');

        $relationshipType = 'relationship_' . $handleStoreRelationshipRequest->get('type');

        $table = DB::table('relationship_types')->where('type', $relationshipType)->first();

        if (!$table) {
            $tableName = $relationshipType;
            
            DB::table('relationship_types')->insert([
                'type' => $tableName,
                'directed' => $directed,
            ]);

            if ($directed) {
                Artisan::call('directed_relationship:table', [
                    'name' => $tableName,
                ]); 
            } else {
                Artisan::call('undirected_relationship:table', [
                    'name' => $tableName,
                ]);
            }
        } else {
            $tableName = $table->type; 
        }

        if ($directed) {
            $source = $handleStoreRelationshipRequest->get('source');
            $destination = $handleStoreRelationshipRequest->get('destination');
            
            $sourceType = 'node_' . $source['type'];
            $destinationType = 'node_' . $destination['type'];
    
            $sourceTypeExists = DB::table('node_types')->where('type', $sourceType)->first();
            $destinationTypeExists = DB::table('node_types')->where('type', $destinationType)->first();
    
            if (!$sourceTypeExists || !$destinationTypeExists) {
                throw new Exception('The source or destination node type does not exists.');
            }
    
            $sourceNode = DB::table($sourceType)->where('name', $source['name'])->first();
            $destinationNode = DB::table($destinationType)->where('name', $destination['name'])->first();
    
            if (!$sourceNode || !$destinationNode) {
                throw new Exception('The source or destination node does not exists.');
            }
    
            $sourceId = $sourceNode->id;
            $destinationId = $destinationNode->id;
    
            return DB::table($tableName)->insert([
                'source_id' => $sourceId,
                'source_type' => $sourceType,
                'destination_id' => $destinationId,
                'destination_type' => $destinationType,
                'properties' => json_encode($handleStoreRelationshipRequest->get('properties'))
            ]);
        } else {
            $first = $handleStoreRelationshipRequest->get('first_node');
            $second = $handleStoreRelationshipRequest->get('second_node');

            $firstNodeType = 'node_' . $first['type'];
            $secondNodeType = 'node_' . $second['type'];
    
            $firstNodeTypeExists = DB::table('node_types')->where('type', $firstNodeType)->first();
            $secondNodeTypeExists = DB::table('node_types')->where('type', $secondNodeType)->first();

            if (!$firstNodeTypeExists || !$secondNodeTypeExists) {
                throw new Exception('The first or second node type does not exists.');
            }

            $firstNode = DB::table($firstNodeType)->where('name', $first['name'])->first();
            $secondNode = DB::table($secondNodeType)->where('name', $second['name'])->first();
    
            if (!$firstNode || !$secondNode) {
                throw new Exception('The source or destination node does not exists.');
            }

            $firstNodeId = $firstNode->id;
            $secondNodeId = $secondNode->id;

            return DB::table($tableName)->insert([
                'first_node_id' => $firstNodeId,
                'first_node_type' => $firstNodeType,
                'second_node_id' => $secondNodeId,
                'second_node_type' => $secondNodeType,
                'properties' => json_encode($handleStoreRelationshipRequest->get('properties'))
            ]);
        }
    }

    public function retreiveRelationshipByType(Request $request)
    {
        $table = DB::table('relationship_types')->where('type', 'relationship_' . $request->type)->first();

        if (!$table) {
            throw new Exception('The relationship type does not exist');
        }

        return DB::table($table->type)->get();
    }

    public function delete(Request $request)
    {
        $relationshipType = 'relationship_' . $request->get('type');

        $table = DB::table('relationship_types')->where('type', $relationshipType)->first();

        if (!$table) {
            throw new Exception('The relationship type does not exist');
        }

        if (!$table->directed) {
            $first = $request->get('first_node');
            $second = $request->get('second_node');

            $firstNodeType = 'node_' . $first['type'];
            $secondNodeType = 'node_' . $second['type'];
    
            $firstNodeTypeExists = DB::table('node_types')->where('type', $firstNodeType)->first();
            $secondNodeTypeExists = DB::table('node_types')->where('type', $secondNodeType)->first();

            if (!$firstNodeTypeExists || !$secondNodeTypeExists) {
                throw new Exception('The first or second node type does not exists.');
            }

            $firstNode = DB::table($firstNodeType)->where('name', $first['name'])->first();
            $secondNode = DB::table($secondNodeType)->where('name', $second['name'])->first();
    
            if (!$firstNode || !$secondNode) {
                throw new Exception('The source or destination node does not exists.');
            }

            return DB::table($table->type)
                            ->where('first_node_id', $firstNode->id)
                            ->where('first_node_type', $firstNodeTypeExists->type)
                            ->where('second_node_id', $secondNode->id)
                            ->where('second_node_type', $secondNodeTypeExists->type)
                            ->delete();
        } else {
            $source = $request->get('source');
            $destination = $request->get('destination');
            
            $sourceType = 'node_' . $source['type'];
            $destinationType = 'node_' . $destination['type'];
    
            $sourceTypeExists = DB::table('node_types')->where('type', $sourceType)->first();
            $destinationTypeExists = DB::table('node_types')->where('type', $destinationType)->first();
    
            if (!$sourceTypeExists || !$destinationTypeExists) {
                throw new Exception('The source or destination node type does not exists.');
            }
    
            $sourceNode = DB::table($sourceType)->where('name', $source['name'])->first();
            $destinationNode = DB::table($destinationType)->where('name', $destination['name'])->first();
    
            if (!$sourceNode || !$destinationNode) {
                throw new Exception('The source or destination node does not exists.');
            }

            return DB::table($table->type)
                            ->where('source_id', $sourceNode->id)
                            ->where('source_type', $sourceTypeExists->type)
                            ->where('destination_id', $destinationNode->id)
                            ->where('destination_type', $destinationTypeExists->type)
                            ->delete();
        }
    }
}
