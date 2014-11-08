<?php

@(require_once('config.php'));
include('version.php');
$debug = 0; // Set to 1 in order to enable debug mode (shows sensitive database info), use for troubleshooting
$footer = "&copy; Copyright 2014 $wsn. Powered by <a href='http://github.com/cydrobolt/polr'>Polr</a> ver $version build $reldate";
//connect to mysql with $mysqli variable
$mysqli = new mysqli($host, $user, $passwd, $db) or $wp = 1; //If cannot connect, then set var $wp to 1
// Attempt to set Charset as UTF8 to avoid real_escape_string vulnerabilities
if (!$mysqli->set_charset("utf8")) {
    $insecure = TRUE;
} else {
    $insecure = FALSE;
}

function autoloader($class) {
        include $class . '.php';
}

spl_autoload_register('autoloader');
session_start();

function sqlex($table, $rowf, $where, $wval) {
    global $mysqli; //Import var into function
//Sanitize strings
    $rowfs = $mysqli->real_escape_string($rowf);
    $tables = $mysqli->real_escape_string($table);
    $wheres = $mysqli->real_escape_string($where);
    $wvals = $mysqli->real_escape_string($wval);
    $q2p = "SELECT {$rowfs} FROM {$tables} WHERE ?=?";
	$stmt = $mysqli->prepare($q2p);
	$stmt->bind_param('ss', $wheres, $wvals);
	$stmt->execute();
	$result = $stmt->get_result();
    $numrows = $result->num_rows;
    if (!$numrows) {
        return false;
    } else {
        return true;
    }
}

function sqlfetch($table, $rowf, $where, $wval) {
    global $mysqli;

    $rowfs = $mysqli->real_escape_string($rowf);
    $tables = $mysqli->real_escape_string($table);
    $wheres = $mysqli->real_escape_string($where);
    $wvals = $mysqli->real_escape_string($wval);

    //$query = "SELECT $rowfs FROM $tables WHERE $wheres='$wvals'";
    $q2p = "SELECT {$rowfs} FROM {$tables} WHERE ?=?";
	$stmt = $mysqli->prepare($q2p);
	echo $mysqli->error;
	$stmt->bind_param('ss', $wheres, $wvals);
	$stmt->execute();
	$result = $stmt->get_result();
    $row = mysqli_fetch_assoc($result);
    return $row[$rowf];
}
function sqlfetch($table, $rowf, $where, $wval) {
	/*
     * Fetch a value from the database
     * Takes 4 arguments:
     * $table : table in question
     * $rowf = row to fetch
     * $where = the 'where' value, as in WHERE $where = $wval
     * $wval = the value of the $where val ^
     */
    global $mysqli;

    $rowfs = $mysqli->real_escape_string($rowf);
    $tables = $mysqli->real_escape_string($table);
    $wheres = $mysqli->real_escape_string($where);
    $wvals = $mysqli->real_escape_string($wval);

    $query = "SELECT $rowfs FROM $tables WHERE $wheres='$wvals'";
    $result = $mysqli->query($query) or showerror();
    $row = mysqli_fetch_assoc($result);
    return $row[$rowf];
}

function showerror() {
	//Show an error, and die. If Debug is on, show SQL error message
    global $debug;
    global $mysqli;
    echo "There seems to be a problem. Contact an administrator to report this issue.";
    if ($debug == 1) {
        echo "<br>Error:<br>";
        echo $mysqli->error;
    }
    die();
}


function filterurl($url) {
    if (!filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED)) {
        return false;
    } else {
        return true;
    }
}
function filteremail($email) {
	// Validate an email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    } else {
        return true;
    }
}