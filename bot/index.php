<?php

declare(strict_types=1);

require_once(dirname(__DIR__) . "/vendor/autoload.php");
require_once(dirname(__DIR__) . "/src/TelegramMarkdown.php");

use TelegramBot\Api\Client;
use TelegramBot\Api\Exception;
use src\TelegramMarkdown;

try {
    $config = parse_ini_file(__DIR__ . "/config.ini");
    $bot = new Client($config["TOKEN"]);

    $bot->command('help', function ($message) use ($bot) {
        $msg = "Bot help you save text and format for any telegram post. Just forward any post to bot and get text file with markdown.";
        $bot->sendMessage($message->getChat()->getId(), $msg, "markdown");
    });

    $bot->command('start', function ($message) use ($bot) {
        $msg = "Hi. I'm can help you save any telegram post. Type /help for details";
        $bot->sendMessage($message->getChat()->getId(), $msg, "markdown");
    });

    $bot->on(function ($update) use ($bot) {
        /** @var \TelegramBot\Api\Types\Update $update */
        /** @var \TelegramBot\Api\Types\Message $message */
        $message = $update->getMessage();
        $tempfile = tempnam(sys_get_temp_dir(), 'getformatbot');
        if ($tempfile) {
            $parser = new TelegramMarkdown($message->getText(), $message->getEntities());
            file_put_contents($tempfile, $parser->getCode());
            $file = new \CURLFile($tempfile, "plain/text", "post" . $message->getMessageId() . ".txt");
            $bot->sendDocument(
                $message->getChat()->getId(),
                $file,
                null,
                $message->getMessageId()
            );
            unlink($tempfile);
        }
    }, function ($message) use ($bot) {
        return true;
    });
    $bot->run();
} catch (Exception $e) {
    printf("Exception: %s on %s:%d\n\n", $e->getMessage(), $e->getFile(), $e->getLine());
    exit(1);
}
