<?php

namespace Johnny\App;

define('GIT', "%ProgramFiles%/Git/bin/git");
define('LOGFILE', "log.txt");
define('TIMEZONE', "Australia/Melbourne");

class AutoDeploy {
    private $_content, $_log, $_time, $_signature, $_hash, $_token, $_dir, $_remote;

    public function __construct($dir,  $token = null, $remote = "origin") {
        date_default_timezone_set(TIMEZONE);

        // Set class variables
        $this->_content = file_get_contents('php://input');
        $this->_log = fopen(LOGFILE, "a");
        $this->_time = time();
        $this->_signature = isset($_SERVER['HTTP_X_HUB_SIGNATURE']) ? $_SERVER['HTTP_X_HUB_SIGNATURE'] : null;
        $this->_hash = isset($this->_signature) ? explode("=", $this->_signature )[1] : null;
        $this->_token = $token;
        $this->_dir = $dir;
        $this->_remote = $remote;
    }

    public function __destruct() {
        $this->_log("Info: Ending script...");
        fputs($this->_log, "\n\n" . PHP_EOL);
        fclose($this->_log);
    }

    /**
     * Authenticate request
     *
     * @return void
     */
    public function auth() {
        if (!isset($this->_signature) || !isset($this->_hash)) {
            $this->_deny("Access Denied: Invalid Token");
        }

        if ($this->_checkGit() && $this->_checkToken()) {
            $this->_executeGit();
        }
        else {
            $this->_deny();
        }
    }

    /**
     * Custom deny method
     *
     * @param  message
     * @return void
     */
    private function _deny($message = null) {
        if ($message) {
            $this->_log($message);
        }

        header('HTTP/1.0 403 Forbidden');
        exit;
    }

    /**
     * Custom logging method
     *
     * @param  messaage
     * @return void
     */
    private function _log($message) {
        fputs($this->_log, $message . "\n");
    }

    /**
     * Check token from request
     *
     * @return boolean
     */
    private function _checkToken() {
        $this->_log("Info: Checking X-Hub-Signature...");
        if ($this->_hash === hash_hmac("sha1", $this->_content, $this->_token)) {
            $this->_log("Success: Token matched.");
            return true;
        }
        else {
            $this->_log("Access Denied: Invalid Token");
            return false;
        }
    }

    /**
     * @return boolean
     */
    private function _checkGit() {
        $this->_log("Info: Checking git directory...");
        if (file_exists($this->_dir . '/.git') && is_dir($this->_dir . "/")) {
            $this->_log("Success: git directory exists.");
            return true;
        }
        else {
            $this->_log("Access Denied: git repo does not exist.");
            return false;
        }
    }

    /**
     * Execute git command through shell_exec
     *
     * @return void
     */
    private function _executeGit() {
        $gitReset = shell_exec("cd " . $this->_dir . " && \"" . GIT . "\" reset --hard " . $this->_remote . "/master 2>&1");
        $gitPull = shell_exec("cd " . $this->_dir . " && \"" . GIT . "\" pull " . $this->_remote . " 2>&1");

        try {
           $this->_log("Info: executing git reset...");
           $this->_log($gitReset);
           $this->_log("Info: executing git pull...");
           $this->_log($gitPull);
           $this->_log("COMPLETE: AUTO-DEPLOY SUCCESSFUL");
        } catch (Exception $e) {
           $this->_log("Exception: " . $e . "");
        }
    }
}