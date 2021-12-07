<?php

namespace App\ImapMail;

use PhpImap\IncomingMail;
use SecIT\ImapBundle\Service\Imap;

class MailGetter
{
    private Imap $imap;

    public function __construct(Imap $imap)
    {
        $this->imap = $imap;
    }

/**
 * @return IncomingMail[]
 * @throws \Exception
 */
    public function getTodayMail(): array
    {
        $rtrMailbox = $this->imap->get('rating_tagline_ru');
        $today = new \DateTime('now -9 day');
        $todayMailUids = $rtrMailbox->searchMailbox('ON ' . $today->format('Y-m-d'));
        dump($todayMailUids);
        $res = [];
        foreach ($todayMailUids as $mailUid) {
            $res[] = $rtrMailbox->getMail($mailUid, false);
        }

        return $res;
    }
}