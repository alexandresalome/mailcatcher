<?php

namespace Alex\MailCatcher\Behat\MailCatcherExtension;

use Alex\MailCatcher\Behat\MailCatcherContext;
use Alex\MailCatcher\Client;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer as InitializerInterface;

class ContextInitializer implements InitializerInterface
{
    protected $client;
    protected $purgeBeforeScenario;

    /**
     * @param Client $client
     * @param bool   $purgeBeforeScenario
     */
    public function __construct(Client $client, $purgeBeforeScenario = true)
    {
        $this->client = $client;
        $this->purgeBeforeScenario = $purgeBeforeScenario;
    }

    /**
     * @param Context $context
     *
     * @return bool
     */
    public function supports(Context $context)
    {
        return $context instanceof MailCatcherContext;
    }

    /**
     * @param Context $context
     */
    public function initializeContext(Context $context)
    {
        if (!$context instanceof MailCatcherContext) {
            return;
        }

        $context->setConfiguration($this->client, $this->purgeBeforeScenario);
    }
}
