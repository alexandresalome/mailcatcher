<?php

namespace Alex\MailCatcher\Tests;

class ClientTest extends AbstractTest
{
    public function testPurge_getMessageCount()
    {
        $client = $this->getClient();
        $client->purge();
        $this->assertEquals(0, $client->getMessageCount());

        $message = \Swift_Message::newInstance()
            ->setSubject('Hello')
            ->setFrom(array('foo@example.org' => 'Foo'))
            ->setTo(array('bar@example.org' => 'Bar'))
            ->setBody('Baz')
        ;

        $this->sendMessage($message);
        $this->assertEquals(1, $client->getMessageCount());
        $this->sendMessage($message);
        $this->sendMessage($message);
        $this->assertEquals(3, $client->getMessageCount());

        $client->purge();
        $this->assertEquals(0, $client->getMessageCount());
    }

    public function testSearch()
    {
        $client = $this->getClient();
        $this->createFixtures();

        // searchOne
        $message = $client->searchOne(array('subject' => '3 ='));
        $this->assertInstanceOf('Alex\MailCatcher\Message', $message);
        $this->assertEquals('3 = 1 x 3 + 0', $message->getSubject());

        // search
        $messages = $client->search();
        $this->assertCount(7, $messages);

        // search: contains
        $messages = $client->search(array('contains' => '+ 1'));
        $this->assertCount(3, $messages);
    }

    public function testAttachment()
    {
        $client = $this->getClient();
        $client->purge();

        $message = \Swift_Message::newInstance()
            ->setSubject('Hello')
            ->setFrom(array('foo@example.org' => 'Foo'))
            ->setTo(array('bar@example.org' => 'Bar'))
            ->setBody('Baz')
            ->attach(new \Swift_Attachment('foobar', 'foo.txt', 'text/plain'))
        ;

        $this->sendMessage($message);

        $message = $client->searchOne();

        $this->assertInstanceOf('Alex\MailCatcher\Message', $message, "message exists");
        $this->assertTrue($message->hasAttachments(), "message has attachments");

        $attachments = $message->getAttachments();
        $attachment = $attachments[0];

        $this->assertEquals('foo.txt', $attachment->getFilename(), "attachment filename is correct");
        $this->assertEquals(6, $attachment->getSize(), "attachment size is correct");
        $this->assertEquals('text/plain', $attachment->getType(), "attachment type is correct");
        $this->assertEquals('foobar', $attachment->getContent(), 'attachment content is correct');
    }

    public function testMultipart()
    {
        $client = $this->getClient();
        $client->purge();

        $message = \Swift_Message::newInstance()
            ->setSubject('Multipart')
            ->setFrom(array('foo@example.org' => 'Foo'))
            ->setTo(array('bar@example.org' => 'Bar'))
            ->addPart($html = str_repeat('<p>foo</p> ', 30), 'text/html')
            ->addPart($text = str_repeat('foo ', 50), 'text/plain')
        ;

        $this->sendMessage($message);

        $message = $client->searchOne();

        $content = $message->getContent();

        $this->assertTrue($message->isMultipart());
        $this->assertEquals($html, $message->getPart('text/html')->getContent());
        $this->assertEquals($text, $message->getPart('text/plain')->getContent());
    }
}
