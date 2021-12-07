<?php

namespace App\EventSubscriber;

use App\Entity\InboxMail;
use App\Message\MailMessage;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class InboxMailListener implements EventSubscriberInterface
{
    private ?LoggerInterface $logger;
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus, LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        $this->bus = $bus;
    }

    public function preUpdate(InboxMail $inboxMail, PreUpdateEventArgs $event)
    {
        if ($event->getNewValue('state') !== $event->getOldValue('state')) {
            $this->bus->dispatch(new MailMessage($inboxMail->getId()));
        }

    }

    public static function getSubscribedEvents()
    {
        return [];
    }
}
