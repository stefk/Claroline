<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Claroline\PluginBundle\Tests\PluginBundleTestCase;

/**
 * Note : Plugin's FQCNs mentionned in this test case refer to stubs living
 *        in the 'stub/plugin' directory.
 */
class ManagerTest extends PluginBundleTestCase
{
    private $em;

    public function setUp()
    {
        parent::setUp();       
        $this->em = $this->client->getContainer()
                                 ->get('doctrine.orm.entity_manager');
        $this->client->beginTransaction();
    }

    public function  tearDown()
    {
        $this->client->rollback();
    }

    public function testInstallAValidPluginRegistersItInConfig()
    {
        $pluginFQCN = 'Valid\Simple\ValidSimple';
        $this->manager->install($pluginFQCN);

        $this->assertEquals(array('Valid'), $this->fileHandler->getRegisteredNamespaces());
        $this->assertEquals(array($pluginFQCN), $this->fileHandler->getRegisteredBundles());
        $expectedRouting = array(
            'ValidSimple_0' => array(
                'resource' => '@ValidSimple/Resources/config/routing.yml'
                )
            );
        $this->assertEquals($expectedRouting, $this->fileHandler->getRoutingResources());
        $this->assertEquals(true, $this->manager->isInstalled($pluginFQCN));
    }

    public function testInstallAnInvalidPluginThrowsAValidationException()
    {
        $this->setExpectedException('Claroline\PluginBundle\Service\PluginManager\Exception\ValidationException');
        $pluginFQCN = 'Invalid\UnloadableRoutingResource_1';
        $this->manager->install($pluginFQCN);
        $this->assertEquals(false, $this->manager->isInstalled($pluginFQCN));
    }

    public function testInstallAnAlreadyInstalledPluginThrowsAConfigurationException()
    {
        $this->setExpectedException('Claroline\PluginBundle\Service\PluginManager\Exception\ConfigurationException');

        $pluginFQCN = 'Valid\Simple\ValidSimple';
        $this->manager->install($pluginFQCN);
        $this->manager->install($pluginFQCN);

        $this->assertEquals(array('Valid'), $this->fileHandler->getRegisteredNamespaces());
        $this->assertEquals(array($pluginFQCN), $this->fileHandler->getRegisteredBundles());
        $expectedRouting = array(
            'ValidSimple_0' => array(
                'resource' => '@ValidSimple/Resources/config/routing.yml'
                )
            );
        $this->assertEquals($expectedRouting, $this->fileHandler->getRoutingResources());
        $this->assertEquals(false, $this->manager->isInstalled($pluginFQCN));
    }

    public function testInstallSeveralValidPlugins()
    {
        $pluginFQCN_1 = 'Valid\Minimal\ValidMinimal';
        $pluginFQCN_2 = 'Valid\Simple\ValidSimple';
        $pluginFQCN_3 = 'Valid\TwoLaunchersApplication\ValidTwoLaunchersApplication';
        
        $this->manager->install($pluginFQCN_1);
        $this->manager->install($pluginFQCN_2);
        $this->manager->install($pluginFQCN_3);

        $this->assertEquals(array('Valid'), $this->fileHandler->getRegisteredNamespaces());
        $this->assertEquals(
                array($pluginFQCN_1, $pluginFQCN_2, $pluginFQCN_3),
                $this->fileHandler->getRegisteredBundles());
        $expectedRouting = array(
            'ValidSimple_0' => array(
                'resource' => '@ValidSimple/Resources/config/routing.yml'
                ),
            'ValidTwoLaunchersApplication_0' => array(
                'resource' => '@ValidTwoLaunchersApplication/Resources/config/routing.yml'
                )
            );
        $this->assertEquals($expectedRouting, $this->fileHandler->getRoutingResources());
        $this->assertEquals(true, $this->manager->isInstalled($pluginFQCN_1));
        $this->assertEquals(true, $this->manager->isInstalled($pluginFQCN_2));
        $this->assertEquals(true, $this->manager->isInstalled($pluginFQCN_3));
    }

    public function testRemovePluginRemovesItFromConfig()
    {
        $pluginFQCN = 'Valid\Simple\ValidSimple';
        $this->manager->install($pluginFQCN);
        $this->manager->remove($pluginFQCN);

        $this->assertEquals(array(), $this->fileHandler->getRegisteredNamespaces());
        $this->assertEquals(array(), $this->fileHandler->getRegisteredBundles());
        $this->assertEquals(array(), $this->fileHandler->getRoutingResources());
        $this->assertEquals(false, $this->manager->isInstalled($pluginFQCN));
    }

    public function testRemovePluginPreservesSharedVendorNamespaces()
    {
        $this->manager->install('Valid\Minimal\ValidMinimal');
        $this->manager->install('Valid\Simple\ValidSimple');
        $this->manager->remove('Valid\Minimal\ValidMinimal');

        $this->assertEquals(array('Valid'), $this->fileHandler->getRegisteredNamespaces());
    }

    public function testRemoveInexistentPluginThrowsAConfigurationException()
    {
        $this->setExpectedException('Claroline\PluginBundle\Service\PluginManager\Exception\ConfigurationException');
        $this->manager->remove('NonExistentVendor\NonExistentPlugin\NonExistentVendorNonExistentPlugin');
    }
  
    public function testIsInstalledReturnsCorrectValue()
    {
        $pluginFQCN = 'Valid\Minimal\ValidMinimal';
        $this->assertEquals(false, $this->manager->isInstalled($pluginFQCN));

        $this->manager->install($pluginFQCN);
        $this->assertEquals(true, $this->manager->isInstalled($pluginFQCN));
    }
}