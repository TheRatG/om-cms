<?php
namespace OmCms\I18nBundle\Routing;

use JMS\I18nRoutingBundle\Exception\NotAcceptableLanguageException;
use JMS\I18nRoutingBundle\Router\I18nLoader;
use JMS\I18nRoutingBundle\Router\LocaleResolverInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router as FrameworkBaseRouter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;

class Router extends FrameworkBaseRouter
{
    /**
     * @var array
     */
    private $hostMap = [];
    /**
     * @var string
     */
    private $i18nLoaderId;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var
     */
    private $defaultLocale = 'en';
    /**
     * @var array
     */
    private $locales = ['en'];
    /**
     * @var bool
     */
    private $redirectToHost = true;
    /**
     * @var LocaleResolverInterface
     */
    private $localeResolver;
    /**
     * @var array
     */
    private $adminLocales = ['en', 'ru'];

    /**
     * Constructor.
     *
     * The only purpose of this is to make the container available in the sub-class
     * since it is declared private in the parent class.
     *
     * The parameters are not listed explicitly here because they are different for
     * Symfony 2.0 and 2.1. If we did list them, it would make this class incompatible
     * with one of both versions.
     */
    public function __construct()
    {
        call_user_func_array(['Symfony\Bundle\FrameworkBundle\Routing\Router', '__construct'], func_get_args());
        $this->container = func_get_arg(0);
    }

    public function setLocaleResolver(LocaleResolverInterface $resolver)
    {
        $this->localeResolver = $resolver;
    }

    /**
     * Whether the user should be redirected to a different host if the
     * matching route is not belonging to the current domain.
     *
     * @param Boolean $bool
     */
    public function setRedirectToHost($bool)
    {
        $this->redirectToHost = (Boolean)$bool;
    }

    /**
     * Sets the host map to use.
     *
     * @param array $hostMap a map of locales to hosts
     */
    public function setHostMap(array $hostMap)
    {
        $this->hostMap = $hostMap;
    }

    public function setI18nLoaderId($id)
    {
        $this->i18nLoaderId = $id;
    }

    public function setDefaultLocale($locale)
    {
        $this->defaultLocale = $locale;
    }

    /**
     * @param array $locales
     * @return self
     */
    public function setLocales($locales)
    {
        $this->locales = $locales;
        return $this;
    }

    /**
     * @param array $adminLocales
     * @return $this
     */
    public function setAdminLocales($adminLocales)
    {
        $this->adminLocales = $adminLocales;

        return $this;
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @param  string $name The name of the route
     * @param  array $parameters An array of parameters
     * @param  Boolean $absolute Whether to generate an absolute URL
     *
     * @return string The generated URL
     */
    public function generate($name, $parameters = [], $absolute = false)
    {
        // determine the most suitable locale to use for route generation
        $currentLocale = $this->context->getParameter('_locale');
        if (isset($parameters['_locale'])) {
            $locale = $parameters['_locale'];
        } elseif ($currentLocale) {
            $locale = $currentLocale;
        } else {
            $locale = $this->defaultLocale;
        }

        // if the locale is changed, and we have a host map, then we need to
        // generate an absolute URL
        if ($currentLocale && $currentLocale !== $locale && $this->hostMap) {
            $absolute = true;
        }

        $generator = $this->getGenerator();

        $currentHost = null;
        // if an absolute URL is requested, we set the correct host
        if ($absolute && $this->hostMap) {
            $currentHost = $this->context->getHost();
            $this->context->setHost($this->hostMap[$locale]);
        }

        if (empty($parameters['_locale'])) {
            $parameters['_locale'] = $locale;
        }

        try {
            try {
                $url = $generator->generate($this->getRouteNamePrefix($locale) . $name, $parameters, $absolute);
            } catch (RouteNotFoundException $ex) {
                try {
                    $url = $generator->generate(
                        $this->getRouteNamePrefix($locale, false) . $name,
                        $parameters,
                        $absolute
                    );
                } catch (RouteNotFoundException $ex) {
                    $url = $generator->generate(
                        $this->getAdminRouteNamePrefix() . $name,
                        $parameters,
                        $absolute
                    );
                }
            }

            if ($absolute && $this->hostMap) {
                $this->context->setHost($currentHost);
            }

            return $url;
        } catch (RouteNotFoundException $ex) {
            if ($absolute && $this->hostMap) {
                $this->context->setHost($currentHost);
            }

            // fallback to default behavior
        }

        // use the default behavior if no localized route exists
        return $generator->generate($name, $parameters, $absolute);
    }


    /**
     * Tries to match a URL with a set of routes.
     *
     * Returns false if no route matches the URL.
     *
     * @param  string $url URL to be parsed
     *
     * @return array|false An array of parameters or false if no route matches
     */
    public function match($url)
    {
        return $this->matchI18n(parent::match($url), $url);
    }

    public function getRouteCollection()
    {
        $collection = parent::getRouteCollection();

        return $this->container->get($this->i18nLoaderId)->load($collection);
    }

    public function getOriginalRouteCollection()
    {
        return parent::getRouteCollection();
    }

    /**
     * To make compatible with Symfony <2.4
     */
    public function matchRequest(Request $request)
    {
        $matcher = $this->getMatcher();
        $pathInfo = $request->getPathInfo();
        if (!$matcher instanceof RequestMatcherInterface) {
            // fallback to the default UrlMatcherInterface
            return $this->matchI18n($matcher->match($pathInfo), $pathInfo);
        }

        return $this->matchI18n($matcher->matchRequest($request), $pathInfo);
    }

    protected function getRouteNamePrefix($locale, $withoutDefault = true)
    {
        if ($locale == $this->defaultLocale && $withoutDefault) {
            $ret = $locale;
        } else {
            if ($withoutDefault) {
                $locales = array_filter(
                    $this->locales,
                    function ($v) {
                        return $v != $this->defaultLocale;
                    }
                );
                $ret = implode('_', $locales);
            } else {
                $ret = implode('_', $this->locales);
            }
        }
        return $ret . I18nLoader::ROUTING_PREFIX;
    }

    protected function getAdminRouteNamePrefix()
    {
        $ret = implode('_', $this->adminLocales);
        return $ret . I18nLoader::ROUTING_PREFIX;
    }

    private function matchI18n(array $params, $url)
    {
        if (false === $params) {
            return false;
        }

        if (isset($params['_locales'])) {
            if (false !== $pos = strpos($params['_route'], I18nLoader::ROUTING_PREFIX)) {
                $params['_route'] = substr($params['_route'], $pos + strlen(I18nLoader::ROUTING_PREFIX));
            }

            if (!($currentLocale = $this->context->getParameter('_locale'))
                && $this->container->isScopeActive('request')
            ) {
                $currentLocale = $this->localeResolver->resolveLocale(
                    $this->container->get('request_stack')->getCurrentRequest(),
                    $params['_locales']
                );

                // If the locale resolver was not able to determine a locale, then all efforts to
                // make an informed decision have failed. Just display something as a last resort.
                if (!$currentLocale) {
                    $currentLocale = reset($params['_locales']);
                }
            }

            if (!in_array($currentLocale, $params['_locales'], true)) {
                // TODO: We might want to allow the user to be redirected to the route for the given locale if
                //       it exists regardless of whether it would be on another domain, or the same domain.
                //       Below we assume that we do not want to redirect always.

                // if the available locales are on a different host, throw a ResourceNotFoundException
                if ($this->hostMap) {
                    // generate host maps
                    $hostMap = $this->hostMap;
                    $availableHosts = array_map(
                        function ($locale) use ($hostMap) {
                            return $hostMap[$locale];
                        },
                        $params['_locales']
                    );

                    $differentHost = true;
                    foreach ($availableHosts as $host) {
                        if ($this->hostMap[$currentLocale] === $host) {
                            $differentHost = false;
                            break;
                        }
                    }

                    if ($differentHost) {
                        throw new ResourceNotFoundException(
                            sprintf(
                                'The route "%s" is not available on the current host "%s", but only on these hosts "%s".',
                                $params['_route'],
                                $this->hostMap[$currentLocale],
                                implode(', ', $availableHosts)
                            )
                        );
                    }
                }

                // no host map, or same host means that the given locale is not supported for this route
                throw new NotAcceptableLanguageException($currentLocale, $params['_locales']);
            }

            unset($params['_locales']);
            $params['_locale'] = $currentLocale;
        } else {
            if (isset($params['_locale']) && false !== $pos = strpos($params['_route'], I18nLoader::ROUTING_PREFIX)) {
                $params['_route'] = substr($params['_route'], $pos + strlen(I18nLoader::ROUTING_PREFIX));
            }
        }

        // check if the matched route belongs to a different locale on another host
        if (isset($params['_locale'])
            && isset($this->hostMap[$params['_locale']])
            && $this->context->getHost() !== $host = $this->hostMap[$params['_locale']]
        ) {
            if (!$this->redirectToHost) {
                throw new ResourceNotFoundException(
                    sprintf(
                        'Resource corresponding to pattern "%s" not found for locale "%s".',
                        $url,
                        $this->getContext()->getParameter('_locale')
                    )
                );
            }

            return [
                '_controller' => 'JMS\I18nRoutingBundle\Controller\RedirectController::redirectAction',
                'path' => $url,
                'host' => $host,
                'permanent' => true,
                'scheme' => $this->context->getScheme(),
                'httpPort' => $this->context->getHttpPort(),
                'httpsPort' => $this->context->getHttpsPort(),
                '_route' => $params['_route'],
            ];
        }

        // if we have no locale set on the route, we try to set one according to the localeResolver
        // if we don't do this all _internal routes will have the default locale on first request
        if (!isset($params['_locale'])
            && $this->container->isScopeActive('request')
            && $locale = $this->localeResolver->resolveLocale(
                $this->container->get('request_stack')->getCurrentRequest(),
                $this->container->getParameter('jms_i18n_routing.locales')
            )
        ) {
            $params['_locale'] = $locale;
        }

        return $params;
    }
}
