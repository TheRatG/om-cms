<?php
namespace TheRat\OmCms\I18nBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use TheRat\OmCms\I18nBundle\Entity\Locale;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadLocaleData implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

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

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $en = new Locale();
        $en->setName('en');
        $en->setAlias('en');
        $en->setEnabled(true);

        $manager->persist($en);

        $ru = new Locale();
        $ru->setName('ru');
        $ru->setAlias('ru');
        $ru->setEnabled(true);

        $manager->persist($en);

        $manager->flush();
    }
}
