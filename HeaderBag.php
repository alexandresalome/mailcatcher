<?php

namespace Alex\Mailcatcher;

/**
 * Header bag
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
class HeaderBag implements \IteratorAggregate
{
    /**
     * @var array
     */
    private $headers = array();

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->headers);
    }

    /**
     * Adds a header to the bag.
     *
     * @param string $name  name of header
     * @param string $value value of header
     *
     * @return HeaderBag
     */
    public function add($name, $value)
    {
        if (!isset($this->headers[$name])) {
            $this->headers[$name] = $value;

            return $this;
        }

        if (!is_array($this->headers[$name])) {
            $this->headers[$name] = array($this->headers[$name]);
        }

        $this->headers[$name][] = $value;

        return $this;
    }

    /**
     * Returns a header value or default value.
     *
     * @param string $name    header name
     * @param mixed  $default value to return if not found
     */
    public function get($name, $default = null)
    {
        return isset($this->headers[$name]) ? $this->headers[$name] : $default;
    }
}
