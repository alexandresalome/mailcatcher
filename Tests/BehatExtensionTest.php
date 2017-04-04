<?php

namespace Alex\MailCatcher\Tests;

use Behat\Behat\ApplicationFactory;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\NullOutput;

class BehatExtensionTest extends AbstractTest
{
    public function testNoServer()
    {
        $this->runBehat(array(
            "When I do something",
        ), true, true);
    }

    public function testTrait()
    {
        if (version_compare(PHP_VERSION, "5.4") < 0) {
            $this->markTestSkipped("PHP version not supported");
        }
        $this->getClient()->purge();

        $this->sendMessage(\Swift_Message::newInstance()
            ->setSubject('Welcome!')
            ->setFrom('world@example.org')
            ->setTo('mailcatcher@example.org')
            ->setBody('This is a message from world to mailcatcher')
        );

        $this->runBehat(array(
            "Then a welcome mail should be sent",
        ));
    }

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

        $this->runBehat(array(

            "Then 2 mails should be sent",
            "Then 2 mail should be sent",

            // Criteria "from"
            'When I open mail from "world@example.org"',
            'Then I should see "from world" in mail',
            'Then I should see "from world" in mail source',

            // Criteria "to"
            'When I open mail to "mailcatcher@example.org"',
            'Then I should see "from world to mailcatcher" in mail',
            'Then I should see "from world to mailcatcher" in mail source',

            // Criteria "containing"
            'When I open mail containing "to mailcatcher"',
            'Then I should see "from world to mailcatcher" in mail',
            'Then I should see "from world to mailcatcher" in mail source',

            // Criteria "with subject"
            'When I open mail with subject "hello mailcatcher"',
            'Then I should see "from world to mailcatcher" in mail',
            'Then I should see "from world to mailcatcher" in mail source',
        ));

        $this->runBehat(array(
            "Then 0 mails should be sent"
        ), true);

    }

    private function runBehat($steps, $purge_before_scenario = false, $failServer = false)
    {
        $client = $this->getClient();

        $file    = tempnam(sys_get_temp_dir(), 'mailcatcher_');
        unlink($file);
        $configFile = $file.'.yml';
        $outputFile = $file.'.output';
        $file = $file.'.feature';
        $content = "Feature: Test\n\n  Scenario: Test\n    ".implode("\n    ", $steps)."\n";

        $contexts = array(
            'Alex\MailCatcher\Behat\MailCatcherContext',
            'Alex\MailCatcher\Tests\BehatCustomContext',
        );

        if (version_compare(PHP_VERSION, "5.4") > 0) {
            $contexts[] = 'Alex\MailCatcher\Tests\BehatTraitContext';
        }

        $config = json_encode(array(
            'default' => array(
                'suites' => array(
                    'default' => array(
                        'paths' => array(sys_get_temp_dir()),
                        'contexts' => $contexts,
                    ),
                ),
                'extensions' => array(
                        'Alex\MailCatcher\Behat\MailCatcherExtension\Extension' => array(
                            'url' => $failServer ? 'http://localhost:1337' : $client->getUrl(),
                            'purge_before_scenario' => $purge_before_scenario
                    ),
                )
            )
        ));

        try {
            $application = new ApplicationFactory();
            $behat = $application->createApplication();
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
            $result = null;
        }

        if ($result !== 0) {
            $this->fail('Should finished with status 0, got '.$result);
        }
    }
}
