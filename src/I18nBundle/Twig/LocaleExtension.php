<?php
namespace TheRat\OmCms\I18nBundle\Twig;

use Doctrine\ORM\EntityManager;
use TheRat\OmCms\I18nBundle\Entity\LocaleRepository;

class LocaleExtension extends \Twig_Extension
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'om-cms_locale_extension';
    }

    public function getFunctions()
    {
        return [
            'locales' => new \Twig_SimpleFunction('locales', [$this, 'getLocales']),
        ];
    }

    public function getLocales()
    {
        /** @var LocaleRepository $localeRepository */
        $localeRepository = $this->getEntityManager()
            ->getRepository('OmCmsI18nBundle:Locale');
        return $localeRepository->findBy(['enabled' => true], ['position' => 'ASC']);
    }
}
