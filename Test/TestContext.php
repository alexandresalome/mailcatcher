<?php

namespace Alex\MailCatcher\Test;

use Alex\MailCatcher\Behat\MailCatcherContext;
use Behat\Behat\Context\BehatContext;

/**
 * Behat context class used for testing.
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
class TestContext extends BehatContext
{
    public function __construct(array $parameters)
    {
        $this->useContext('url', new UrlContext());
        $this->useContext('mailcatcher', new MailCatcherContext());
    }
}
