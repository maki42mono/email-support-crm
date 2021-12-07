<?php

namespace App\Message;

class MailMessage
{
    private $id;
//    private $context;

    public function __construct(int $id)
    {
        $this->id = $id;
//        $this->context = $context;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /*public function getContext(): array
    {
        return $this->context;
    }*/
}