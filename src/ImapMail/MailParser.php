<?php

namespace App\ImapMail;

use App\Entity\InboxMail;
use App\ImapMail\Status\MailStatus;
use App\ImapMail\Status\OurBcc;
use App\ImapMail\Status\PotentialHam;
use App\ImapMail\Status\PotentialSpam;
use Psr\Log\LoggerInterface;

class MailParser
{
    /** Начинается с Re: и есть ID из 10 цифр*/
    private const SUBJ_WITH_REQ_ID = '/^Re:.*ID:\d{11}/';
    private const ID_REQ = '/ID:\d{11}/';
    private const RUSSIANS_MIN_PART = 1 / 3;
    private const ID_OFFSET = 3;
    private const NOT_SPAM_WORDS = [
        'Тэглайн',
        'преми',
//        'Tagline',
//        'award',
        'рейтинг',
        'Раменск',
        'Фриммер',
        'Назаров',
        'Раменский',
        'Черников',
//        'Ольг',
        'Куликов',
        'Кушнир',
        'Гептнер',
        'номинац',
        'Народное голосование',
        'Вся информация, которая содержится в письме и прикрепленных к нему файлах',
        'опла',
        'конкурс',
//        'кейс',
    ];

    private InboxMail $inboxMail;
    private ?LoggerInterface $logger;

    public function __construct(InboxMail $inboxMail, LoggerInterface $logger = null)
    {
        $this->inboxMail = $inboxMail;
        $this->logger = $logger;
    }

    /** Парсим ID обращения из темы письма*/
    public function getReqId()
    {
        preg_match(self::SUBJ_WITH_REQ_ID, $this->inboxMail->getSubj(), $matches, PREG_OFFSET_CAPTURE);
        if (count($matches) !== 1) {
            return false;
        }

        preg_match(self::ID_REQ, $matches[0][0], $matches, PREG_OFFSET_CAPTURE);

        return mb_substr($matches[0][0], self::ID_OFFSET, mb_strlen($matches[0][0]) - self::ID_OFFSET);
    }

    public function getStatus(): MailStatus
    {
        $body = mb_strtolower(strip_tags($this->inboxMail->getBody()));
        $subj = mb_strtolower($this->inboxMail->getSubj());
        $from = mb_strtolower($this->inboxMail->getFromEmail());

        /** Если письмо с домена tagline.ru — пометить как BCC */
        if (strpos($from, '@tagline.ru') !== false) {
            return new OurBcc();
        }

        /** Если в теме или теле встречается одно из ключевых слов — не спам */
        foreach (self::NOT_SPAM_WORDS as $word) {
            $word = mb_strtolower($word);
            if (strpos($subj, $word) !== false || strpos($body, $word) !== false) {
                if (isset($this->logger)) {
                    $this->logger->debug('ПРОШЛО ПРОВЕРКУ: ', ['word' => $word, 'subj' => $subj]);
                }
                return new PotentialHam();
            }
        }

        /** Если в теле русских символов больше нормы — не спам */
        preg_match_all('/[А-Яа-яЁё]+/u', $body, $matches);
        $russians = implode('', $matches[0]);
        if ($this->logger) {
            $this->logger->debug('MESSAGE VARS:', [mb_strlen($russians), mb_strlen($body), $body]);
        }

        if (mb_strlen($russians) >= (mb_strlen($body) * self::RUSSIANS_MIN_PART)) {
            return new PotentialHam();
        }
        return new PotentialSpam();
    }
}