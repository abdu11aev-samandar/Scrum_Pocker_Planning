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

//Task query
$result = $task->read();
//Get row count
$num = $result->rowCount();

//Check if any tasks
if ($num > 0) {
    //User array
    $tasks_arr = [];
    $tasks_arr['data'] = [];

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $task_item = [
            'id' => $id,
            'name' => $name,
            'point' => $point,
            'user_id' => $user_id
        ];

        //Push to "data"
        array_push($tasks_arr['data'], $task_item);
    }

    //Turn to JSON & output
    echo json_encode($tasks_arr);
} else {
    //No Posts
    echo json_encode(
        ['message' => 'No Tasks Found']
    );
}