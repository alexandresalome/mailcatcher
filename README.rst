Mailcatcher for PHP
===================

.. image:: https://travis-ci.org/alexandresalome/mailcatcher.png?branch=master
   :alt: Build status
   :target: https://travis-ci.org/alexandresalome/mailcatcher

STILL IN DEVELOPMENT

Integrates `Mailcatcher <http://mailcatcher.me>`_ in your PHP application.
Mailcatcher is a simple SMTP server with an HTTP API, and this bundle aims to
integrate it to make it easy to use it with PHP.

Client API
----------

Browse easily your API with the integrated SDK:

.. code-block:: php

    // Client API

    $client = new Alex\Mailcatcher\Client('http://localhost:1080');

    // Returns all messages
    $messages = $client->search();

    // Count messages
    $client->getMessageCount();

    // Filter messages
    $messages = $client->search(array(
        'from'        => 'bob@example.org',
        'to'          => 'alice@example.org',
        'subject'     => 'Bla',
        'contains'    => 'Hello',
        'attachments' => true,
        'format'      => 'html',
    ), $limit = 3);

    // Search one message
    $message = $client->searchOne(array('subject' => 'Welcome'));

    // Message API, return a Person object or an array of Person object
    $message->getFrom();
    $message->getRecipients();

    // Person API
    $person = $message->getFrom();

    $person->getName();
    $person->getMail();

    // Client attachments API
    $client->hasAttachments();
    $client->getAttachments();

    // Attachment API
    $attachment->getFilename();
    $attachment->getSize();
    $attachment->getType();
    $attachment->getContent();

Behat extension
---------------

This extension provides you ability to integrate Mailcatcher steps in your
testing process.

Available steps
:::::::::::::::

* When I open mail from "foo@example.org"
* When I open mail containing "Hello"
* When I click on "Activate" in mail
* Then I should see "Some text"
* Then ``1`` mail should be sent
* Then ``13`` mails should be sent

Configuration
:::::::::::::

.. code-block:: yaml

    default:
    extensions:
        Alex\Mailcatcher\Behat\MailcatcherExtension\Extension:
            url: http://localhost:1080
            purge_before_scenario: true

You can also override on execution using environment variable ``MAILCATCHER_URL``.


* **Purge before scenario**: Automatically drop all messages in Mailcatcher
before executing a scenario
