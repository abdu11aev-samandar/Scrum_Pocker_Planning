<?php

//Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/Database.php';
include_once '../../models/User.php';

//Instantiate DB & connect
$database = new Database();
$db = $database->connect();

//Instantiate blog user object
$user = new User($db);

//Blog user query
$result = $user->read();
//Get row count
$num = $result->rowCount();

//Check if any posts
if ($num > 0) {
    //User array
    $users_arr = [];
    $users_arr['data'] = [];

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $post_item = [
            'id' => $id,
            'login' => $login,
            'password' => $password
        ];

        //Push to "data"
        array_push($users_arr['data'], $user_item);
    }

    //Turn to JSON & output
    echo json_encode($users_arr);
} else {
    //No Posts
    echo json_encode(
        ['message' => 'No Posts Found']
    );
}