<?php

namespace Alex\MailCatcher\Behat;

use Alex\MailCatcher\Client;
use Alex\MailCatcher\Message;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\TranslatableContext;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Context class for mail browsing and manipulation.
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
class MailCatcherContext implements Context, TranslatableContext, MailCatcherAwareInterface
{
    /**
     * This property is duplicated from MailCatcherTrait for support in PHP 5.3
     *
     * @var Client|null
     */
    protected $mailCatcherClient;

    /**
     * @var boolean
     */
    protected $purgeBeforeScenario;

    /**
     * @var Message|null
     */
    protected $currentMessage;

    /**
     * Sets mailcatcher configuration.
     *
     * @param boolean $purgeBeforeScenario set false if you don't want context to purge before scenario
     */
    public function setMailCatcherConfiguration($purgeBeforeScenario = true)
    {
        $this->purgeBeforeScenario = $purgeBeforeScenario;
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
        try {
            $this->getMailCatcherClient()->purge();
        } catch (\Exception $e) {
            @trigger_error("Unable to purge mailcatcher: ".$e->getMessage());
        }
    }


    /**
     * @When /^I purge mails$/
     */
    public function purge()
    {
        $this->getMailCatcherClient()->purge();
    }

    /**
     * @When I open mail from :from
     */
    public function openMailFrom($from)
    {
        $message = $this->findMail(Message::FROM_CRITERIA, $from);

        $this->currentMessage = $message;
    }

    /**
     * @When I open mail with subject :subject
     */
    public function openMailSubject($subject)
    {
        $message = $this->findMail(Message::SUBJECT_CRITERIA, $subject);

        $this->currentMessage = $message;
    }

    /**
     * @When I open mail to :to
     */
    public function openMailTo($to)
    {
        $message = $this->findMail(Message::TO_CRITERIA, $to);

        $this->currentMessage = $message;
    }

    /**
     * @When I open mail containing :value
     */
    public function openMailContaining($value)
    {
        $message = $this->findMail(Message::CONTAINS_CRITERIA, $value);

        $this->currentMessage = $message;
    }

    /**
     * @Then I should see mail from :from
     */
    public function seeMailFrom($from)
    {
        $message = $this->findMail(Message::FROM_CRITERIA, $from);
    }

    /**
     * @Then /^I should see mail with subject "([^"]+)"$/
     */
    public function seeMailSubject($value)
    {
        $message = $this->findMail(Message::SUBJECT_CRITERIA, $value);
    }

    /**
     * @Then /^I should see mail to "([^"]+)"$/
     */
    public function seeMailTo($value)
    {
        $message = $this->findMail(Message::TO_CRITERIA, $value);
    }

    /**
     * @Then /^I should see mail containing "([^"]+)"$/
     */
    public function seeMailContaining($value)
    {
        $message = $this->findMail(Message::CONTAINS_CRITERIA, $value);
    }


    /**
     * @Then I should see :text in mail
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
     * @Then :count mails should be sent
     * @Then :count mail should be sent
     */
    public function verifyMailsSent($count)
    {
        $count = (int) $count;
        $actual = $this->getMailCatcherClient()->getMessageCount();

        if ($count !== $actual) {
            throw new \InvalidArgumentException(sprintf('Expected %d mails to be sent, got %d.', $count, $actual));
        }
    }

    /**
     * Returns list of definition translation resources paths.
     *
     * @return array
     */
    public static function getTranslationResources()
    {
        return glob(__DIR__.'/../i18n/*.xliff');
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

    /**
     * This method is duplicated from MailCatcherTrait, for support in PHP 5.3
     *
     * Sets the mailcatcher client.
     *
     * @param Client  $client a mailcatcher client
     */
    public function setMailCatcherClient(Client $client)
    {
        $this->mailCatcherClient = $client;
    }

    /**
     * This method is duplicated from MailCatcherTrait, for support in PHP 5.3
     *
     * Returns the mailcatcher client.
     *
     * @return Client
     *
     * @throws \RuntimeException client if missing from context
     */
    public function getMailCatcherClient()
    {
        if (null === $this->mailCatcherClient) {
            throw new \RuntimeException(sprintf('No MailCatcher client injected.'));
        }

        return $this->mailCatcherClient;
    }

    /**
     * This method is duplicated from MailCatcherTrait, for support in PHP 5.3
     *
     * @return Message
     */
    protected function findMail($type, $value)
    {
        $criterias = array($type => $value);

        $message = $this->getMailCatcherClient()->searchOne($criterias);

        if (null === $message) {
            throw new \InvalidArgumentException(sprintf('Unable to find a message with criterias "%s".', json_encode($criterias)));
        }

        return $message;
    }

}
