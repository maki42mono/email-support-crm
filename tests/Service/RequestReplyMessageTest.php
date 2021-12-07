<?php

namespace App\Tests\Service;

use App\Entity\InboxMail;
use App\Entity\SupportRequest;
use App\InboxMail\InboxMailService;
use App\Support\SupportRequestService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class RequestReplyMessageTest extends KernelTestCase
{
    public function testSomething(): void
    {
        $kernel = self::bootKernel();

        $supportRequestService = $this->createMock(SupportRequestService::class);

        $request = (new SupportRequest($supportRequestService))
                        ->setReqId('12345');

        $incomeEmail = (new InboxMail())
            ->setFromName('Анна Агафьевна')
            ->setFromEmail('test@yandаex.ru')
            ->setBody('Привет! Можно к вам обратиться? Мой номер: +7 901 900 1234')
            ->setSubj('Обращение')
            ->setSupport($request);

        $html = $kernel->getContainer()->get('test.'.InboxMailService::class)->makeResponseHtml($incomeEmail);
        $mailer = $kernel->getContainer()->get('test.'.MailerInterface::class);

        $reply = (new Email())
            ->from('test@tagline.ru')
            ->to('maksim.pukh@gmail.com')
            ->text($html);

        $mailer->send($reply);
        dump($reply);

        $this->assertTrue(true);
    }
}
