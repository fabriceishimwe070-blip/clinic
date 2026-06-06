<?php
/**
 * config.php — Database credentials (edit these for InfinityFree or any host)
 *
 * InfinityFree: your DB host looks like sql123.infinityfree.com
 * Find it in your InfinityFree control panel → MySQL Databases
 */

define('DB_HOST', 'localhost');          // ← change to your host (e.g. sql123.infinityfree.com)
define('DB_NAME', 'clinic');             // ← your database name (e.g. epiz_12345678_clinic)
define('DB_USER', 'root');               // ← your DB username (e.g. epiz_12345678)
define('DB_PASS', '');                   // ← your DB password
define('DB_CHAR', 'utf8mb4');

/**
 * Base URL of your site (no trailing slash).
 * Used to build absolute redirect URLs when needed.
 * On InfinityFree: 'https://yoursite.infinityfreeapp.com'
 */
define('BASE_URL', '');
