<?php

namespace App\Entity;

use App\Repository\InboxMailRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use App\EventSubscriber\InboxMailListener;

/**
 * @ORM\Entity(repositoryClass=InboxMailRepository::class)
 * @ORM\EntityListeners({InboxMailListener::class})
 */
class InboxMail
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fromName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fromEmail;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $body;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $subj;

    /**
     * @ORM\Column(type="integer", unique=true)
     */
    private $mailUid;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $state;

    /**
     * @ORM\ManyToOne(targetEntity=SupportRequest::class, inversedBy="inboxMails")
     */
    private $support;

    /**
     * @ORM\Column(type="datetime")
     */
    private $received;

    const INIT_STATE = 'new_to_check';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFromName(): ?string
    {
        return $this->fromName;
    }

    public function setFromName(string $fromFull): self
    {
        $this->fromName = $fromFull;

        return $this;
    }

    public function getFromEmail(): ?string
    {
        return $this->fromEmail;
    }

    public function setFromEmail(string $fromEmail): self
    {
        $this->fromEmail = $fromEmail;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getSubj(): ?string
    {
        return $this->subj;
    }

    public function setSubj(?string $subj): self
    {
        $this->subj = $subj;

        return $this;
    }

    public function getMailUid(): ?int
    {
        return $this->mailUid;
    }

    public function setMailUid(int $mailUid): self
    {
        $this->mailUid = $mailUid;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getSupport(): ?SupportRequest
    {
        return $this->support;
    }

    public function setSupport(?SupportRequest $support): self
    {
        $this->support = $support;

        return $this;
    }

    public function getReceived(): ?\DateTimeInterface
    {
        return $this->received;
    }

    public function setReceived(\DateTimeInterface $received): self
    {
        $this->received = $received;

        return $this;
    }

    public function getFromFull(): string
    {
        $res = $this->getFromEmail();
        if (null !== $this->getFromName()) {
            $res = $this->getFromName() . ' ' . $res;
        }
        return $res;
    }

    public function __toString(): string
    {
        $subj = $this->getSubj();
        $offset = 25;
        if (mb_strlen($subj) > $offset) {
            $subj = mb_substr($subj, 0, $offset)."…";
        }
        $intlFormatter = new \IntlDateFormatter('ru_RU', \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT);
        $intlFormatter->setPattern('d MMMM');
        return sprintf('%s от %s, %s', $subj, $this->getFromFull(), $intlFormatter->format($this->received));
    }

    public function hasContacts(): bool
    {
        $phoneRusRegex = '/^.*(\+7|7|8)?[\s\-]?\(?[489][0-9]{2}\)?[\s\-]?[0-9]{3}[\s\-]?[0-9]{2}[\s\-]?[0-9]{2}.*$/';
        $freeEmailDomains = [
            'gmail.com', 'yandex.ru', 'ya.ru', 'mail.ru', 'inbox.ru', 'list.ru', 'bk.ru', 'internet.ru', 'vk.ru', 'mail.com',
            'yahoo.com', 'outlook.com', 'icloud.com'
        ];
        if (!preg_match($phoneRusRegex, $this->body, $matches)) {
            return false;
        }

        $domain = substr(strrchr($this->fromEmail, "@"), 1);
        if (in_array($domain, $freeEmailDomains)) {
            return false;
        }

        return true;
    }
}
