<?php
namespace OmCms\I18nBundle\Routing;

use JMS\I18nRoutingBundle\Router\I18nLoader;
use JMS\I18nRoutingBundle\Router\PatternGenerationStrategyInterface;
use JMS\I18nRoutingBundle\Router\RouteExclusionStrategyInterface;
use Symfony\Component\Routing\RouteCollection;

class Loader
{
    const ROUTING_PREFIX = '__RG__';

    private $routeExclusionStrategy;
    private $patternGenerationStrategy;

    public function __construct(RouteExclusionStrategyInterface $routeExclusionStrategy, PatternGenerationStrategyInterface $patternGenerationStrategy)
    {
        $this->routeExclusionStrategy = $routeExclusionStrategy;
        $this->patternGenerationStrategy = $patternGenerationStrategy;
    }

    public function load(RouteCollection $collection)
    {
        $i18nCollection = new RouteCollection();
        foreach ($collection->getResources() as $resource) {
            $i18nCollection->addResource($resource);
        }
        $this->patternGenerationStrategy->addResources($i18nCollection);

        foreach ($collection->all() as $name => $route) {
            if ($this->routeExclusionStrategy->shouldExcludeRoute($name, $route)) {
                $i18nCollection->add($name, $route);
                continue;
            }

            foreach ($this->patternGenerationStrategy->generateI18nPatterns($name, $route) as $pattern => $locales) {
                // If this pattern is used for more than one locale, we need to keep the original route.
                // We still add individual routes for each locale afterwards for faster generation.
                if (count($locales) > 1) {
                    $catchMultipleRoute = clone $route;
                    $catchMultipleRoute->setPath($pattern);
                    $catchMultipleRoute->setRequirement('_locale', implode('|', $locales));
                    $i18nCollection->add(implode('_', $locales) . I18nLoader::ROUTING_PREFIX . $name, $catchMultipleRoute);
                } elseif (count($locales) == 1) {
                    $catchRoute = clone $route;
                    $catchRoute->setPath($pattern);
                    $catchRoute->setDefault('_locale', $locales[0]);
                    $catchRoute->setRequirement('_locale', $locales[0]);
                    $i18nCollection->add(implode('_', $locales) . I18nLoader::ROUTING_PREFIX . $name, $catchRoute);
                }
            }
        }

        return $i18nCollection;
    }
}
