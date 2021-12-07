<?php

namespace App\ImapMail\Status;

class PotentialSpam extends MailStatus
{
    function getWorkflowTransaction(): string
    {
        return 'reject';
    }
}