<?php

namespace Liip\ImagineBundle\Tests\DependencyInjection;

use Liip\ImagineBundle\DependencyInjection\Factory\Loader\FileSystemLoaderFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\WebPathResolverFactory;
use Liip\ImagineBundle\Tests\AbstractTest;
use Liip\ImagineBundle\DependencyInjection\LiipImagineExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @covers Liip\ImagineBundle\DependencyInjection\Configuration
 * @covers Liip\ImagineBundle\DependencyInjection\LiipImagineExtension
 */
class LiipImagineExtensionTest extends AbstractTest
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected $containerBuilder;

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testUserLoadThrowsExceptionUnlessDriverIsValid()
    {
        $loader = new LiipImagineExtension();
        $config = array('driver' => 'foo');
        $loader->load(array($config), new ContainerBuilder());
    }

    public function testLoadWithDefaults()
    {
        $this->createEmptyConfiguration();

        $this->assertParameter('default', 'liip_imagine.cache.resolver.default');
        $this->assertAlias('liip_imagine.gd', 'liip_imagine');
        $this->assertHasDefinition('liip_imagine.controller');
        $this->assertDICConstructorArguments(
            $this->containerBuilder->getDefinition('liip_imagine.controller'),
            array(
                new Reference('liip_imagine.data.manager'),
                new Reference('liip_imagine.filter.manager'),
                new Reference('liip_imagine.cache.manager'),
                new Reference('liip_imagine.cache.signer'),
                new Reference('logger', ContainerInterface::IGNORE_ON_INVALID_REFERENCE),
            )
        );
    }

    public function testCustomRouteRequirements()
    {
        $this->createFullConfiguration();
        $param = $this->containerBuilder->getParameter('liip_imagine.filter_sets');

        $this->assertTrue(isset($param['small']['filters']['route']['requirements']));

        $variable1 = $param['small']['filters']['route']['requirements']['variable1'];
        $this->assertEquals('value1', $variable1, sprintf('%s parameter is correct', $variable1));
    }

    /**
     * @dataProvider factoriesProvider
     */
    public function testFactoriesConfiguration($service, $factory)
    {
        if (version_compare(Kernel::VERSION_ID, '20600') < 0) {
            $this->markTestSkipped('No need to test on symfony < 2.6');
        }

        $this->createEmptyConfiguration();
        $definition = $this->containerBuilder->getDefinition($service);

        $this->assertEquals($factory, $definition->getFactory());
    }

    /**
     * @dataProvider factoriesProvider
     */
    public function testLegacyFactoriesConfiguration($service, $factory)
    {
        if (version_compare(Kernel::VERSION_ID, '20600') >= 0) {
            $this->markTestSkipped('No need to test on symfony >= 2.6');
        }

        $this->createEmptyConfiguration();
        $definition = $this->containerBuilder->getDefinition($service);

        $this->assertEquals($factory[0], $definition->getFactoryClass());
        $this->assertEquals($factory[1], $definition->getFactoryMethod());
    }

    public function factoriesProvider()
    {
        return array(
            array(
              'liip_imagine.mime_type_guesser',
              array('Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser', 'getInstance'),
            ),
            array(
              'liip_imagine.extension_guesser',
              array('Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser', 'getInstance'),
            ),
        );
    }

    /**
     * @return ContainerBuilder
     */
    protected function createEmptyConfiguration()
    {
        $this->containerBuilder = new ContainerBuilder();
        $loader = new LiipImagineExtension();
        $loader->addLoaderFactory(new FileSystemLoaderFactory());
        $loader->addResolverFactory(new WebPathResolverFactory());
        $loader->load(array(array()), $this->containerBuilder);
        $this->assertTrue($this->containerBuilder instanceof ContainerBuilder);
    }

    /**
     * @return ContainerBuilder
     */
    protected function createFullConfiguration()
    {
        $this->containerBuilder = new ContainerBuilder();
        $loader = new LiipImagineExtension();
        $loader->addLoaderFactory(new FileSystemLoaderFactory());
        $loader->addResolverFactory(new WebPathResolverFactory());
        $loader->load(array($this->getFullConfig()), $this->containerBuilder);
        $this->assertTrue($this->containerBuilder instanceof ContainerBuilder);
    }

    protected function getFullConfig()
    {
        $yaml = <<<'EOF'
driver: imagick
cache: false
filter_sets:
    small:
        filters:
            thumbnail: { size: [100, ~], mode: inset }
            route:
                requirements: { variable1: 'value1' }
        quality: 80
    medium_small_cropped:
        filters:
            thumbnail: { size: [223, 173], mode: outbound }
    medium_cropped:
        filters:
            thumbnail: { size: [232, 180], mode: outbound }
    medium:
        filters:
            thumbnail: { size: [232, 180], mode: inset }
    large_cropped:
        filters:
            thumbnail: { size: [483, 350], mode: outbound }
    large:
        filters:
            thumbnail: { size: [483, ~], mode: inset }
    xxl:
        filters:
            thumbnail: { size: [660, ~], mode: inset }
        quality: 100
    '':
        quality: 100
data_loader: my_loader
EOF;
        $parser = new Parser();

        return $parser->parse($yaml);
    }

    private function assertAlias($value, $key)
    {
        $this->assertEquals($value, (string) $this->containerBuilder->getAlias($key), sprintf('%s alias is correct', $key));
    }

    private function assertParameter($value, $key)
    {
        $this->assertEquals($value, $this->containerBuilder->getParameter($key), sprintf('%s parameter is correct', $key));
    }

    private function assertHasDefinition($id)
    {
        $this->assertTrue(($this->containerBuilder->hasDefinition($id) ?: $this->containerBuilder->hasAlias($id)));
    }

    private function assertNotHasDefinition($id)
    {
        $this->assertFalse(($this->containerBuilder->hasDefinition($id) ?: $this->containerBuilder->hasAlias($id)));
    }

    private function assertDICConstructorArguments($definition, $args)
    {
        $this->assertEquals($args, $definition->getArguments(), "Expected and actual DIC Service constructor arguments of definition '".$definition->getClass()."' don't match.");
    }

    protected function tearDown()
    {
        unset($this->containerBuilder);
    }
}
