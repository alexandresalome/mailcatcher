<?php

namespace Alex\MailCatcher\Tests;

use Alex\MailCatcher\Behat\MailCatcherAwareInterface;
use Alex\MailCatcher\Behat\MailCatcherTrait;
use Alex\MailCatcher\Message;
use Behat\Behat\Context\Context;

class BehatCustomContext implements Context, MailCatcherAwareInterface
{
    use MailCatcherTrait;

    /**
     * @Then /^a welcome mail should be sent$/
     */
    public function testTrait()
    {
        $this->findMail(Message::SUBJECT_CRITERIA, 'Welcome!');
    }
}
