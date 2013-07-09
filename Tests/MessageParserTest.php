<?php

namespace Alex\MailCatcher\Tests;
use Alex\MailCatcher\Mime\Parser;

class MessageParserTest extends \PHPUnit_Framework_TestCase
{
    public function testSimple()
    {
        $message = <<<EOF
Foo: bar
Bar: baz

Hello world
EOF;

        $parser = new Parser();
        list($headers, $content) = $parser->parsePart($message);

        $this->assertEquals('Hello world', $content, 'content is "Hello world"');
        $this->assertEquals('bar', $headers->get('Foo'));
        $this->assertEquals('baz', $headers->get('Bar'));
    }

    public function testHeaderMultiline()
    {
        $message = <<<EOF
Foo: bar
 baz
Bar: foo
 bar
 baz

Hello world
EOF;

        $parser = new Parser();
        list($headers, $content) = $parser->parsePart($message);

        $this->assertEquals('Hello world', $content, 'content is "Hello world"');
        $this->assertEquals('barbaz', $headers->get('Foo'));
        $this->assertEquals('foobarbaz', $headers->get('Bar'));
    }
}
