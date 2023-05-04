<?php

namespace App\Http\Controllers;

use App\Http\Requests\HandleDelteNodeRequest;
use App\Http\Requests\HandleStoreOrUpdateNodeRequest;
use Exception;
use Illuminate\Database\Eloquent\Factories\Relationship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class NodeController extends Controller
{
    public function store(HandleStoreOrUpdateNodeRequest $handleStoreNodeRequest)
    {
        $nodeType = 'node_' . $handleStoreNodeRequest->get('type');

        $table = DB::table('node_types')->where('type', $nodeType)->first();
        
        if (!$table) {
            $tableName = $nodeType;
            
            DB::table('node_types')->insert([
                'type' => $tableName,
            ]);

            Artisan::call('node:table', [
                'name' => $tableName,
            ]);
        } else {
            $tableName = $table->type; 
        }

        $nodeExists = DB::table($tableName)->where('name', $handleStoreNodeRequest->get('name'))->first();

        if ($nodeExists) {
            throw new Exception('The node name is already taken for this type. Please choose a different name.');
        }
        
        return DB::table($tableName)->insert([
            'name' => $handleStoreNodeRequest->get('name'),
            'properties' =>  json_encode($handleStoreNodeRequest->get('properties'))
        ]);
    }

    public function retreiveNodesByType(Request $request)
    {
        $table = DB::table('node_types')->where('type', 'node_' . $request->type)->first();

        if (!$table) {
            throw new Exception('The node type does not exist');
        }

        return DB::table($table->type)->get();
    }

    public function retreiveNode(Request $request)
    {
        $table = DB::table('node_types')->where('type', 'node_' . $request->type)->first();

        if (!$table) {
            throw new Exception('The node type does not exist');
        }

        $node = DB::table($table->type)->where('name', $request->name)->first();

        if (!$node) {
            throw new Exception('This node does not exist');
        }

        return $node;
    }

    public function getNodeRelationshipsByType(Request $request)
    {
        if ($request->direction == 'source') {
            $secondNodeDirection = 'destination';
        } elseif ($request->direction == 'destination') {
            $secondNodeDirection = 'source';
        } else {
            throw new Exception('You should specify the relationship direction');
        }

        $nodeTable = DB::table('node_types')->where('type', 'node_' . $request->nodeType)->first();

        if (!$nodeTable) {
            throw new Exception('The node type does not exist');
        }

        $node = DB::table($nodeTable->type)->where('name', $request->name)->first();

        if (!$node) {
            throw new Exception('This node does not exist');
        }

        $relationshipTable = DB::table('relationship_types')->where('type', 'relationship_' . $request->relationshipType)->first();

        if (!$relationshipTable) {
            throw new Exception('The relationship type does not exist');
        }

        $relationships = DB::table($relationshipTable->type)->where($request->direction . '_id', $node->id)
                                                ->where($request->direction . '_type', $nodeTable->type)
                                                ->get();

        $nodes = collect();
        
        foreach($relationships as $relationship) {
            $secondNodeId = $relationship->{$secondNodeDirection . '_id'};
            $secondNodeType = $relationship->{$secondNodeDirection . '_type'};

            $node = DB::table($secondNodeType)->where('id', $secondNodeId)->first();

            $nodes->push($node);
        }

        return $nodes;
    }

    public function getNodesRealtedByUnDirectedRelationship(Request $request)
    {   
        $table = DB::table('relationship_types')->where('type', 'relationship_' . $request->relationshipType)->first();

        if (!$table) {
            throw new Exception('The relationship type does not exist');
        }

        $relationships = DB::table($table->type)->get();

        $nodes = collect();

        foreach($relationships as $relationship) {
            $relatedNodes = collect();

            $firstNodeId = $relationship->first_node_id;
            $firstNodeType = $relationship->first_node_type;

            $secondNodeId = $relationship->second_node_id;
            $secondNodeType = $relationship->second_node_type;

            $firstNode = DB::table($firstNodeType)->where('id', $firstNodeId)->first();
            $secondNode = DB::table($secondNodeType)->where('id', $secondNodeId)->first();

            $relatedNodes->push($firstNode);
            $relatedNodes->push($secondNode);

            $nodes->push($relatedNodes);
        }

        return $nodes;
    }

    public function updateNode(HandleStoreOrUpdateNodeRequest $handleUpdateNodeRequest)
    {
        $table = DB::table('node_types')->where('type', 'node_' . $handleUpdateNodeRequest->get('type'))->first();

        if (!$table) {
            throw new Exception('The node type does not exist');
        }

        $node = DB::table($table->type)->where('name', $handleUpdateNodeRequest->get('name'))->first();

        if (!$node) {
            throw new Exception('This node does not exist');
        }

        $updatedNode = DB::table($table->type)
                                ->where('name', $handleUpdateNodeRequest->get('name'))
                                ->update(['properties' => json_encode($handleUpdateNodeRequest->get('properties'))]);


        return $updatedNode;
    }

    // note whenever we delete a node we need to delte all relationships that belongs to this node
    public function delete(HandleDelteNodeRequest $handleDelteNodeRequest)
    {
        $table = DB::table('node_types')->where('type', 'node_' . $handleDelteNodeRequest->get('type'))->first();

        if (!$table) {
            throw new Exception('The node type does not exist');
        }

        $node = DB::table($table->type)->where('name', $handleDelteNodeRequest->get('name'))->first();

        if (!$node) {
            throw new Exception('This node does not exist');
        }

        // deleting the relationships that belongs to this node

        $relationshipTables = DB::table('relationship_types')->get();

        foreach($relationshipTables as $relationshipTable) {
            if ($relationshipTable->directed) {

                DB::table($relationshipTable->type)->where('source_id', $node->id)
                                        ->where('source_type', $table->type)
                                        ->delete();

                DB::table($relationshipTable->type)->where('destination_id', $node->id)
                                        ->where('destination_type', $table->type)
                                        ->delete();

            } else {
                DB::table($relationshipTable->type)->where('first_node_id', $node->id)
                                        ->where('first_node_type', $table->type)
                                        ->delete();

                DB::table($relationshipTable->type)->where('second_node_id', $node->id)
                                        ->where('second_node_type', $table->type)
                                        ->delete();
            }
        }

        DB::table($table->type)->where('name', $handleDelteNodeRequest->get('name'))->delete();
    }
}
