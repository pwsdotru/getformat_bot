<?php

declare(strict_types=1);

require_once(dirname(__DIR__) . "/vendor/autoload.php");

use TelegramBot\Api\Client;
use TelegramBot\Api\Exception;


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
    printf("Exception: %s\n\n", $e->getMessage());
    exit(1);
}


class TelegramMarkdown
{
    private $_text;
    private $_entities;

    private $_parsed;

    public function __construct($text, $entities)
    {
        $this->_text = $text;
        $this->_entities = $entities;
        $this->_parsed = '';
        $this->parse();
    }

    private function parse($offset = 0)
    {
        if (empty($this->_entities)) {
            $this->_parsed .= mb_substr($this->_text, $offset);
        }
        $entity = array_shift($this->_entities);

        if ($entity && $entity != null) {
            $this->_parsed .= mb_substr($this->_text, $offset, $entity->getOffset() - $offset);

            $text = mb_substr($this->_text, $entity->getOffset(), $entity->getLength());

            switch ($entity->getType()) {
                case "bold":
                    $text = $this->bold($text);
                    break;
                case "italic":
                    $text = $this->italic($text);
                    break;
                case "code":
                    $text = $this->code($text);
                    break;
            }

            $this->_parsed .= $text;
            $this->parse($entity->getOffset() + $entity->getLength());
        }
    }

    private function bold($text)
    {
        return "**" . $text . "**";
    }

    private function italic($text)
    {
        return "__" . $text . "__";
    }

    private function code($text)
    {
        return "`" . $text . "`";
    }

    public function getCode()
    {
        return $this->_parsed;
    }
}
