<?php

namespace Alex\MailCatcher\Tests;

use Alex\MailCatcher\Person;

class PersonTest extends \PHPUnit_Framework_TestCase
{
    public function provideIncorrect()
    {
        return array(
            array('foo'),
            array('foo@example.org'),
        );
    }

    public function provideCorrect()
    {
        return array(
            array('foo <foo@example.org>', 'foo', 'foo@example.org'),
            array('<foo@example.org>', null, 'foo@example.org'),
        );
    }

    /**
     * @dataProvider provideIncorrect
     * @expectedException \InvalidArgumentException
     */
    public function testIncorrect($string)
    {
        Person::createFromString($string);
    }

    /**
     * @dataProvider provideCorrect
     */
    public function testCorrect($string, $name, $email)
    {
        $person = Person::createFromString($string);

        $this->assertEquals($email, $person->getEmail(), "Email is correct");
        $this->assertEquals($name, $person->getName(), "Name is correct");
    }
}
