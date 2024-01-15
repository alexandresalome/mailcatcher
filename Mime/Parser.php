<?php

namespace Alex\MailCatcher\Mime;

class Parser
{
    const TOKEN_HEADER_NAME = '[A-Za-z][-A-Za-z0-9]*';

    private $content;
    private $cursor;

    /**
     * @param $content
     * @param $boundary
     *
     * @return array|null
     */
    public function parseBoundary($content, $boundary)
    {
        $content = str_replace("\r", '', $content); // acceptable

        $this->content = $content;
        $this->cursor  = 0;

        try {
            return $this->doParseBoundary($boundary);
        } catch (\Exception $e) {
            $text = substr($this->content, $this->cursor, 10);

            throw new \InvalidArgumentException(sprintf('Error while parsing "%s" (cursor: %d, text: "%s").'."\n%s", $e->getMessage(), $this->cursor, $text, $e->getTraceAsString()));
        }
    }

    /**
     * @param $text
     *
     * @return array
     */
    public function parsePart($text)
    {
        $text = str_replace("\r", '', $text); // acceptable

        $this->content = $text;
        $this->cursor  = 0;

        try {
            return $this->doParsePart();
        } catch (\Exception $e) {
            $text = substr($this->content, $this->cursor, 10);

            throw new \InvalidArgumentException(sprintf('Error while parsing "%s" (cursor: %d, text: "%s").'."\n%s", $e->getMessage(), $this->cursor, $text, $e->getTraceAsString()));
        }
    }

    /**
     * @param $boundary
     *
     * @return array|null
     */
    private function doParseBoundary($boundary)
    {
        $result = array();
        $prefix = "--".$boundary;

        $this->consumeRegexp("/\n*/");
        $this->consumeTo($prefix);
        $this->consume($prefix);

        while ($this->expects("\n")) {
            $content = $this->consumeTo("\n".$prefix);

            $part = new Part();
            $part->loadSource($content);
            if ($part->isMultipart()) {
                $result = array_merge($result, $part->getParts());
            } else {
                $result[] = $part;
            }

            $this->consume("\n".$prefix);
        }

        return $result;
    }

    /**
     * @return array
     */
    private function doParsePart()
    {
        $headerBag = $this->parseHeaderBag();

        $this->consume("\n");

        $content = $this->consumeAll();

        if ($headerBag->get('Content-Transfer-Encoding') == 'quoted-printable') {
            $content = quoted_printable_decode(rtrim($content, "\n"));
        }

        return array($headerBag, $content);
    }

    /**
     * @return HeaderBag
     */
    private function parseHeaderBag()
    {
        $headerBag = new HeaderBag();

        while ($this->parseHeader($headerBag)) {
            continue;
        }

        return $headerBag;
    }

    /**
     * @param HeaderBag $headerBag
     *
     * @return bool
     */
    private function parseHeader(HeaderBag $headerBag)
    {
        try {
            $vars = $this->consumeRegexp('/('.self::TOKEN_HEADER_NAME.'): ?/');
            $headerName = $vars[1];
            $value      = $this->consumeTo("\n");
            $this->consume("\n");
            while ($this->expects(" ") || $this->expects("\t")) {
                $value .= $this->consumeTo("\n");
                $this->consume("\n");
            }

            $headerBag->set($headerName, $value);
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function isFinished()
    {
        return $this->cursor === $this->length;
    }

    /**
     * @return string
     */
    protected function consumeAll()
    {
        $rest = substr($this->content, $this->cursor);
        $this->cursor += strlen($rest);

        return $rest;
    }

    /**
     * @param $expected
     *
     * @return bool
     */
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

    /**
     * @param $regexp
     *
     * @return mixed
     */
    protected function consumeRegexp($regexp)
    {
        if (!preg_match($regexp.'A', $this->content, $vars, 0, $this->cursor)) {
            throw new \InvalidArgumentException('No match for regexp '.$regexp.' Upcoming: '.substr($this->content, $this->cursor, 30));
        }

        $this->cursor += strlen($vars[0]);

        return $vars;
    }

    /**
     * @param $text
     *
     * @return string
     */
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

    /**
     * @param $expected
     *
     * @return mixed
     */
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

    /**
     * @return mixed
     */
    protected function consumeNewLine()
    {
        return $this->consume("\n");
    }
}
