<?php

namespace App\Command;

use App\Entity\InboxMail;
use App\ImapMail\MailGetter;
use App\Message\MailMessage;
use Doctrine\ORM\EntityManagerInterface;
use SecIT\ImapBundle\Service\Imap;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class GetIncomeEmailCommand extends Command
{
    protected static $defaultName = 'app:get-income-email';
    protected static $defaultDescription = 'Get new emails to parse to support';
    private $imap;
    private $entityManager;
    private $bus;

    public function __construct(Imap $imap, EntityManagerInterface $entityManager, MessageBusInterface $bus)
    {
        $this->imap = $imap;
        $this->entityManager = $entityManager;
        $this->bus = $bus;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        }

        $mailGetter = new MailGetter($this->imap);
        $todayMails = $mailGetter->getTodayMail();
        $repository = $this->entityManager->getRepository(InboxMail::class);
        foreach ($todayMails as $email) {
            $exists = $repository->findOneBy(['mailUid' => $email->id]);
            if (isset($exists)) {
                continue;
            }

            $inboxMail = new InboxMail();
            $inboxMail->setMailUid($email->id)
                ->setSubj($email->subject)
                ->setFromEmail($email->fromAddress)
                ->setBody($email->textHtml ?? $email->textPlain)
                ->setReceived(new \DateTime($email->date))
                ->setState(InboxMail::INIT_STATE);

            if (isset($email->fromName)) {
                $inboxMail->setFromName($email->fromName);
            }

            $this->entityManager->persist($inboxMail);
            $this->bus->dispatch(new MailMessage($inboxMail->getId()));
        }
        $this->entityManager->flush();

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
