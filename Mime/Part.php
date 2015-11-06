<?php

namespace Alex\MailCatcher\Mime;

class Part
{
    /**
     * @var HeaderBag
     */
    protected $headers;

    /**
     * @var string
     */
    protected $content;

    protected $parts = null;

    /**
     * Load source part.
     *
     * @param $source
     */
    public function loadSource($source)
    {
        $parser = new Parser();

        list($this->headers, $this->content) = $parser->parsePart($source);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return HeaderBag
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return boolean
     */
    public function isMultipart()
    {
        if (null === $this->parts) {
            $this->loadParts();
        }

        return false !== $this->parts;
    }

    /**
     * @return null
     */
    public function getParts()
    {
        if (null === $this->parts) {
            $this->loadParts();
        }

        if (false === $this->parts) {
            throw new \RuntimeException('Can\'t get parts: message is not multipart');
        }

        return $this->parts;
    }

    /**
     * @param $type
     *
     * @return bool
     */
    public function hasPart($type)
    {
        try {
            $this->getPart($type);

            return true;
        } catch (\InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * @param $type
     *
     * @return mixed
     */
    public function getPart($type)
    {
        $parts = $this->getParts();

        foreach ($parts as $part) {
            if (0 === strpos($part->getHeaders()->get('Content-Type'), $type)) {
                return $part;
            }
        }

        throw new \InvalidArgumentException(sprintf('Unable to find part with Content-Type "%s" in parts. Got: %s', $type, implode("", array_map(function ($part) {
            return "\n- ".$part->getHeaders()->get('Content-Type');
        }, $parts))));
    }

    /**
     *
     */
    private function loadParts()
    {
        $content = $this->getContent();
        $headers = $this->getHeaders();

        if (null === $content || null === $headers) {
            throw new \RuntimeException('Unable to load part: no content or headers set in message.');
        }

        $contentType = $headers->get('Content-Type');
        if (0 !== strpos($contentType, 'multipart')) {
            $this->parts = false;

            return;
        }

        if (!preg_match('#^multipart/(alternative|mixed|related);\s*boundary="?([^"]*)"?$#', $contentType, $vars)) {
            throw new \InvalidArgumentException(sprintf('Unable to parse multipart header: "%s".', $contentType));
        }

        $parser = new Parser();
        $this->parts = $parser->parseBoundary($content, $vars[2]);

    }
}
