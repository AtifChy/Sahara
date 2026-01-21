<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'sahara_db');
define('DB_USER', 'root');
define('DB_PASS', '');

$conn = null;

function getDB()
{
    global $conn;

    if ($conn !== null) {
        return $conn;
    }

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if (!$conn) {
        die('Database connection failed: ' . mysqli_connect_error());
    }

    return $conn;
}

function query($sql)
{
    $db = getDB();

    $result = mysqli_query($db, $sql);

    if ($result === false) {
        error_log('Query Error: ' . mysqli_error($db) . ' | SQL: ' . $sql);
        return false;
    }

    return $result;
}

function fetchOne($sql)
{
    $result = query($sql);

    if (!$result) {
        return null;
    }

    $row = mysqli_fetch_assoc($result);

    if ($row === null) {
        return null;
    }

    return $row;
}

function fetchAll($sql)
{
    $result = query($sql);

    if (!$result) {
        return [];
    }

    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    return $rows;
}

function lastInsertId()
{
    $db = getDB();
    return mysqli_insert_id($db);
}


function affectedRows()
{
    $db = getDB();
    return mysqli_affected_rows($db);
}

function closeDB()
{
    global $conn;

    if ($conn !== null) {
        mysqli_close($conn);
        $conn = null;
    }
}

function tableExists($tableName)
{
    $result = query("SHOW TABLES LIKE '$tableName'");
    if (!$result) {
        return false;
    }
    $exists = mysqli_num_rows($result) > 0;
    return $exists;
}
