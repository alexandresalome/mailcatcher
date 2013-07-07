<?php

namespace Alex\Mailcatcher;

class MessageParser
{
    const TOKEN_HEADER_NAME = '[A-Z][-A-Za-z0-9]*';

    private $content;
    private $cursor;

    public function parse($text)
    {
        $this->content = $text;
        $this->cursor = 0;

        try {
            return $this->doParse();
        } catch (\Exception $e) {
            $text = substr($this->content, $this->cursor, 10);

            throw new \InvalidArgumentException(sprintf('Error while parsing "%s" (cursor: %d, text: "%s").', $e, $this->cursor, $text));
        }
    }

    private function doParse()
    {
        $headerBag = $this->parseHeaderBag();

        $this->consume("\n");

        $content = $this->consumeAll();

        return array($headerBag, $content);
    }

    private function parseHeaderBag()
    {
        $headerBag = new HeaderBag();

        while ($this->parseHeader($headerBag)) {
            continue;
        }

        return $headerBag;
    }

    private function parseHeader(HeaderBag $headerBag)
    {
        try {
            $vars = $this->consumeRegexp('/('.self::TOKEN_HEADER_NAME.'): ?/');
            $headerName = $vars[1];
            $value      = $this->consumeTo("\n");
            $this->consume("\n");
            while ($this->expects(" ")) {
                $value .= $this->consumeTo("\n");
                $this->consume("\n");
            }

            $headerBag->add($headerName, $value);
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        return true;
    }

    protected function isFinished()
    {
        return $this->cursor === $this->length;
    }

    protected function consumeAll()
    {
        $rest = substr($this->content, $this->cursor);
        $this->cursor += strlen($rest);

        return $rest;
    }

    protected function expects($expected)
    {
        $length = strlen($expected);
        $actual = substr($this->content, $this->cursor, $length);
        if ($actual !== $expected) {
            return false;
        }

        $this->cursor += $length;

        return true;
    }

    protected function consumeRegexp($regexp)
    {
        if (!preg_match($regexp.'A', $this->content, $vars, null, $this->cursor)) {
            throw new \InvalidArgumentException('No match for regexp '.$regexp.' Upcoming: '.substr($this->content, $this->cursor, 30));
        }

        $this->cursor += strlen($vars[0]);

        return $vars;
    }

    protected function consumeTo($text)
    {
        $pos = strpos($this->content, $text, $this->cursor);

        if (false === $pos) {
            throw new \InvalidArgumentException(sprintf('Unable to find "%s"', $text));
        }

        $result = substr($this->content, $this->cursor, $pos - $this->cursor);
        $this->cursor = $pos;

        return $result;
    }

    protected function consume($expected)
    {
        $length = strlen($expected);
        $actual = substr($this->content, $this->cursor, $length);
        if ($actual !== $expected) {
            throw new \InvalidArgumentException(sprintf('Expected "%s", but got "%s" (%s)', $expected, $actual, substr($this->content, $this->cursor, 10)));
        }
        $this->cursor += $length;

        return $expected;
    }

    protected function consumeNewLine()
    {
        return $this->consume("\n");
    }
}
