<?php

namespace Alex\MailCatcher\Test;
use Behat\Behat\Context\Context;

/**
 * Context Behat class, used for testing.
 *
 * @see Alex\MailCatcher\Tests\BehatExtensionTest
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
class UrlContext implements Context
{
    /**
     * Last called URLs.
     *
     * @var string[]
     */
    protected $urls = array();

    /**
     * Returns all URLs stored by context.
     *
     * @param boolean $purge indicates to purge urls in memory an start from an empty array
     *
     * @return array urls
     */
    public function getUrls($purge = false)
    {
        $urls = $this->urls;

        if ($purge) {
            $this->urls = array();
        }

        return $urls;
    }

    /**
     * @When /^I open "([^"]+)"$/
     */
    public function openUrl($url)
    {
        $this->urls[] = $url;
    }
}
