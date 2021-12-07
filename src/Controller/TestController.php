<?php

namespace App\Controller;

use App\Entity\InboxMail;
use App\Message\MailMessage;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    /**
     * @Route("/test", name="test")
     */
    public function index(EntityManagerInterface $entityManager): Response
    {
        $InboxMail = new InboxMail();
        $InboxMail->setState('new_to_check')
            ->setFromEmail('aaa')
            ->setFromFull('aaa')
            ->setSubj('aaa')
            ->setSpamStatus('OK')
            ->setMailUid(123);

        $data = [
            'Hi, Тэглайн! Greeting from US. Have a nice day',
            'Это письмо на русском',
        ];
        $index = rand(0, count($data) - 1);
        $InboxMail->setBody($data[$index]);
        $entityManager->persist($InboxMail);
        $entityManager->flush();

        $this->bus->dispatch(new MailMessage($InboxMail->getId()));

//        dd($InboxMail);

        return $this->render('test/index.html.twig', [
            'controller_name' => 'TestController',
        ]);
    }
}
