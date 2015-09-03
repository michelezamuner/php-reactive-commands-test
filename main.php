<?php
include 'vendor/autoload.php';

use Update\Console\Application;
use Update\Configuration\Configuration;

$app = new Application(new Configuration(__DIR__.'/config.yaml'));
$app->add(new Update\Console\Command\Server);
$app->add(new Update\Console\Command\TestRemoteConnection);
$app->add(new Update\Console\Command\TestDownload);
$app->run();