<?php
/**
 * Codeception PHP script runner
 */

require_once dirname(__FILE__).'/vendor/codeception/codeception/autoload.php';
require_once __DIR__ . '/tests/TestCase.php';

use Codeception\Application;

$app = new Application('Codeception', Codeception\Codecept::VERSION);
$app->add(new Codeception\Command\Run('run'));

$app->run();