<?php

declare(strict_types=1);

namespace src;

class TelegramMarkdown
{
    private $_text;
    private $_entities;

    private $_parsed;

    public function __construct(?string $text, ?array $entities)
    {
        $this->_text = $text;
        $this->_entities = $entities;
        $this->_parsed = '';
        $this->parse();
    }

    private function parse(int $offset = 0)
    {
        if (empty($this->_text)) {
            return;
        }
        if (empty($this->_entities)) {
            $this->_parsed .= mb_substr($this->_text, $offset);
        }
        if (null !== $this->_entities) {
            $entity = array_shift($this->_entities);
        } else {
            $entity = null;
        }

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
