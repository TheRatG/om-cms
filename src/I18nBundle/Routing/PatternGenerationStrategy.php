<?php
namespace TheRat\OmCms\I18nBundle\Routing;

use JMS\I18nRoutingBundle\Router\DefaultPatternGenerationStrategy;
use JMS\I18nRoutingBundle\Router\PatternGenerationStrategyInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Translation\TranslatorInterface;

class PatternGenerationStrategy implements PatternGenerationStrategyInterface, ContainerAwareInterface
{
    private $strategy;
    private $translator;
    private $translationDomain;
    private $locales;
    private $cacheDir;
    private $defaultLocale;
    /** @var  ContainerInterface */
    private $container;

    public function __construct($strategy, TranslatorInterface $translator, array $locales, $cacheDir, $translationDomain = 'routes', $defaultLocale = 'en')
    {
        $this->strategy = $strategy;
        $this->translator = $translator;
        $this->translationDomain = $translationDomain;
        $this->locales = $locales;
        $this->cacheDir = $cacheDir;
        $this->defaultLocale = $defaultLocale;
    }

    public function generateI18nPatterns($routeName, Route $route)
    {
        $patterns = [];
        /** @var \Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag $parameterBag */
        $parameterBag = $this->container->getParameterBag();
        $locales = $route->getOption('i18n_locales') ?: $this->locales;
        $locales = $parameterBag->resolveValue($locales);
        foreach ($locales as $locale) {
            $i18nPattern = $route->getPath();

            // prefix with locale if requested
            if (DefaultPatternGenerationStrategy::STRATEGY_PREFIX === $this->strategy
                || DefaultPatternGenerationStrategy::STRATEGY_PREFIX === $route->getOption('i18n_strategy')
                || (DefaultPatternGenerationStrategy::STRATEGY_PREFIX_EXCEPT_DEFAULT === $this->strategy && $this->defaultLocale !== $locale)
            ) {
                $i18nPattern = '/{_locale}' . $i18nPattern;
                if (null !== $route->getOption('i18n_prefix')) {
                    $prefix = $route->getOption('i18n_prefix');
                    $prefix = $parameterBag->resolveValue($prefix);
                    $i18nPattern = $prefix . $i18nPattern;
                }
            }

            $patterns[$i18nPattern][] = $locale;
        }
        return $patterns;
    }

    /**
     * {@inheritDoc}
     */
    public function addResources(RouteCollection $i18nCollection)
    {
        foreach ($this->locales as $locale) {
            if (file_exists($metadata = $this->cacheDir . '/translations/catalogue.' . $locale . '.php.meta')) {
                foreach (unserialize(file_get_contents($metadata)) as $resource) {
                    $i18nCollection->addResource($resource);
                }
            }
        }
    }

    /**
     * @param ContainerInterface|null $container
     * @return $this
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        return $this;
    }
}
