<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';      // menyediakan $pdo (skema Ojan)
require_once __DIR__ . '/auth_check.php';       // wajibLogin(), wajibAdmin(), e()
require_once __DIR__ . '/csrf.php';             // generateCsrfToken(), csrfInput(), verifyCsrfToken()
require_once __DIR__ . '/functions.php';        // base_url(), set_flash(), get_flash()
