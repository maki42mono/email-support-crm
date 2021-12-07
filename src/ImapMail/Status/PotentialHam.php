<?php

namespace App\ImapMail\Status;

class PotentialHam extends MailStatus
{

    function getWorkflowTransaction(): string
    {
        return 'prepare_for_send';
    }
}