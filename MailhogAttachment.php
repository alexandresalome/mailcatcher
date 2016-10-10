<?php

namespace Alex\MailCatcher;

/**
 * Attachment of a message.
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 * @author Christoph Gross <gross@gross-it.com>
 */
class MailhogAttachment
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var string
     */
    protected $type;

    /**
     * Content of attachment.
     *
     * @var string
     */
    protected $content;

    /**
     * Attachment CID
     *
     * @var string
     */
    protected $cid;

    /**
     * Encoding of content
     *
     * @var string
     */
    protected $encoding = false;

    /**
     * Raw content
     *
     * @var string
     */
    protected $rawContent;

    /**
     * Constructor.
     *
     * @param Client $client
     * @param array  $data
     */
    public function __construct(Client $client, array $data = array())
    {
        $this->client = $client;
        $this->loadFromArray($data);
    }

    /**
     * Loads data into the Attachment from an array.
     *
     * @param array $array
     *
     * @return Attachment
     */
    public function loadFromArray(array $array)
    {
        $headers = $array['Headers'];
        $contentDisposition = $headers['Content-Disposition'][0];
        $contentType = $headers['Content-Type'][0];

        if (preg_match('/filename=(.*)/', $contentDisposition, $match) === 1) {
            $this->filename = $match[1];
        }

        # Mailcatcher seems to count this differently => see getSize()
//        if (isset($array['Size'])) {
//            $this->size = $array['Size'];
//        }

        if (preg_match('/(.*);/', $contentType, $match) === 1) {
            $this->type = $match[1];
        }

        if (isset($array['id'])) {
            $this->cid = $array['id'];
        }

        if (isset($headers['Content-Transfer-Encoding']) && count($headers['Content-Transfer-Encoding']) > 0) {
            $this->encoding = $headers['Content-Transfer-Encoding'][0];
        }

        if (isset($array['Body'])) {
            $this->rawContent = $array['Body'];
        }
        return $this;
    }

    /**
     * Returns filename.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Returns size.
     *
     * @return int
     */
    public function getSize()
    {
        return strlen($this->getContent());
    }

    /**
     * Returns type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns CID.
     *
     * @return string
     */
    public function getCid()
    {
        return $this->cid;
    }

    /**
     * Returns content, decoded if possible
     *
     * @return string
     */
    public function getContent()
    {
        if (! $this->encoding) {
            $this->content = $this->rawContent;
        }
        else if ($this->encoding === 'base64') {
            $this->content = base64_decode($this->rawContent);
        } else {
            throw new \RuntimeException('Unsupported encoding: ' + $this->encoding);
        }
        return $this->content;
    }
}
