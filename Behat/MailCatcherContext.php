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
    use MailCatcherTrait;

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
        $this->getMailCatcherClient()->purge();
    }


    /**
     * @When /^I purge mails$/
     */
    public function purge()
    {
        $this->getMailCatcherClient()->purge();
    }

    /**
     * @When /^I open mail from "([^"]+)"$/
     */
    public function openMailFrom($value)
    {
        $message = $this->findMail(Message::FROM_CRITERIA, $value);

        $this->currentMessage = $message;
    }

    /**
     * @When /^I open mail with subject "([^"]+)"$/
     */
    public function openMailSubject($value)
    {
        $message = $this->findMail(Message::SUBJECT_CRITERIA, $value);

        $this->currentMessage = $message;
    }

    /**
     * @When /^I open mail to "([^"]+)"$/
     */
    public function openMailTo($value)
    {
        $message = $this->findMail(Message::TO_CRITERIA, $value);

        $this->currentMessage = $message;
    }

    /**
     * @When /^I open mail containing "([^"]+)"$/
     */
    public function openMailContaining($value)
    {
        $message = $this->findMail(Message::CONTAINS_CRITERIA, $value);

        $this->currentMessage = $message;
    }

    /**
     * @Then /^I should see mail from "([^"]+)"$/
     */
    public function seeMailFrom($value)
    {
        $message = $this->findMail(Message::FROM_CRITERIA, $value);
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
}
