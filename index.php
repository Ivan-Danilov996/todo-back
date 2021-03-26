<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');
header('Access-Control-Allow-Methods: *');
header('Access-Control-Allow-Credentials: true');
echo('hello');
$connect = mysqli_connect('remotemysql.com', 'LXoH1FNrh3', 'xUWrAdKnKX', 'LXoH1FNrh3');

$method = $_SERVER['REQUEST_METHOD'];
$q = $_GET['q'];
$params = explode('/', $q);
$type = $params[0];

function get_tasks()
{
    $task = [];
    global $connect;
    $tasks = mysqli_query($connect, "SELECT * FROM `tasks`");


    while ($task_element = mysqli_fetch_assoc($tasks)) {
        $task[] = $task_element;
    }
    return $task;
}


function sort_up($new_tasks)
{
    global $offset;
    global $data;
    $offset = $_GET['offset'];
    $data = $_GET['sort'];
    usort($new_tasks, function ($a, $b) {
        global $data;
        return strcmp((string) $a[$data], (string) $b[$data]);
    });
}

function sort_down($new_tasks)
{
    global $offset;
    global $data;
    $offset = $_GET['offset'];
    $data = $_GET['sort'];
    usort($new_tasks, function ($a, $b) {
        global $data;
        return strcmp((string) $a[$data], (string) $b[$data]);
    });
}




if ($method === 'GET') {
    $new_tasks = get_tasks();
    if ($type === 'tasks') {
        echo json_encode(['task' => array_slice($new_tasks, 0, 3), 'length' => count($new_tasks)]);
    } else if ($type === 'offset') {
        $offset = $_GET['offset'];
        echo json_encode(['task' => array_slice($new_tasks, $offset, 3), 'length' => count($new_tasks)]);
    } else if ($type === 'sortup') {
        sort_up($new_tasks);
        echo json_encode(['task' => array_slice($new_tasks, $offset, 3), 'length' => count($new_tasks)]);
    } else if ($type === 'sortdown') {
        sort_down($new_tasks);
        echo json_encode(['task' => array_slice($new_tasks, $offset, 3), 'length' => count($new_tasks)]);
    }
} else if ($method === 'POST') {
    if ($type === 'tasks') {
        $name = $_POST["name"];
        $email = $_POST["email"];
        $text = $_POST["textarea"];
        $status = 0;
        mysqli_query($connect, "INSERT INTO `tasks` (`name`,`email`,`text`,`status`) VALUES ('$name','$email','$text', $status)");
        $new_tasks = get_tasks();
        echo json_encode(['task' => array_slice($new_tasks, 0, 3), 'length' => count($new_tasks)]);
    } else if ($type === 'auth') {
        $login = $_POST["login"];
        $password = $_POST["password"];
        $password = md5($password);
        $result = mysqli_query($connect, "SELECT * FROM `users` WHERE `login` = '$login' AND `password` = '$password'");
        if (mysqli_num_rows($result) === 1) {
            echo json_encode(['success' => 'ok']);
        } else {
            echo json_encode(['success' => 'error']);
        }
    }
} else if ($method === 'PATCH') {
    $id = $_GET['id'];
    mysqli_query($connect, "UPDATE `tasks` SET status = 1 WHERE id = '$id'");
    $new_tasks = get_tasks();
    if ($type === 'offset') {
        $offset = $_GET['offset'];
        echo json_encode(['task' => array_slice($new_tasks, $offset, 3), 'length' => count($new_tasks)]);
    } else if ($type === 'sortup') {
        sort_up($new_tasks);
        echo json_encode(['task' => array_slice($new_tasks, $offset, 3), 'length' => count($new_tasks)]);
    } else if ($type === 'sortdown') {
        sort_down($new_tasks);
        echo json_encode(['task' => array_slice($new_tasks, $offset, 3), 'length' => count($new_tasks)]);
    }
}
