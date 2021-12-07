<?php

namespace App\MessageHandler;

use App\Entity\SupportRequest;
use App\ImapMail\MailParser;
use App\InboxMail\InboxMailService;
use App\Message\MailMessage;
use App\Repository\InboxMailRepository;
use App\Support\SupportRequestService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
//use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Workflow\WorkflowInterface;

class MailMessageHandler implements MessageHandlerInterface
{
//    private MessageBusInterface $bus;
    private WorkflowInterface $workflow;
    private EntityManagerInterface $entityManager;
    private InboxMailRepository $inboxMailRepository;
    private ?LoggerInterface $logger;
    private SupportRequestService $supportRequestService;
    private InboxMailService $inboxMailService;
    private MailerInterface $mailer;


    public function __construct(
        EntityManagerInterface $entityManager,
//        MessageBusInterface $bus,
        WorkflowInterface $commentStateMachine,
        InboxMailRepository $inboxMailRepository,
        SupportRequestService $supportRequestService,
        InboxMailService $inboxMailService,
        MailerInterface $mailer,
        LoggerInterface $messengerLogger = null
    ) {
        $this->entityManager = $entityManager;
//        $this->bus = $bus;
        $this->workflow = $commentStateMachine;
        $this->inboxMailRepository = $inboxMailRepository;
        $this->supportRequestService = $supportRequestService;
        $this->logger = $messengerLogger;
        $this->inboxMailService = $inboxMailService;
        $this->mailer = $mailer;
    }

    public function __invoke(MailMessage $message)
    {
        $inboxMail = $this->inboxMailRepository->find($message->getId());
        if (!$inboxMail) {
            return;
        }
        $mailParser = new MailParser($inboxMail, $this->logger);

        if ($this->workflow->can($inboxMail, 'has_no_req_id')) {
            $reqId = $mailParser->getReqId();

            if (!$reqId) {
                $transaction = 'has_no_req_id';
            } else {
                $transaction = 'has_something';
            }
            $this->workflow->apply($inboxMail, $transaction);
            $this->entityManager->flush();
//            $this->bus->dispatch($message);

        } elseif ($this->workflow->can($inboxMail, 'prepare_for_send')) {
            $mailStatus = $mailParser->getStatus();
            $transaction = $mailStatus->getWorkflowTransaction();
            $this->workflow->apply($inboxMail, $transaction);
            $this->entityManager->flush();
//            $this->bus->dispatch($message);
        } elseif ($this->workflow->can($inboxMail, 'sent_new')) {
            $supportRequest = new SupportRequest($this->supportRequestService);
            $supportRequest->setReqId($supportRequest->calcRequestId())
                ->setFromEmail($inboxMail->getFromEmail())
                ->setFromName($inboxMail->getFromName())
                ->setRequestText($inboxMail->getBody())
                ->setReceived($inboxMail->getReceived())
                ->setSubject($inboxMail->getSubj());
            $inboxMail->setSupport($supportRequest);
            $html = $this->inboxMailService->makeResponseHtml($inboxMail);
            $subj = $this->inboxMailService->makeResponseSubj($inboxMail);
            $this->logger->debug('ТЕКСТ ОТВЕТА:', ['reply' => $html]);
            $this->workflow->apply($inboxMail, 'sent_new');

            $attachmentName = $this->inboxMailService->makeRequestFile($inboxMail);
            $reply = (new Email())
                ->from(new Address('rating@tagline.ru', 'Агентство Тэглайн'))
                ->text($html)
                ->subject($subj)
                ->to(new Address($inboxMail->getFromEmail(), $inboxMail->getFromName()))
                ->attachFromPath(
                    $attachmentName[0],
                    $attachmentName[1],
                    'text/plain');
            $this->logger->debug('НОВЫЙ ЗАПРОС В ПОДДЕРЖКУ', [$reply, $reply->getBody()]);
            $this->logger->debug('Аттачмент', [$this->inboxMailService->makeRequestFile($inboxMail)]);
            $this->mailer->send($reply);
            $supportRequest->setRepliedText(sprintf('<b>%s</b><br>%s', $subj, $html));
            $this->entityManager->persist($supportRequest);
            $this->entityManager->flush();

//            $this->bus->dispatch($message);
        }
        $this->logger->debug('Dropping comment message', ['state' => $inboxMail->getState()]);
    }
}