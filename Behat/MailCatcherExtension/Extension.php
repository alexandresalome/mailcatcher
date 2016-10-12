<?php

namespace Alex\MailCatcher\Behat\MailCatcherExtension;

use Symfony\Component\Config\FileLocator,
    Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Behat\Behat\Context\ServiceContainer\ContextExtension;

/**
 * Mink extension for MailCatcher manipulation.
 *
 * @author Alexandre Salomé <alexandre.salome@gmail.com>
 */
class Extension implements ExtensionInterface
{
    const MAILCATCHER_ID = 'mailcatcher';

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/services'));
        $loader->load('core.xml');

        if ($config['mailhog'])
            $this->loadMailhog($container);

        $this->loadContextInitializer($container);

        $container->setParameter('behat.mailcatcher.client.url', $config['url']);
        $container->setParameter('behat.mailcatcher.purge_before_scenario', $config['purge_before_scenario']);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadMailhog(ContainerBuilder $container)
    {
        $container->setDefinition(self::MAILCATCHER_ID, new Definition(
            'Alex\MailCatcher\MailhogClient',
            array(
                '%behat.mailcatcher.client.url%'
            )
        ));
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadContextInitializer(ContainerBuilder $container)
    {
        $definition = new Definition('Alex\MailCatcher\Behat\MailCatcherExtension\ContextInitializer', array(
            new Reference(self::MAILCATCHER_ID),
            '%behat.mailcatcher.purge_before_scenario%'
        ));
        $definition->addTag(ContextExtension::INITIALIZER_TAG, array('priority' => 0));
        $container->setDefinition('mailcatcher.context_initializer', $definition);
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->booleanNode('purge_before_scenario')->defaultTrue()->end()
                ->booleanNode('mailhog')->defaultFalse()->end()
                ->scalarNode('url')->defaultValue('http://localhost:1080')->end()
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getCompilerPasses()
    {
        return array();
    }

    /**
     * @return array
     */
    protected function loadEnvironmentConfiguration()
    {
        $config = array();

        if ($url = getenv('MAILCATCHER_URL')) {
            $config['url'] = $url;
        }

        return $config;
    }
    
    /**
     * {@inheritDoc}
     */
    public function getConfigKey()
    {
        return 'mailcatcher';
    }
    
    /**
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager)
    {
    }
    
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
    }
}
