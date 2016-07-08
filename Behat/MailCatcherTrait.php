<?php

namespace Alex\MailCatcher\Behat;

use Alex\MailCatcher\Client;

trait MailCatcherTrait
{
    /**
     * @var Client|null
     */
    protected $mailCatcherClient;

    /**
     * Sets the mailcatcher client.
     *
     * @param Client  $client a mailcatcher client
     */
    public function setMailCatcherClient(Client $client)
    {
        $this->mailCatcherClient = $client;
    }

    /**
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
