<?php

namespace LibraLocale\View\Helper;

use Zend\Mvc\Router\RouteMatch;
use Zend\View\Helper\AbstractHelper;

/**
 * Description of IsLocale
 *
 * @author duke
 */
class IsLocale extends AbstractHelper
{
    /**
     * RouteInterface match returned by the router.
     *
     * @var RouteMatch
     */
    protected $routeMatch;

    /**
     * Compare locale against currentc locale case insensitive
     *
     * @param string $locale
     * @param bool $insensitive
     * @return bool
     */
    public function __invoke($locale, $insensitive = true)
    {
        if ($insensitive) {
            $result = strtolower($this->routeMatch->getParam('locale')) === strtolower($locale);
        } else {
            $result = $this->routeMatch->getParam('locale') === $locale;
        }
        return $result;
    }

    /**
     * Set route match returned by the router.
     *
     * @param  RouteMatch $routeMatch
     * @return IsLocale
     */
    public function setRouteMatch(RouteMatch $routeMatch)
    {
        $this->routeMatch = $routeMatch;
        return $this;
    }
}
