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
    protected function findMail($type, $value, $negation = false)
    {
        $criterias = array($type => $value);

        $message = $this->getMailCatcherClient()->searchOne($criterias);

        if (null === $message) {
            // If no message was found but we wanted to NOT see a message, return a fake message to make the test pass
            if($negation) return new Message($this->mailCatcherClient);
            throw new \InvalidArgumentException(sprintf('Unable to find a message with criterias "%s".', json_encode($criterias)));
        } else {
            if($negation) {
                // If a message was found but we wanted to NOT see a message, throw an exception
                throw new \InvalidArgumentException(sprintf('A message corresponding to your criterias was found : "%s".', json_encode($criterias)));
            }
        }

        return $message;
    }
}
