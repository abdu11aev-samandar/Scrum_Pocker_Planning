<?php

//Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/Database.php';
include_once '../../models/Task.php';

//Instantiate DB & connect
$database = new Database();
$db = $database->connect();

//Instantiate blog user object
$task = new Task($db);

//Get ID
$task->id = isset($_GET['id']) ? $_GET['id'] : die();

//Get user
$task->read_single();

//Create array
$user_arr = [
    'id' => $task->id,
    'name' => $task->name,
    'point' => $task->point,
    'user_id' => $task->user_id,
];

//Make JSON
print_r(json_encode($user_arr));