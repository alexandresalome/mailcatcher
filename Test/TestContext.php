<?php

namespace Alex\MailCatcher\Test;

use Alex\MailCatcher\Behat\MailCatcherContext;
use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

/**
 * Behat context class used for testing.
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
class TestContext implements Context
{
    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();

        $this->urlContext = $environment->getContext('Behat\MinkExtension\Context\MinkContext');
    }
}
