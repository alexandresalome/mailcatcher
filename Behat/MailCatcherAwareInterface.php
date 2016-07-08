<?php

namespace Alex\MailCatcher\Behat;

use Alex\MailCatcher\Client;

interface MailCatcherAwareInterface
{
    /**
     * Sets the mailcatcher client.
     *
     * @param Client  $client a mailcatcher client
     */
    public function setMailCatcherClient(Client $client);
}
