<?php
namespace TheRat\OmCms\I18nBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use TheRat\OmCms\I18nBundle\Command\LocaleUpdateCommand;
use TheRat\OmCms\I18nBundle\Entity\Locale;

class LocaleSubscriber implements EventSubscriber, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

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
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'postUpdate',
            'postPersist',
        ];
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->updateLocalesConfig($args);
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->updateLocalesConfig($args);
    }

    protected function updateLocalesConfig(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Locale) {
            $command = new LocaleUpdateCommand();
            $command->setContainer($this->container);
            $input = new ArrayInput(['--cache-clear' => null]);
            $output = new NullOutput();
            $command->run($input, $output);
        }
    }
}
