<?php

namespace Alex\MailCatcher\Tests;

use Alex\MailCatcher\Client;

abstract class AbstractTest extends \PHPUnit_Framework_TestCase
{
    public function getClient()
    {
        if (!isset($_SERVER['MAILCATCHER_HTTP'])) {
            $this->markTestSkipped('mailcatcher HTTP missing');
        }

        return new Client($_SERVER['MAILCATCHER_HTTP']);
    }

    public function sendMessage(\Swift_Message $message)
    {
        if (!isset($_SERVER['MAILCATCHER_SMTP'])) {
            $this->markTestSkipped('mailcatcher SMTP missing');
        }

        if (!preg_match('#^smtp://(?P<host>[^:]+):(?P<port>\d+)$#', $_SERVER['MAILCATCHER_SMTP'], $vars)) {
            throw new \InvalidArgumentException(sprintf('SMTP URL malformatted. Expected smtp://host:port, got "%s".', $_SERVER['MAILCATCHER_SMTP']));
        }

        $host = $vars['host'];
        $port = $vars['port'];

        static $mailer;
        if (null === $mailer) {
            $transport = \Swift_SmtpTransport::newInstance($host, $port);
            $mailer = \Swift_Mailer::newInstance($transport);
        }

        if (!$mailer->send($message)) {
            throw new \RuntimeException('Unable to send message');
        }
    }

    public function createFixtures()
    {
        $client = $this->getClient();
        $client->purge();

        for ($i = 1; $i <= 7; $i++) {

            // 7 = 2 x 3 + 1
            $detail = floor($i/3).' x 3 + '.($i - floor($i/3)*3);

            $message = \Swift_Message::newInstance()
                ->setSubject($i.' = '.$detail)
                ->setFrom(array('foo'.$i.'@example.org' => 'Foo '.$detail))
                ->setTo(array('bar@example.org' => 'Bar'))
                ->setBody('Bazinga! '.$detail)
            ;

            $this->sendMessage($message);
        }
    }
}
