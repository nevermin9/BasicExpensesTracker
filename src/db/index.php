<?php

function initDB()
{
    $db_host = getenv('MYSQL_HOST');
    $db_name = getenv('MYSQL_DATABASE');
    $db_user = getenv('MYSQL_USER');
    $db_pass = getenv('MYSQL_PASSWORD');
    // $pdo = null;

    // try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name",
        $db_user,
        $db_pass,
        array(PDO::ATTR_PERSISTENT => true)
    );
    // } catch (\PDOException $error) {
    //     echo "Error while connecting to database: " . $error->getMessage();
    // }

    return function() use ($pdo): PDO
    {
        return $pdo;
    };
}
return initDB();
