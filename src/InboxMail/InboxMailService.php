<?php

namespace App\InboxMail;

use App\Entity\InboxMail;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;
use Html2Text\Html2Text;

class InboxMailService
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function makeResponseHtml(InboxMail $mail): string
    {
        return $this->twig->render('@autoresponder/layout.html.twig', [
            'email' => $mail,
        ]);
    }

    public function makeResponseSubj(InboxMail $mail): string
    {
        return $this->twig->render('@autoresponder/subject.html.twig', [
            'email' => $mail,
        ]);
    }


    /**
     * @return string[]
     */
    public function makeRequestFile(InboxMail $mail): array
    {
        $filesystem = new Filesystem();
        $path = sys_get_temp_dir().'/attachments';

        if (!$filesystem->exists($path)) {
            try {
                $filesystem->mkdir($path, 0700);
            } catch (IOExceptionInterface $exception) {
                echo "An error occurred while creating your directory at ".$exception->getPath();
            }
        }

        $filename = $mail->getId().'_'.md5(random_int(0, 1000)).'.txt';
        $fileNameAndPath = $path.'/'.$filename;
        $html = new Html2Text($mail->getBody());
        $filesystem->dumpFile($fileNameAndPath, $html->getText());
        $attachedFileName = 'Текст обращения #'.$mail->getSupport()->getReqId().'.txt';

        return [$fileNameAndPath, $attachedFileName];
    }
}