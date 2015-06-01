<?php

namespace Alex\MailCatcher\Behat;

use Alex\MailCatcher\Client;
use Alex\MailCatcher\Message;
use Behat\Behat\Context\Context;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Context class for mail browsing and manipulation.
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
class MailCatcherContext implements Context
{
    /**
     * @var Client|null
     */
    protected $client;

    /**
     * @var boolean
     */
    protected $purgeBeforeScenario;

    /**
     * @var Message|null
     */
    protected $currentMessage;

    /**
     * Sets configuration of the context.
     *
     * @param Client  $client client to use for API.
     * @param boolean $purgeBeforeScenario set false if you don't want context to purge before scenario
     */
    public function setConfiguration(Client $client, $purgeBeforeScenario = true)
    {
        $this->client = $client;
        $this->purgeBeforeScenario = $purgeBeforeScenario;
    }

    /**
     * Method used to chain calls. Throws exception if client is missing.
     *
     * @return client
     *
     * @throws \RuntimeException client if missing from context
     */
    public function getClient()
    {
        if (null === $this->client) {
            throw new \RuntimeException(sprintf('Client is missing from MailCatcherContext'));
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
     * @When /^I purge mails$/
     */
    public function purge()
    {
        $this->getClient()->purge();
    }

    /**
     * @When /^I open mail (from|with subject|to|containing) "([^"]+)"$/
     */
    public function openMail($type, $value)
    {
        if ($type === 'with subject') {
            $type = 'subject';
        } elseif ($type === 'containing') {
            $type = 'contains';
        }
        $criterias = [$type => $value];

        $message = $this->getClient()->searchOne($criterias);

        if (null === $message) {
            throw new \InvalidArgumentException(sprintf('Unable to find a message with criterias "%s".', json_encode($criterias)));
        }

        $this->currentMessage = $message;
    }

    /**
     * @Then /^I should see "([^"]+)" in mail$/
     */
    public function seeInMail($text)
    {
        $message = $this->getCurrentMessage();

        if (!$message->isMultipart()) {
            $content = $message->getContent();
        } elseif ($message->hasPart('text/html')) {
            $content = $this->getCrawler($message)->text();
        } elseif ($message->hasPart('text/plain')) {
            $content = $message->getPart('text/plain')->getContent();
        } else {
            throw new \RuntimeException(sprintf('Unable to read mail'));
        }

        if (false === strpos($content, $text)) {
            throw new \InvalidArgumentException(sprintf("Unable to find text \"%s\" in current message:\n%s", $text, $message->getContent()));
        }
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

    /**
     * @return Message|null
     */
    private function getCurrentMessage()
    {
        if (null === $this->currentMessage) {
            throw new \RuntimeException('No message selected');
        }

        return $this->currentMessage;
    }

    /**
     * @param Message $message
     *
     * @return Crawler
     */
    private function getCrawler(Message $message)
    {
        if (!class_exists('Symfony\Component\DomCrawler\Crawler')) {
            throw new \RuntimeException('Can\'t crawl HTML: Symfony DomCrawler component is missing from autoloading.');
        }

        return new Crawler($message->getPart('text/html')->getContent());
    }
}
