<?php
require_once('database.php');
$userlogin = 'cir2';

$requestMethod = $_SERVER['REQUEST_METHOD'];
$request = substr($_SERVER['PATH_INFO'], 1);
$request = explode('/', $request);
$requestRessource = array_shift($request);
$id = array_shift($request);
if ($id == '')
    $id = null;

$db = dbConnect();
if ($db === false){
    header('HTTP/1.1 503 Service Unavailable');
    exit();
}

switch ($requestRessource){
    case 'photos':
        photos();
    case 'commentaires':
        commentaires();
}

function photos(){
    global $db, $id;
    if ($id == null)
        $photos = dbRequestPhotos($db);
    else
        $photos = dbRequestPhoto($db, $id);

    if ($photos) {
        header('Content-Type: text/json; charset=utf-8');
        header('Cache-control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('HTTP/1.1 200 OK');
        echo json_encode($photos);
    }
    else {
        header('HTTP/1.1 400 Bad Request');
    }
    exit();
}

function commentaires(){
    global $requestMethod;

    function get(){
        global $db;
        if (isset($_GET['photoid'])) {
            $list_of_comments = dbRequestComments($db, $_GET['photoid']);

            header('Content-Type: text/json; charset=utf-8');
            header('Cache-control: no-store, no-cache, must-revalidate');
            header('Pragma: no-cache');
            header('HTTP/1.1 200 OK');

            echo json_encode($list_of_comments);
            exit();
        }
    }

    function post(){
        global $db;

        echo print_r($_POST, true);

        if (isset($_POST['userlogin']) && isset($_POST['photoid']) && isset($_POST['text'])){
            if (dbAddComment($db, $_POST['userlogin'], $_POST['photoid'], $_POST['text'])){
                header('HTTP/1.1 201 Created');
            } else {
                header('HTTP/1.1 500 Internal Server Error');
            }
        } else {
            header('HTTP/1.1 400 Bad Request');
        }
        exit();
    }

    function put(){
        global $db, $id;
        parse_str(file_get_contents('php://input'), $_PUT);

        if (isset($_PUT['userlogin']) and isset($_PUT['comment'])){
            if (dbModifyComment($db, $id, $_PUT['userlogin'], $_PUT['comment'])){
                header('HTTP/1.1 200 OK');
            } else {
                header('HTTP/1.1 500 Internal Server Error');
            }
        } else {
            header('HTTP/1.1 400 Bad Request');
        }
        exit();
    }

    function delete(){
        global $db, $id;

        if (isset($_GET['userlogin'])){
            if (dbDeleteComment($db, $id, $_GET['userlogin'])){
                    header('HTTP/1.1 200 OK');
                } else {
                    header('HTTP/1.1 500 Internal Server Error');
                }
            } else {
                header('HTTP/1.1 400 Bad Request');
        }
        exit();
    }

    switch ($requestMethod){
        case 'GET':
            get();
        case 'POST':
            post();
        case 'PUT':
            ini_set("log_errors", 1);
            error_log("put");
            put();
        case 'DELETE':
            delete();
    }

}