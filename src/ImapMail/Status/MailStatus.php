<?php

namespace App\ImapMail\Status;



abstract class MailStatus
{
    abstract function getWorkflowTransaction(): string;
}