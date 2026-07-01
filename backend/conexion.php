<?php

function conexion() {
    $host = "mysql";
    $db = "app_db";
    $user = "app_user";
    $pass = "app_password";

    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$db;charset=utf8",
            $user,
            $pass
        );

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    } catch (PDOException $e) {
        throw $e;
    }
}