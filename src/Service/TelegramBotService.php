<?php

namespace App\Service;

use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Exception\TelegramException;

class TelegramBotService
{
    /**
     * @var string
     */
    private $botApiKey;

    /**
     * @var string
     */
    private $botUsername;

    /**
     * @var string
     */
    private $chatId;

    /**
     * @param string $botApiKey
     * @param string $botUsername
     * @param string $chatId
     */
    public function __construct(string $botApiKey, string $botUsername, string $chatId)
    {
        $this->botApiKey = $botApiKey;
        $this->botUsername = $botUsername;
        $this->chatId = $chatId;
    }

    /**
     * @param string $message
     */
    public function sendMessage(string $message): void
    {
        try {
            new Telegram($this->botApiKey, $this->botUsername);

            Request::sendMessage([
                'chat_id' => $this->chatId,
                'text' => $message,
            ]);
        } catch (\Exception $ex) {
        }
    }
}