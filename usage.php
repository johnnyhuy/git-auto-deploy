<?php
require_once 'AutoDeploy.php';

use Johnny\App\AutoDeploy;

// Replace DIRECTORY and TOKEN respectively
$deploy = new AutoDeploy("DIRECTORY", "TOKEN");
$deploy->auth();