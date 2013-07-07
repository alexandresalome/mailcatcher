<?php

namespace Alex\Mailcatcher;

/**
 * Message in Mailcatcher
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
class Message
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var Person
     */
    protected $sender;

    /**
     * @var array array of Person
     */
    protected $recipients;

    /**
     * @var array array of Attachment
     */
    protected $attachments;

    /**
     * @var DateTime
     */
    protected $createdAt;

    /**
     * @var HeaderBag
     */
    protected $headers;

    /**
     * @var array
     */
    protected $formats;

    /**
     * @var string
     */
    protected $content;

    /**
     * Constructor
     *
     * @param Client $client
     * @param array $data
     */
    public function __construct(Client $client, array $data = array())
    {
        $this->client = $client;
        $this->loadFromArray($data);
    }

    /**
     * @return Message
     */
    public function loadFromArray(array $array)
    {
        if (isset($array['id'])) {
            $this->id = $array['id'];
        }

        if (isset($array['created_at'])) {
            $this->createdAt = new \DateTime($array['created_at']);
        }

        if (isset($array['size'])) {
            $this->size = $array['size'];
        }

        if (isset($array['subject'])) {
            $this->subject = $array['subject'];
        }

        if (isset($array['sender'])) {
            $this->sender = Person::createFromString($array['sender']);
        }

        if (isset($array['recipients'])) {
            $this->recipients = array_map(function ($string) {
                return Person::createFromString($string);
            }, $array['recipients']);
        }

        if (isset($array['formats'])) {
            $this->formats = $array['formats'];
        }

        if (isset($array['type'])) {
            $this->type = $array['type'];
        }

        if (isset($array['attachments'])) {
            $attachments = $array['attachments'];

            $client = $this->client;
            $this->attachments = array_map(function ($array) use ($client) {
                return new Attachment($client, $array);
            }, $array['attachments']);
        }

        if (isset($array['source'])) {
            $source = $array['source'];

            $parser = new MessageParser();

            list($this->headers, $this->content) = $parser->parse($source);
        }

        return $this;
    }

    public function match(array $criterias)
    {
        foreach ($criterias as $criteria) {
            list($type, $value) = $criteria;
            switch ($type) {
                case 'from':
                    if (!$this->getSender()->match($value)) {
                        return false;
                    }

                    break;

                case 'subject':
                    if (false === strpos($this->getSubject(), $value)) {
                        return false;
                    }

                    break;

                case 'to':
                    $foundTo = false;
                    foreach ($this->getRecipients() as $recipient) {
                        if ($recipient->match($value)) {
                            $foundTo = true;
                            break;
                        }
                    }

                    if (!$foundTo) {
                        return false;
                    }

                    break;

                case 'contains':
                    if (false === strpos($this->getContent(), $value)) {
                        return false;
                    }

                    break;

                case 'format':
                    if (!$this->hasFormat($value)) {
                        return false;
                    }

                    break;

                case 'attachments':
                    if (!is_bool($value)) {
                        throw new \InvalidArgumentException(sprintf('Expected a boolean, got a "%s".', gettype($value)));
                    }

                    if ($value != $this->hasAttachments()) {
                        return false;
                    }

                    break;

                default:
                    throw new \InvalidArgumentException(sprintf('Unexpected type of criteria: "%s".', $type));
            }
        }

        return true;
    }

    /**
     * @return boolean
     */
    public function hasFormat($format)
    {
        return in_array($format, $this->getFormats());
    }

    /**
     * @return array
     */
    public function getFormats()
    {
        if (null === $this->formats) {
            $this->hydrate();
        }

        return $this->formats;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return integer
     */
    public function getSize()
    {
        if (null === $this->size) {
            $this->hydrate();
        }

        return $this->size;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        if (null === $this->subject) {
            $this->hydrate();
        }

        return $this->subject;
    }

    /**
     * @return boolean
     */
    public function isPlain()
    {
        return $this->getType() === 'text/plain';
    }

    /**
     * @return string
     */
    public function getType()
    {
        if (null === $this->type) {
            $this->hydrate();
        }

        return $this->type;
    }

    /**
     * @return array array of Attachment
     */
    public function getAttachments()
    {
        if (null === $this->attachments) {
            $this->hydrate();
        }

        return $this->attachments;
    }

    /**
     * @return boolean
     */
    public function hasAttachments()
    {
        return count($this->getAttachments()) > 0;
    }

    /**
     * @return Person
     */
    public function getSender()
    {
        if (null === $this->sender) {
            $this->hydrate();
        }

        return $this->sender;
    }

    /**
     * @return array
     */
    public function getRecipients()
    {
        if (null === $this->recipients) {
            $this->hydrate();
        }

        return $this->recipients;
    }

    /**
     * @return HeaderBag
     */
    public function getHeaders()
    {
        if (null === $this->headers) {
            $this->hydrate();
        }

        return $this->headers;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        if (null === $this->content) {
            $this->hydrate();
        }

        return $this->content;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        if (null === $this->createdAt) {
            $this->hydrate();
        }

        return $this->createdAt;
    }

    private function hydrate()
    {
        $this->loadFromArray($this->client->request('GET', $this->id.'.json'));
    }
}
