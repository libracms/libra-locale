<?php

namespace LibraLocale\View\Helper;

use Zend\Mvc\Router\RouteMatch;
use Zend\View\Helper\AbstractHelper;
use LibraLocale\Module as LocaleModule;

/**
 * Working with locale
 *
 * @author duke
 */
class Locale extends AbstractHelper
{
    /**
     * RouteInterface match returned by the router.
     *
     * @var RouteMatch
     */
    protected $routeMatch;

    /**
     * Current locale alias
     * @var string
     */
    protected $currentLocale;

    /**
     * if locale === null gets object
     *
     * Otherwise compare $locale against current locale case insensitive
     *
     * @param null|string $locale
     * @param bool $insensitive
     * @return string|bool locale helper|result of comparison
     */
    public function __invoke($locale = null, $insensitive = true)
    {
        if ($locale === null) {
            return $this;
        }

        return $this->compare($locale, $insensitive);
    }

    /**
     * Gets current locale alias
     *
     * @return string
     */
    public function current()
    {
        if (null === $this->currentLocale) {
            $this->currentLocale = $this->routeMatch->getParam('locale');
        }

        return $this->currentLocale;
    }

    /**
     * Compare locale against current locale case insensitive
     *
     * @param string $locale
     * @param bool $insensitive
     * @return bool
     */
    public function compare($locale, $insensitive = true)
    {
        if ($insensitive) {
            $result = strtolower($this->routeMatch->getParam('locale')) === strtolower($locale);
        } else {
            $result = $this->routeMatch->getParam('locale') === $locale;
        }
        return $result;
    }

    /**
     * Get full language tag
     *
     * @return string
     */
    public function tag()
    {
        return LocaleModule::getLocaleByAlias($this->current());
    }

    /**
     * Set route match returned by the router.
     *
     * @param  RouteMatch $routeMatch
     * @return Locale
     */
    public function setRouteMatch(RouteMatch $routeMatch)
    {
        $this->routeMatch = $routeMatch;
        $this->currentLocale = null;
        return $this;
    }
}
