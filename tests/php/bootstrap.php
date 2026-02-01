<?php
/**
 * PHPUnit Bootstrap File
 *
 * This file initializes the test environment for OptStack.
 * No WordPress dependencies should be loaded here.
 *
 * @package OptStack\Tests
 */

declare(strict_types=1);

// Load Composer autoloader
$autoloader = dirname(__DIR__, 2) . '/vendor/autoload.php';

if (!file_exists($autoloader)) {
    echo 'Please run "composer install" before running tests.' . PHP_EOL;
    exit(1);
}

require_once $autoloader;

// Define any test constants here (if needed)
// These should NOT be WordPress constants

// Ensure we're in testing mode
if (!defined('OPTSTACK_TESTING')) {
    define('OPTSTACK_TESTING', true);
}
