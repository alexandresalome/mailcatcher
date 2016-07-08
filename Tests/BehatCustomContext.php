<?php

namespace Alex\MailCatcher\Tests;

use Alex\MailCatcher\Message;
use Behat\Behat\Context\Context;

class BehatCustomContext implements Context
{
    /**
     * @When /^I do something$/
     */
    public function testSomething()
    {
        // test step
    }
}
