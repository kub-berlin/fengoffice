<?php

// grep -o mysql_[a-z_]* `git grep -l mysql_ | grep -v MysqlDBAdapter` | sed 's/.*://' | sort | uniq

$MYSQL_DATA = array();
$MYSQL_CON = null;

// general
function mysql_connect($host, $user, $pass) {
	global $MYSQL_DATA;
	$MYSQL_DATA['host'] = $host;
	$MYSQL_DATA['user'] = $user;
	$MYSQL_DATA['pass'] = $pass;
}

function mysql_close() {
	global $MYSQL_CON;
	$MYSQL_CON = null;
}

function mysql_select_db($name) {
	global $MYSQL_DATA, $MYSQL_CON;
	$MYSQL_CON = new mysqli($MYSQL_DATA['host'], $MYSQL_DATA['user'], $MYSQL_DATA['pass'], $name);
}

function mysql_real_escape_string($s) {
	return mysqli_real_escape_string($s);
}

// connection
function mysql_query($sql) {
	global $MYSQL_CON;
	return $MYSQL_CON->query($sql);
}

function mysql_errno() {
	global $MYSQL_CON;
	return $MYSQL_CON->errno;
}

function mysql_error() {
	global $MYSQL_CON;
	return $MYSQL_CON->error;
}

function mysql_get_server_info() {
	global $MYSQL_CON;
	return $MYSQLI_NUM['con']->server_info;
}

function mysql_insert_id() {
	global $MYSQL_CON;
	return $MYSQL_CON->insert_id;
}

function mysql_ping() {
	global $MYSQL_CON;
	return $MYSQL_CON->ping();
}

// result
function mysql_fetch_array($res) {
	return $res->fetch_array($result_type);
}

function mysql_fetch_assoc($res) {
	return $res->fetch_assoc();
}

function mysql_fetch_object($res) {
	return $res->fetch_object();
}

function mysql_fetch_row($res) {
	return $res->fetch_row();
}

?>
