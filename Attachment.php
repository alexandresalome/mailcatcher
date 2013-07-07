<?php

namespace Alex\Mailcatcher;

/**
 * Attachment of a message.
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
class Attachment
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
     * Attachment CID
     *
     * @var string
     */
    protected $cid;

    /**
     * Href to download attachment.
     *
     * @var string
     */
    protected $href;

    /**
     * Content of attachment.
     *
     * @var string
     */
    protected $content;

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
        if (isset($array['filename'])) {
            $this->filename = $array['filename'];
        }

        if (isset($array['size'])) {
            $this->size = $array['size'];
        }

        if (isset($array['type'])) {
            $this->type = $array['type'];
        }

        if (isset($array['cid'])) {
            $this->cid = $array['cid'];
        }

        if (isset($array['href'])) {
            $this->href = $array['href'];
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
        return $this->size;
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
     * Returns HREF.
     *
     * @return string
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * Returns content, raw format.
     *
     * @return string
     */
    public function getContent()
    {
        if (null === $this->content) {
            $this->content = $this->client->requestRaw('GET', $this->href);
        }

        return $this->content;
    }
}
