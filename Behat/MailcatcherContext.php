<?php

namespace Alex\Mailcatcher\Behat;

use Alex\Mailcatcher\Client;
use Behat\Behat\Context\BehatContext;

class MailcatcherContext extends BehatContext
{
    protected $client;
    protected $purgeBeforeScenario;
    protected $currentMessage;

    public function setConfiguration(Client $client, $purgeBeforeScenario = true)
    {
        $this->client = $client;
        $this->purgeBeforeScenario = $purgeBeforeScenario;
    }

    public function getClient()
    {
        if (null === $this->client) {
            throw new \RuntimeException(sprintf('Client is missing from MailcatcherContext'));
        }

        return $this->client;
    }

    /**
     * @BeforeScenario
     */
    public function beforeScenario()
    {
        if (!$this->purgeBeforeScenario) {
            return;
        }

        $this->currentMessage = null;
        $this->getClient()->purge();
    }

    /**
     * @When /^I open mail (from|with subject|to|containing) "([^"]+)"$/
     */
    public function lol($type, $value)
    {
        if ($type === 'with subject') {
            $type = 'subject';
        }
        $criterias = array(array($type, $value));

        $message = $this->getClient()->searchOne($criterias);

        if (null === $message) {
            throw new \InvalidArgumentException(sprintf('Unable to find a message with criterias "%s".', json_encode($criterias)));
        }

        $this->currentMessage = $message;
    }

    /**
     * @Then /^I should see "([^"]+)" in mail$/
     */
    public function seeInMail()
    {
        $message = $this->getCurrentMessage();

    }

    /**
     * @Then /^I click "[^"]+" in mail$/
     */
    public function clickInMail()
    {
    }

    /**
     * @Then /^(?P<count>\d+) mails? should be sent$/
     */
    public function verifyMailsSent($count)
    {
        $count = (int) $count;
        $actual = $this->getClient()->getMessageCount();

        if ($count !== $actual) {
            throw new \InvalidArgumentException(sprintf('Expected %d mails to be sent, got %d.', $count, $actual));
        }
    }

    private function getCurrentMessage()
    {
        if (null === $this->currentMessage) {
            throw new \RuntimeException('No message selected');
        }


        return $this->message;
    }
}
