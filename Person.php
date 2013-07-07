<?php

namespace Alex\Mailcatcher;

class Person
{
    protected $name;
    protected $email;

    public function __construct($name, $email)
    {
        $this->name  = null === $name  ? null : (string) $name;
        $this->email = null === $email ? null : (string) $email;
    }

    public function match($text)
    {
        return false !== strpos($this->name, $text) || false !== strpos($this->email, $text);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function equals(Person $person)
    {
        return $person->getName() === $this->name && $person->getEmail() === $this->email;
    }

    public static function createFromString($string)
    {
        if (preg_match('/^(?:(.+) )?<(.+)>$/', $string, $vars)) {
            $name  = $vars[1] === '' ? null : $vars[1];
            $email = $vars[2] === '' ? null : $vars[2];
            return new Person($name, $email);
        }

        throw new \InvalidArgumentException(sprintf('Unable to parse Person "%s".', $string));
    }
}
