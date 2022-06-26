<?php
require_once 'db.php';

if (($_SERVER['REQUEST_METHOD'] === 'POST') && isset($_POST['fuckyouiamcat'])) {
    $dbconn = Db::getConnection();
    if ($dbconn !== null) {
        $dbconn->query("UPDATE stata SET fuckyouiamcat = NOW() WHERE id = " . (int) $_POST['fuckyouiamcat'] . ";");
        $dbconn->close();
    }

    session_start();
    $_SESSION['fuckyouiamcat'] = time();
    echo 'meow';
    die;
}

if (($_SERVER['REQUEST_METHOD'] !== 'POST') || !isset($_POST['click'])) {
    echo 'fail';
    die;
}

$dbconn = Db::getConnection();
if ($dbconn == null) {
    echo 'fail';
    die;
}

if ($_POST['btn'] == 'resume')
    $dbconn->query("UPDATE stata SET resume_clicked = true, clicked_dt = NOW() WHERE id = " . (int) $_POST['click'] . ";");
elseif ($_POST['btn'] == 'git')
    $dbconn->query("UPDATE stata SET git_clicked = NOW() WHERE id = " . (int) $_POST['click'] . ";");

$dbconn->close();
echo 'ok';
?>