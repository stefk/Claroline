<?php

namespace Claroline\CoreBundle\Tests\DataFixtures;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Claroline\CoreBundle\Security\RoleManager;
use Claroline\CoreBundle\Entity\User;

class LoadUserData extends AbstractFixture implements ContainerAwareInterface
{
    /** @var ContainerInterface $container */
    private $container;
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    public function load($manager)
    {   
        $user = new User();
        $user->setFirstName('Jane');
        $user->setLastName('Doe');
        $user->setUserName('user');
        $user->setPlainPassword('123');

        $wsCreator = new User();
        $wsCreator->setFirstName('Henry');
        $wsCreator->setLastName('Doe');
        $wsCreator->setUserName('ws_creator');
        $wsCreator->setPlainPassword('123');
        $wsCreator->addRole($this->getReference('role/ws_creator'));
        
        $admin = new User();
        $admin->setFirstName('John');
        $admin->setLastName('Doe');
        $admin->setUserName('admin');
        $admin->setPlainPassword('123');
        $admin->addRole($this->getReference('role/admin'));
        
        $userManager = $this->container->get('claroline.user.manager');
        $userManager->create($user);
        $userManager->create($wsCreator);
        $userManager->create($admin);

        $this->addReference('user/user', $user);
        $this->addReference('user/ws_creator', $wsCreator);
        $this->addReference('user/admin', $admin);
    }
}