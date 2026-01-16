<?php
// src/Message/SendEmailMessage.php

namespace App\Message;

class SendEmailMessage
{
    public function __construct(
        private int $userId,
        private string $subject,
        private ?string $textContent = null
    ) {}

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getTextContent(): ?string
    {
        return $this->textContent;
    }
}
