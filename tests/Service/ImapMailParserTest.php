<?php

namespace App\Tests\Service;

use App\Entity\InboxMail;
use App\ImapMail\MailParser;
use App\ImapMail\Status\PotentialHam;
use App\ImapMail\Status\PotentialSpam;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use SecIT\ImapBundle\Service\Imap;

class ImapMailParserTest extends KernelTestCase
{
    private InboxMail $inboxMail;

    public function setUp(): void
    {
        $inboxMail = new InboxMail();
        $testStr = 'test me!';
        $inboxMail->setSubj($testStr)
            ->setState(InboxMail::INIT_STATE)
            ->setFromName($testStr)
            ->setBody($testStr)
            ->setFromEmail('test@test.ru');

        $this->inboxMail = $inboxMail;

        parent::setUp();
    }


    public function testConnection(): void
    {
        $kernel = self::bootKernel();

        $ImapService = $kernel->getContainer()
            ->get(Imap::class);

        $rtrPswParamName = 'rtr_psw';
        $rtrPsw = false;
        if ($kernel->getContainer()->hasParameter($rtrPswParamName) &&
            $kernel->getContainer()->getParameter($rtrPswParamName) != "") {
            $rtrPsw = $kernel->getContainer()->getParameter($rtrPswParamName);
        }

        if (!$rtrPsw) {
            $this->markTestSkipped();
        } else {
            $isConnectable = $ImapService->testConnection('rating_tagline_ru');

            $this->assertTrue($isConnectable);
        }
    }

    /**
     * @dataProvider getSubjs
     */
    public function testHasReqIdInSubj(string $subj, $reqId)
    {
        $this->inboxMail->setSubj($subj);
        $MailParser = new MailParser($this->inboxMail);
        $this->assertSame($MailParser->getReqId(), $reqId);
    }

    public function getSubjs()
    {
        return [
            'success' => ['Re: Я пишу вам впервые…, ID:20211030999', '20211030999'],
            ['Я пишу вам впервые…', false],
            ['Re: Я пишу вам впервые…, ID:2021103099', false],
            ['Re: Я пишу вам впервые…, ID:202g103099', false],
        ];
    }

    /**
     * @dataProvider getBody
     */
    public function testPotentialHam(string $body, bool $isHam)
    {
        $this->inboxMail->setBody($body);
        $MailParser = new MailParser($this->inboxMail);
        $mailStatus = $MailParser->getStatus();
        $spamStatus = new PotentialHam();
        $this->assertSame($mailStatus instanceof $spamStatus, $isHam);
    }

    public function getBody()
    {
        return [
            ['Hi, Тэглайн! Greeting from US. Have a nice day', true],
            ['Hi, Tagline! Greeting from US. Have a nice day', false],
            ['Hi, редакция! Greeting from US. Have a nice day', false],
            ['Это письмо на русском', true],
            ['смена почты' => '<div dir="ltr">Добрый день.<div>Прошу сменить почту в аккаунте с <a href="mailto:marketing.litota@yandex.ru">marketing.litota@yandex.ru</a> на <a href="mailto:marketing@litota.ru">marketing@litota.ru</a> и что для этого нужно?</div></div>

<br>
<div><font size="1" face="Arial" style="background-color:white" color="#000000"><br></font></div><div><font size="1" face="Arial" style="background-color:white" color="#000000"><u>УВЕДОМЛЕНИЕ О КОНФИДЕНЦИАЛЬНОСТИ:</u> Это электронное сообщение и любые документы, приложенные к нему, содержат конфиденциальную информацию. Настоящим уведомляем Вас о том, что если это сообщение не предназначено Вам, использование, копирование, распространение информации, содержащейся в настоящем сообщении, а также осуществление любых действий на основе этой информации запрещено. Если Вы получили это сообщение по ошибке, пожалуйста, сообщите об этом отправителю по электронной почте и удалите это сообщение. </font></div><div><font size="1" face="Arial" style="background-color:white" color="#000000"><br></font></div><div><font size="1" face="Arial" style="background-color:white" color="#000000"><u>CONFIDENTIALITY NOTICE:</u> This email and any files attached to it are confidential. If you are not the intended recipient you are notified that using, copying, distributing or taking any action in reliance on the contents of this information is strictly prohibited. If you have received this email in error please notify the sender and delete this email.</font></div>', true],
        ];
    }
}
