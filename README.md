# MailCatcher for PHP

![Build status](https://travis-ci.org/alexandresalome/mailcatcher.png?branch=master) [![Latest Stable Version](https://poser.pugx.org/alexandresalome/mailcatcher/v/stable)](https://packagist.org/packages/alexandresalome/mailcatcher) [![Total Downloads](https://poser.pugx.org/alexandresalome/mailcatcher/downloads)](https://packagist.org/packages/alexandresalome/mailcatcher) [![License](https://poser.pugx.org/alexandresalome/mailcatcher/license)](https://packagist.org/packages/alexandresalome/mailcatcher) [![Monthly Downloads](https://poser.pugx.org/alexandresalome/mailcatcher/d/monthly)](https://packagist.org/packages/alexandresalome/mailcatcher) [![Daily Downloads](https://poser.pugx.org/alexandresalome/mailcatcher/d/daily)](https://packagist.org/packages/alexandresalome/mailcatcher)

Integrates [MailCatcher](http://mailcatcher.me) in your PHP application.

* [View CHANGELOG](CHANGELOG.md)
* [View CONTRIBUTORS](CONTRIBUTORS.md)

MailCatcher is a simple SMTP server with an HTTP API, and this library aims to
integrate it to make it easy to use it with PHP.

## Behat extension

This library provides a Behat extension to help you test mails in your application.

To use it, you first need to be sure [MailCatcher](http://mailcatcher.me) is
properly installed and running. You can use docker to execute it:

```bash
docker run -d -p 1080:1080 -p 1025:1025 --name mailcatcher schickling/mailcatcher
```

First, configure in your ``behat.yml``:

```yaml
default:
    extensions:
        Alex\MailCatcher\Behat\MailCatcherExtension\Extension:
            url: http://localhost:1080
            purge_before_scenario: true
```

Then, add the **MailCatcherContext** context in your **FeatureContext** file:

```php
use Alex\MailCatcher\Behat\MailCatcherContext;
use Behat\Behat\Context\BehatContext;

class FeatureContext extends BehatContext
{
   public function __construct(array $parameters)
   {
      $this->useContext('mailcatcher', new MailCatcherContext());
   }
}
```

### Available steps

This extension provides you mail context in your tests. To use assertions, you
must first **open a mail** using criterias.

Once it's opened, you can make **assertions** on it and **click** in it.

**Server manipulation**

Deletes all messages on the server

* When I purge mails

**Mail opening**

* When I open mail from "**foo@example.org**"
* When I open mail containing "**a message**"
* When I open mail to "**me@example.org**"
* When I open mail with subject "**Welcome, mister Bond!**"

**Assertion**

Verify number of messages sent to the server:

* Then **1** mail should be sent
* Then **13** mails should be sent

Verify text presence in message:

* Then I should see "**something**" in mail
* Then I should see "**something else**" in mail
* Then I should see "**something else**" in mail source

Verify text presence in mail without opening:

* Then I should see mail from "**foo@example.org**"
* Then I should see mail containing "**a message**"
* Then I should see mail to "**me@example.org**"
* Then I should see mail with subject "**Welcome, mister Bond!**"

### Custom mailcatcher context

**Only available from PHP 5.4**

If you want to create a context class that relates to MailCatcher, you can use the **MailCatcherTrait** to get the mailcatcher client injected inside your class:

```php
use Alex\MailCatcher\Behat\MailCatcherAwareInterface;
use Alex\MailCatcher\Behat\MailCatcherTrait;
use Alex\MailCatcher\Message;
use Behat\Behat\Context\Context;

class WelcomeContext implements Context, MailCatcherAwareInterface
{
    use MailCatcherTrait;

    /**
     * @Then /^a welcome mail should be sent$/
     */
    public function testTrait()
    {
        $this->findMail(Message::SUBJECT_CRITERIA, 'Welcome!');
    }
}
```

This trait offers the following methods:

* **getMailCatcherClient()**: returns the mailcatcher **Client**  instance.
* **findMail($criteria, $value)**: facility to search for a given message, or throws an exception if not found

**Don't forget** to implement the **MailCatcherAwareInterface** to get the mailcatcher client injected inside your context class.

## Client API

Browse easily your API with the integrated SDK:

```php
$client = new Alex\MailCatcher\Client('http://localhost:1080');

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
```

**Message API**

```php
// Message API, get the content of a message
$subject = $message->getSubject();
$plainTextBody = $message->getPart('text/plain')->getContent();
$htmlBody = $message->getPart('text/html')->getContent();

// Message API, return a Person object or an array of Person object
$person  = $message->getFrom();
$persons = $message->getRecipients();

// Person API
$person = $message->getFrom();

$name = $person->getName(); // null means not provided
$mail = $person->getMail();

// Attachments
$message->hasAttachments();
$message->getAttachments();

// Delete
$message->delete();
```

**Attachment API**

```php
// Attachment API
$attachment->getFilename();
$attachment->getSize();
$attachment->getType();
$attachment->getContent();
```
