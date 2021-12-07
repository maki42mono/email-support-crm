<?php

namespace App\Entity;

use App\Repository\SupportRequestRepository;
use App\Support\SupportRequestService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use App\Exceptions\InvalidMaxDayRequestsException;
use Symfony\Component\Validator\Constraints\Date;

/**
 * @ORM\Entity(repositoryClass=SupportRequestRepository::class)
 */
class SupportRequest
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $reqId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fromEmail;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fromName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $subject;

    /**
     * @ORM\OneToMany(targetEntity=InboxMail::class, mappedBy="support")
     */
    private $inboxMails;

    private $supportRequestService;

    /**
     * @ORM\Column(type="datetime")
     */
    private $received;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $repliedText;

    /**
     * @ORM\Column(type="text")
     */
    private $requestText;

    public function __construct(SupportRequestService $supportRequestService)
    {
        $this->supportRequestService = $supportRequestService;
        $this->inboxMails = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReqId(): ?string
    {
        return $this->reqId;
    }

    public function setReqId(string $reqId): self
    {
        $this->reqId = $reqId;

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

    public function getFromName(): ?string
    {
        return $this->fromName;
    }

    public function setFromName(?string $fromName): self
    {
        $this->fromName = $fromName;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return Collection|InboxMail[]
     */
    public function getInboxMails(): Collection
    {
        return $this->inboxMails;
    }

    public function addInboxMail(InboxMail $inboxMail): self
    {
        if (!$this->inboxMails->contains($inboxMail)) {
            $this->inboxMails[] = $inboxMail;
            $inboxMail->setSupport($this);
        }

        return $this;
    }

    public function removeInboxMail(InboxMail $inboxMail): self
    {
        if ($this->inboxMails->removeElement($inboxMail)) {
            // set the owning side to null (unless already changed)
            if ($inboxMail->getSupport() === $this) {
                $inboxMail->setSupport(null);
            }
        }

        return $this;
    }

    public function calcRequestId(): string
    {
        $suffix = $this->getRequestSuffix();
        $today = new \DateTime('now');
        return $today->format('Ymd') . $suffix;
    }

    /**
     * @throws InvalidMaxDayRequestsException
     */
    public function getRequestSuffix(): string
    {
        $dayMaxRequests = $this->supportRequestService->getDayMaxRequests();
        if (!is_int($dayMaxRequests / 10)) {
            throw new InvalidMaxDayRequestsException(sprintf('dayMaxRequests должно быть 10, 100, 1000 и т.д., у вас %d', $dayMaxRequests));
        }
        $todayRequests = (string)$this->supportRequestService->getTodayRequestsCount();
        $todayRequests++;
        $symbolsInNumber = log10($dayMaxRequests);

        while (mb_strlen($todayRequests) < $symbolsInNumber) {
            $todayRequests = '0' . $todayRequests;
        }

        return $todayRequests;
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

    public function __toString()
    {
        $intlFormatter = new \IntlDateFormatter('ru_RU', \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT);
        $intlFormatter->setPattern('d MMMM');
        $when = $intlFormatter->format($this->received);
        $offset = 25;
        $suffix = mb_strlen($this->subject) > $offset ? "…" : "";
        $subj = mb_substr($this->subject, 0, $offset);
        return sprintf('#%d %s%s (%s, %s %s)', $this->reqId, $subj, $suffix, $when, $this->fromEmail, $this->fromName);
    }

    public function getFromFull(): string
    {
        $res = $this->getFromEmail();
        if (null !== $this->getFromName()) {
            $res = $this->getFromName() . ' ' . $res;
        }
        return $res;
    }

    public function getRepliedText(): ?string
    {
        return $this->repliedText;
    }

    public function setRepliedText(string $repliedText): self
    {
        $this->repliedText = $repliedText;

        return $this;
    }

    public function getRequestText(): ?string
    {
        return $this->requestText;
    }

    public function setRequestText(string $requestText): self
    {
        $this->requestText = $requestText;

        return $this;
    }
}
