<?php

namespace Alex\MailCatcher;

/**
 * Client to manipulate a MailCatcher server.
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
class Client
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var array
     */
    protected $messages = array();

    /**
     * Creates a new client.
     *
     * @param string $url url of the server
     */
    public function __construct($url = 'http://localhost:1080')
    {
        $this->url = $url;
    }

    /**
     * Deletes all messages on server.
     *
     * @return Client
     */
    public function purge()
    {
        $this->request('DELETE');
        $this->messages = array();

        return $this;
    }

    /**
     * @return string URL of server used by the client
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Returns the number of messages on the server.
     *
     * @return int
     */
    public function getMessageCount()
    {
        return count($this->request('GET'));
    }

    /**
     * Searches for one messages on the server.
     *
     * See method `Message::match` method for more informations on criterias.
     *
     * @param string
     *
     * @return Message|null
     */
    public function searchOne(array $criterias = array())
    {
        $results = $this->search($criterias, 1);

        if (count($results) !== 1) {
            return null;
        }

        return $results[0];
    }

    /**
     * Searches for messages on the server.
     *
     * See method `Method::match` for more informations on criterias.
     *
     * @param array $criterias an array of criterias
     * @param int   $limit     maximum number of elements to fetch. Null means no limit
     *
     * @return array a list of messages
     */
    public function search(array $criterias = array(), $limit = null)
    {
        $messages = array();

        foreach ($this->request('GET') as $message) {
            if (isset($this->messages[$message['id']])) {
                $messages[] = $this->messages[$message['id']];
            } else {
                $messages[] = $this->messages[$message['id']] = new Message($this, $message);
            }
        }

        $result = array();
        foreach ($messages as $message) {
            if (null !== $limit && count($result) >= $limit) {
                break;
            }
            if ($message->match($criterias)) {
                $result[] = $message;
            }
        }

        return $result;
    }

    /**
     * Request the API of MailCatcher.
     *
     * @param string $method HTTP method to use (POST, PUT, GET, DELETE)
     * @param string $path   relative path from '/messages' (ex: null, '132.json')
     * @param array  $parameters parameters to POST
     *
     * @return string response body
     */
    public function request($method, $path = null, $parameters = array())
    {
        if (null === $path) {
            $url = '/messages';
        } else {
            $url  = '/messages/'.$path;
        }

        return json_decode($this->requestRaw($method, $url, $parameters), true);
    }

    /**
     * Raw method to request the API of MailCatcher.
     *
     * @param string $method     HTTP method
     * @param string $url        absolute URL on server (`/messages/132.json`)
     * @param array  $parameters parameters to POST
     *
     * @return string response body
     */
    public function requestRaw($method, $url, $parameters = array())
    {
        $url = $this->url.$url;

        if (false === $curl = curl_init()) {
            throw new ClientException('Unable to create a new cURL handle');
        }

        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_URL            => $url,
            CURLOPT_TIMEOUT_MS     => 3000,
            CURLOPT_TIMEOUT        => 3,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_FAILONERROR    => true,
            CURLOPT_SSL_VERIFYPEER => false,
        );

        switch ($method) {
            case 'HEAD':
                $options[CURLOPT_NOBODY] = true;
                break;

            case 'GET':
                $options[CURLOPT_HTTPGET] = true;
                break;

            case 'POST':
            case 'PUT':
            case 'DELETE':
            case 'PATCH':
                $options[CURLOPT_POSTFIELDS] = http_build_query($parameters);

                break;
        }

        curl_setopt_array($curl, $options);

        $result = curl_exec($curl);

        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($statusCode === 200 || $statusCode === 204 || ($statusCode >= 300 && $statusCode <= 303)) {
            return $result;
        }

        if (0 === $statusCode) {
            throw new \RuntimeException(sprintf('Unable to connect to "%s".', $this->url));
        }

        throw new \RuntimeException(sprintf('Unexpected status code. Expected valid code, got %s.', $statusCode));
    }
}
