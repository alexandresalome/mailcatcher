<?php

namespace Alex\MailCatcher\Tests;

use Behat\Behat\Console\BehatApplication;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;

class BehatExtensionTest extends AbstractTest
{
    public function testCriterias()
    {
        $this->getClient()->purge();

        $this->sendMessage(\Swift_Message::newInstance()
            ->setSubject('hello mailcatcher')
            ->setFrom('world@example.org')
            ->setTo('mailcatcher@example.org')
            ->setBody('This is a message from world to mailcatcher')
        );

        $this->sendMessage(\Swift_Message::newInstance()
            ->setSubject('hello php')
            ->setFrom('world@example.org')
            ->setTo('php@example.org')
            ->setBody('This is a message from world to php')
        );

        $behat = $this->runBehat(array(

            "Then 2 mails should be sent",
            "Then 2 mail should be sent",

            // Criteria "from"
            'When I open mail from "world@example.org"',
            'Then I should see "from world" in mail',

            // Criteria "to"
            'When I open mail to "mailcatcher@example.org"',
            'Then I should see "from world to mailcatcher" in mail',

            // Criteria "containing"
            'When I open mail containing "to mailcatcher"',
            'Then I should see "from world to mailcatcher" in mail',

            // Criteria "with subject"
            'When I open mail with subject "hello mailcatcher"',
            'Then I should see "from world to mailcatcher" in mail',
        ));
    }

    private function runBehat($steps)
    {
        $client = $this->getClient();

        $file    = tempnam(sys_get_temp_dir(), 'mailcatcher_');
        unlink($file);
        $configFile = $file.'.config';
        $outputFile = $file.'.output';
        $file = $file.'.feature';
        $content = "Feature: Test\n\n    Scenario: Test\n    ".implode("\n    ", $steps)."\n";

        $config = json_encode(array(
            'default' => array(
                'context' => array(
                    'class' => 'Alex\MailCatcher\Test\TestContext',
                ),
                'extensions' => array(
                    'Alex\MailCatcher\Behat\MailCatcherExtension\Extension' => array(
                        'url' => $client->getUrl(),
                        'purge_before_scenario' => false
                    ),
                ),
            ),
        ));

        try {
            $behat = new BehatApplication('DEV');
            $behat->setAutoExit(false);

            $input = new ArgvInput(array('behat', '--format', 'progress', '--config', $configFile, '--out', $outputFile, $file));
            $output = new NullOutput();

            file_put_contents($file, $content);
            file_put_contents($configFile, $config);
            $result = $behat->run($input, $output);
            unlink($file);
            unlink($configFile);

        } catch (\Exception $exception) {
            unlink($file);
            unlink($file.'.config');
            $this->fail($exception->getMessage());
        }

        if ($result !== 0) {
            $this->fail('Should finished with status 0, got '.$result);
        }
    }
}
