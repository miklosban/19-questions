<?php

//
// Set up production environment
//
error_reporting(E_ALL);
ini_set('display_errors', 1);

//
// Database configuration
//
#define('DB_FILE', __DIR__ . '/database.db');
#define('DB_DSN', 'sqlite:' . constant('DB_FILE'));
define('DB_DSN', 'pgsql:host=localhost;port=5432;dbname=19questions');
define('user','gisadmin');
define('password','Koo3aico');
