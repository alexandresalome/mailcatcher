<?php

namespace Alex\Mailcatcher\Tests;

class MessageParserTest
{
    public function testSimple()
    {
        $message = <<<EOF
Foo: bar
Bar: baz

Hello world
EOF;

        list($headers, $content) = $parser->parse($message);

        $this->assertEquals('Hello world', $content, 'content is "Hello world"');
        $this->assertEquals('bar', $header->get('Foo'));
        $this->assertEquals('baz', $header->get('Bar'));
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

        list($headers, $content) = $parser->parse($message);

        $this->assertEquals('Hello world', $content, 'content is "Hello world"');
        $this->assertEquals('barbaz', $header->get('Foo'));
        $this->assertEquals('foobarbaz', $header->get('Bar'));
    }
}
