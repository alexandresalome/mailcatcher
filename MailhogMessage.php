<?php

namespace Alex\MailCatcher;

use Alex\MailCatcher\Mime\HeaderBag;
use Alex\MailCatcher\Mime\Message as BaseMessage;

/**
 * Message in MailCatcher
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 * @author Christoph Gross <gross@gross-it.com>
 */
class MailhogMessage extends BaseMessage
{
    /**
     * @var ClientMailhog
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
     * @var array
     */
    protected $formats;

    /**
     * Constructor
     *
     * @param Client $client
     * @param array $data
     */
    public function __construct(MailhogClient $client, array $data = array())
    {
        $this->client = $client;
        $this->loadFromArray($data);
    }

    /**
     * @return Message
     */
    public function loadFromArray(array $array)
    {
        if (isset($array['ID'])) {
            $this->id = $array['ID'];
        }

        if (isset($array['Created'])) {
            // Looks like: 2016-10-09T21:38:24.065518968+02:00
            // Make it ISO8601 compliant and drop the faction part of seconds: .065518968
            $created = preg_replace('/\.\d+/', '', $array['Created']);
            $this->createdAt = new \DateTime($created);
        }

        if (isset($array['Content'])) {
            $content = $array['Content'];

            if (isset($content['Size'])) {
                $this->size = $content['Size'];
            }

            if (isset($content['Headers'])) {
                $headers = $content['Headers'];

                if (isset($headers['Subject'])) {
                    $this->subject = $headers['Subject'][0];
                }

                if(isset($headers['From']) && isset($headers['From'][0])) {
                    $this->sender = $this->createPersonFromEmailString($headers['From'][0]);
                }

                if(isset($headers['To']) && count($headers['To']) > 0) {
                    $this->recipients = array_map(function ($string) {
                        return $this->createPersonFromEmailString($string);
                    }, $headers['To']);
                }

                if(isset($headers['Content-Type']) && count($headers['Content-Type']) > 0
                    && preg_match('/(.*);/', $headers['Content-Type'][0], $match) === 1) {
                    $this->type = $match[1];
                }
            }
        }

        if (isset($array['Raw'])) {
            $raw = $array['Raw'];

            if (isset($raw['Data'])) {
                $this->loadSource($raw['Data']);
            }
        }

        if (isset($array['MIME']) && isset($array['MIME']['Parts'])) {
            $client = $this->client;
            $filteredAttachments = array_filter($array['MIME']['Parts'], function($part) {
                if (MailhogMessage::isAttachment($part))
                    return true;
                return false;
            });
            $this->attachments = array_map(function ($array, $key) use ($client) {
                $array['id'] = $key;
                return new MailhogAttachment($client, $array);
            }, $filteredAttachments, array_keys($filteredAttachments));
        }

        return $this;
    }

    private static function isAttachment($part) {
        // Does it have a Content Type?
        if (isset($part['Headers']) && isset($part['Headers']['Content-Type'])) {
            // Is it an attachment
            if(isset($part['Headers']['Content-Disposition']) && count($part['Headers']['Content-Disposition']) > 0
                && preg_match('/^attachment;/',$part['Headers']['Content-Disposition'][0]) === 1)
                return true;
        }
        return false;
    }

    /**
     * @param array $criterias
     *
     * @return bool
     */
    public function match(array $criterias)
    {
        foreach ($criterias as $type => $value) {
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
     * @param $format
     *
     * @return bool
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
        throw new \RuntimeException('Not implemented for Mailhog');
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
     * @return \DateTime
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
        $this->loadFromArray($this->client->request('GET', $this->id));
    }

    /**
     * @return string
     */
    public function delete()
    {
        return $this->client->request('DELETE', $this->id);
    }

    /**
     * @return Person
     */
    private function createPersonFromEmailString($string) {
        // Name <email@mail.tld>
        if (preg_match('/^(?:(.+) )?<(.+)>$/', $string, $vars)) {
            $name  = $vars[1] === '' ? null : $vars[1];
            $email = $vars[2] === '' ? null : $vars[2];
            return new Person($name, $email);
        }
        // email@mail.tld
        return new Person('', $string);
    }
}
