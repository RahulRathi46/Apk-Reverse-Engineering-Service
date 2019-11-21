<?php

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;


// Addon

$container = $app->getContainer();
$container['upload_directory'] = getcwd() . '/uploads';
$container['result_directory'] = getcwd() . '/results';

function moveUploadedFile($directory, UploadedFile $uploadedFile, $flag)
{
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
    if ($flag) {
        $basename = str_replace(' ', '_', pathinfo($uploadedFile->getClientFilename(), PATHINFO_FILENAME) . bin2hex(random_bytes(8)));
    } else {
        $basename = pathinfo($uploadedFile->getClientFilename(), PATHINFO_FILENAME);
    }

    $filename = sprintf('%s.%0.8s', $basename, $extension);

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
}

function db_connect()
{
    // db connection params
    $servername = "taskqueue.cyhijjtyhq9l.us-east-2.rds.amazonaws.com";
    $username = "vangiex";
    $password = "amount1234";
    $dbname = "task_queue";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname, 3306);

    // Check connection
    if ($conn->connect_error) {
        // error log
        return $conn->connect_error;
    }

    // return conn object
    return $conn;
}

// excute db query
function query($sql)
{
    // get conn object
    $conn = db_connect();
    // execute query
    $result = $conn->query($sql);
    // close conn
    $conn->close();
    // return result
    return $result;
}

function maintanince()
{
    // Get All Record
    $sql = "SELECT `UID`,`FILENAME`,`FILE_BROWSE_LOC`,`STATUS`,`SYNCED`,`TIMESTAMPER` FROM `Worker_Queue` HAVING `STATUS` = 'true' AND `SYNCED` = 'true' AND `TIMESTAMPER` < DATE_SUB(CURDATE(), INTERVAL 2 DAY) AND `TIMESTAMPER` < CURDATE() ORDER BY `TIMESTAMPER` DESC";
    $result = query($sql);
    if ($result->num_rows > 50) {
        $rows = $result;
        while ($result = $rows->fetch_assoc()) {
            // Extracted Zip
            $zip_op = $result['FILE_BROWSE_LOC'];
            // Uploaded Apk
            $up_apk = 'uploads/' . $result['FILENAME'];
            // Zip File
            $zip_res = $result['FILE_BROWSE_LOC'] . '.zip';
            // Manage
            rmdir($zip_op);
            unlink($up_apk);
            unlink($zip_res);
            $m_sql = "DELETE FROM `Worker_Queue` WHERE `UID` = '" . $result['UID'] . "'";
            $m_result = query($m_sql);
            // echo print_r($result);
        }
    }
}

function disk_aval()
{
    $aval = (disk_free_space("/") / 1000000000);

    if ($aval < 2) {
        // Get All Record
        $sql = "SELECT `UID`,`FILENAME`,`FILE_BROWSE_LOC`,`STATUS`,`SYNCED`,`TIMESTAMPER` FROM `Worker_Queue` having `TIMESTAMPER` > DATE_SUB(CURDATE(), INTERVAL 120 minute ) ORDER BY `TIMESTAMPER` asc LIMIT 6";
        $result = query($sql);
        if ($result->num_rows > 0) {
            $rows = $result;
            while ($result = $rows->fetch_assoc()) {
                // Extracted Zip
                $zip_op = $result['FILE_BROWSE_LOC'];
                // Uploaded Apk
                $up_apk = 'uploads/' . $result['FILENAME'];
                // Zip File
                $zip_res = $result['FILE_BROWSE_LOC'] . '.zip';
                // Manage
                rmdir($zip_op);
                unlink($up_apk);
                unlink($zip_res);
                $m_sql = "DELETE FROM `Worker_Queue` WHERE `UID` = '" . $result['UID'] . "'";
                $m_result = query($m_sql);
                echo print_r($result);
                echo $result['UID'];
            }
        }
    }
    return true;
}

$app->get('/test', function (Request $request, Response $response, array $args) {
    maintanince();
    // echo(disk_free_space("/") / 1000000000);

});

// Routes
$app->get('/', function (Request $request, Response $response, array $args) {

    session_destroy();

    // Set Operation Variables
    $uid = uniqid();
    $tool = 'both';
    $task = 'disallow';

    session_start();
    $_SESSION = array();
    $_SESSION['uid'] = $uid;

    setcookie('uid', $uid, 0, "/"); // 86400 = 1 day
    setcookie('task', $task, 0, "/"); // 86400 = 1 day
    setcookie('tool', $tool, 0, "/"); // 86400 = 1 day

    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    $args['tool'] = $tool;

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});


$app->get('/apktool', function (Request $request, Response $response, array $args) {

    session_destroy();

    // Set Operation Variables
    $uid = uniqid();
    $task = 'disallow';
    $tool = 'apktool';

    session_start();
    $_SESSION = array();
    $_SESSION['uid'] = $uid;

    setcookie('uid', $uid, 0, "/"); // 86400 = 1 day
    setcookie('task', $task, 0, "/"); // 86400 = 1 day
    setcookie('tool', $tool, 0, "/"); // 86400 = 1 day

    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    $args['tool'] = $tool;

    // Render index view
    return $this->renderer->render($response, 'apktool.phtml', $args);
});


$app->get('/jadx', function (Request $request, Response $response, array $args) {

    session_destroy();

    // Set Operation Variables
    $uid = uniqid();
    $task = 'disallow';
    $tool = 'jadx';

    session_start();
    $_SESSION = array();
    $_SESSION['uid'] = $uid;

    setcookie('uid', $uid, 0, "/"); // 86400 = 1 day
    setcookie('task', $task, 0, "/"); // 86400 = 1 day
    setcookie('tool', $tool, 0, "/"); // 86400 = 1 day

    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    $args['tool'] = $tool;

    // Render index view
    return $this->renderer->render($response, 'jadx.phtml', $args);
});

$app->get('/download[/]', function ($request, $response, $args) {


    if (isset($_COOKIE['task'])) {
        $task = $_COOKIE['task'];
    } else {
        $task = '';
    }

    if ($task == 'disallow') {
        return $response->withRedirect('/');
    } else {

        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        // Render index view
        return $this->renderer->render($response, 'download.phtml', $args);
    }
});

$app->get('/progress[/]', function ($request, $response, $args) {

    if (isset($_COOKIE['task'])) {
        $task = $_COOKIE['task'];
    } else {
        $task = '';
    }

    if ($task == 'disallow') {
        return $response->withRedirect('/');
    } else {

        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        return $this->renderer->render($response, 'progress.phtml', $args);
    }
});

$app->get('/about[/]', function ($request, $response, $args) {

    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    // Render index view
    return $this->renderer->render($response, 'about.phtml', $args);
});

$app->get('/search[/]', function ($request, $response, $args) {

    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    // Render index view
    return $this->renderer->render($response, 'search.phtml', $args);
});

$app->get('/privacy', function (Request $request, Response $response, array $args) {

    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    // Render index view
    return $this->renderer->render($response, 'privacy.phtml', $args);
});

$app->get('/server[/]', function ($request, $response, $args) {

    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    PHPinfo();
});

$app->post('/upload[/]', function ($request, $response, $args) {

    date_default_timezone_set('Asia/Kolkata');
    session_start();

    $respn = [
        'status' => 500,
        'msg' => 'Unable To Process',
        'action' => '/progress'
    ];

    if (isset($_COOKIE['tool']) and isset($_SESSION['uid'])) {
        $uid = $_SESSION['uid'];
        $tool = $_COOKIE['tool'];

        $task = 'allow';
        setcookie('task', $task, 0, "/"); // 86400 = 1 day
        setcookie('tool', $tool, 0, "/"); // 86400 = 1 day

        $directory = $this->get('upload_directory');

        $uploadedFiles = $request->getUploadedFiles();

        // handle single input with single file upload
        $uploadedFile = $uploadedFiles['file'];
        if ($uploadedFile->getError() === UPLOAD_ERR_OK and $uploadedFile->getSize() < 100000000 and strpos($uploadedFile->getClientFilename(), '.apk') !== false) {
            disk_aval();
            $filename = moveUploadedFile($directory, $uploadedFile, true);
            $file_browse_loc = 'results/' . substr($filename, 0, -4);
            $file_download_url = "http://" . $_SERVER['SERVER_NAME'] . '/uploads/' . $filename;
            $result_download_url = "http://" . $_SERVER['SERVER_NAME'] . '/results/' . substr($filename, 0, -4) . '.zip';
            $status = 'false';
            $status_msg = 'Task Assigned';
            $synced = 'false';

            $sql = "INSERT INTO `Worker_Queue`(`UID`, `TOOL`, `FILENAME`, `FILE_BROWSE_LOC`, `FILE_DOWNLOAD_URL`, `RESULT_DOWNLOAD_URL`, `STATUS`, `STATUS_MSG`, `SYNCED`) VALUES ('$uid','$tool','$filename','$file_browse_loc','$file_download_url','$result_download_url','$status','$status_msg','$synced')";
            $result = query($sql);
            if ($result == true) {
                $respn['status'] = 200;
                $respn['msg'] = "Task Assigned Waiting For Queue";
            } else {
                $respn['status'] = 404;
                $respn['msg'] = "Task Assignment Error";
            }

        } else {
            $respn['status'] = 404;
            $respn['msg'] = "File Upload Error : Error With File Type Or File Size Exceeded Limit ( <100MB )";
        }

    }
    echo json_encode($respn);
});

$app->post('/post_result[/]', function ($request, $response, $args) {

    $respn = [
        'status' => 500,
        'msg' => 'Unable To Process / No UID Found',
    ];

    if (isset($_POST['UID'])) {

        $uid = $_POST['UID'];
        $directory = $this->get('result_directory');
        $uploadedFiles = $request->getUploadedFiles();

        // handle single input with single file upload
        $uploadedFile = $uploadedFiles['file'];
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $filename = moveUploadedFile($directory, $uploadedFile, false);
            $sql = "UPDATE `Worker_Queue` SET `STATUS`= 'true' WHERE `UID`= '$uid'";
            $result = query($sql);
            if ($result === true) {
                $respn['status'] = 200;
                $respn['msg'] = "Result Posted For $uid";
            } else {
                $respn['status'] = 404;
                $respn['msg'] = "Result File Post Error";
            }

        } else {
            $respn['status'] = 404;
            $respn['msg'] = "Result File Post Error";
        }

    }
    echo json_encode($respn);
});


$app->post('/synced[/]', function ($request, $response, $args) {

    $respn = [
        'status' => 500,
        'msg' => 'Unable To Process No UID Found',
    ];

    if (isset($_POST['UID'])) {
        $uid = $_POST['UID'];
        $sql = "UPDATE `Worker_Queue` SET `SYNCED` = 'true' WHERE `UID`= '$uid'";
        $result = query($sql);
        if ($result) {
            $respn['status'] = 200;
        } else {
            $respn['status'] = 404;
        }
        $respn['msg'] = $result;
    }
    echo json_encode($respn);

});

$app->get('/queue', function ($request, $response, $args) {

    maintanince();

    $sql = "SELECT * FROM `Worker_Queue` HAVING `STATUS`= 'false' AND `SYNCED` = 'false' ORDER BY `TIMESTAMPER` ASC LIMIT 3";

    $result = query($sql);
    $rows = array();

    if ($result->num_rows > 0) {
        while ($Row = $result->fetch_assoc()) {
            $rows[] = $Row;
        }

        return $response->withJson($rows,
            200,
            JSON_UNESCAPED_UNICODE);
    } else {
        return $response->withJson($rows,
            200,
            JSON_UNESCAPED_UNICODE);
    }

});

$app->post('/update/{uid}[/]', function ($request, $response, $args) {

    $uid = $args['uid'];
    $current_path = '';
    $file_name = '';

    $respn = [
        'status' => '500',
        'msg' => 'task under process',
        'action' => '/download'
    ];

    $sql = "SELECT `FILE_BROWSE_LOC` ,`STATUS` , `UID`  FROM `Worker_Queue` WHERE `STATUS`= 'true' AND `UID` = '$uid'";
    $result = query($sql);
    if ($result->num_rows > 0) {
        $result = $result->fetch_array();
        $current_path = $result['FILE_BROWSE_LOC'];
        $file_name = $result['FILE_BROWSE_LOC'];

        if (is_dir($current_path)) {
            $respn['status'] = '200';
            $respn['msg'] = 'task done';
            setcookie('current_path', $current_path, 0, "/"); // 86400 = 1 day
            setcookie('file_name', $file_name, 0, "/"); // 86400 = 1 day
        } else {
            $zip = new ZipArchive;
            $res = $zip->open($result['FILE_BROWSE_LOC'] . '.zip');
            if ($res === TRUE) {
                $zip->extractTo($current_path);
                $zip->close();
                $respn['status'] = '200';
                $respn['msg'] = 'task done';
                setcookie('current_path', $current_path, 0, "/"); // 86400 = 1 day
                setcookie('file_name', $file_name, 0, "/"); // 86400 = 1 day
            } else {
                $respn['status'] = '404';
                $respn['msg'] = 'Unable To Decode' . $result['FILE_BROWSE_LOC'] . '.zip';
            }
        }
    }

    echo json_encode($respn);
});

$app->post('/worker[/]', function ($request, $response, $args) {

    $respn = [
        'status' => '500',
        'msg' => 'unable to process' . json_encode($_POST),
    ];

    if (isset($_POST['batch_size'])) {
        if ($_POST['batch_size'] === $_POST["batch_size_done"]) {
            // Log Only
            $this->logger->info("Worker Log " . json_encode($_POST) . " Stats");
            $respn['status'] = '200';
            $respn['msg'] = 'normally loged';

        } else {
            // Email Alert with details
            // Log With Error
            $this->logger->error("Worker Log " . json_encode($_POST) . " Stats Error");
            $respn['status'] = '200';
            $respn['msg'] = 'Alerted Log';
        }
    }

    echo json_encode($respn);
});