<?php

namespace Alex\MailCatcher\Behat\MailCatcherExtension;

use Alex\MailCatcher\Behat\MailCatcherContext;
use Alex\MailCatcher\Client;
use Behat\Behat\Context\ContextInterface;
use Behat\Behat\Context\Initializer\InitializerInterface;

class ContextInitializer implements InitializerInterface
{
    protected $client;
    protected $purgeBeforeScenario;

    public function __construct(Client $client, $purgeBeforeScenario = true)
    {
        $this->client = $client;
        $this->purgeBeforeScenario = $purgeBeforeScenario;
    }

    public function supports(ContextInterface $context)
    {
        return $context instanceof MailCatcherContext;
    }

    public function initialize(ContextInterface $context)
    {
        $context->setConfiguration($this->client, $this->purgeBeforeScenario);
    }
}
