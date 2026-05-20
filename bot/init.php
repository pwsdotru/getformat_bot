<?php

declare(strict_types=1);

require_once(dirname(__DIR__) . "/vendor/autoload.php");

use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;

if (empty($argv[1])) {
    printf("Use URL to bot folder as argument\n");
    exit(1);
}
printf("Starting set webhook....\n");
try {
    $config = parse_ini_file(__DIR__ . "/config.ini");
    $bot = new BotApi($config["TOKEN"]);
    $response = $bot->setWebhook(trim($argv[1], "/") . "/index.php");
    if ($response == "1") {
        printf("OK\n");
    } else {
        printf("Error: %s\n", $response);
    }
} catch (Exception $e) {
    printf("Exception: %s\n\n", $e->getMessage());
}
printf("Done\n");
