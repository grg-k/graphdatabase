<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NodeController;
use App\Http\Controllers\RelationshipController;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/node', [NodeController::class, 'store']);
Route::get('/node/{type}', [NodeController::class, 'retreiveNodesByType']);
Route::get('/node/{type}/{name}', [NodeController::class, 'retreiveNode']);
Route::get('/node/{name}/{nodeType}/{relationshipType}/directed/{direction}', [
    NodeController::class, 'getNodeRelationshipsByType'
]);
Route::put('/node', [NodeController::class, 'updateNode']);
Route::get('/undirected/{relationshipType}', [
    NodeController::class, 'getNodesRealtedByUnDirectedRelationship'
]);
Route::delete('/node', [NodeController::class, 'delete']);

Route::post('/relationship', [RelationshipController::class, 'store']);
Route::get('/relationship/{type}', [RelationshipController::class, 'retreiveRelationshipByType']);
Route::delete('/relationship', [RelationshipController::class, 'delete']);
