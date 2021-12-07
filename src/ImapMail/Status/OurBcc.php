<?php

namespace App\ImapMail\Status;

class OurBcc extends MailStatus
{

    function getWorkflowTransaction(): string
    {
        return 'our_bcc';
    }
}