<?php
namespace OmCms\AdminBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use OmCms\AdminBundle\Entity\User\Group;
use OmCms\AdminBundle\Entity\User\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadUserData implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $group = new Group();
        $group->setName('admin');
        $group->addRole('ROLE_SUPER_ADMIN');
        $manager->persist($group);

        $user = new User();
        $user->setUsername('admin');
        $user->setEmail('admin@email.com');
        $user->setEnabled(true);
        $user->addGroup($group);

        $encoder = $this->container
            ->get('security.encoder_factory')
            ->getEncoder($user);
        $user->setPassword($encoder->encodePassword('admin', $user->getSalt()));

        $manager->persist($user);

        $manager->flush();
    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
