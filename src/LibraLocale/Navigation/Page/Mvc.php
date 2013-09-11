<?php
/**
 * Libra-CMS (http://www.ejoom.com/libra-cms/)
 */

namespace LibraLocale\Navigation\Page;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\RouteStackInterface;
use Zend\Navigation\Exception;
use Zend\Navigation\Page\Mvc as ZendMvc;

/**
 * Represents a page that is defined using controller, action, route
 * name and route params to assemble the href
 */
class Mvc extends ZendMvc
{
    /**
     * Returns href for this page
     *
     * This method uses {@link RouteStackInterface} to assemble
     * the href based on the page's properties.
     *
     * @see RouteStackInterface
     * @return string  page href
     * @throws Exception\DomainException if no router is set
     */
    public function getHref()
    {
        if ($this->hrefCache) {
            return $this->hrefCache;
        }

        $router = $this->router;
        if (null === $router) {
            $router = static::$defaultRouter;
        }

        if (!$router instanceof RouteStackInterface) {
            throw new Exception\DomainException(
                __METHOD__
                . ' cannot execute as no Zend\Mvc\Router\RouteStackInterface instance is composed'
            );
        }

        if ($this->useRouteMatch() && $this->getRouteMatch()) {
            $rmParams = $this->getRouteMatch()->getParams();

            if (isset($rmParams[ModuleRouteListener::ORIGINAL_CONTROLLER])) {
                $rmParams['controller'] = $rmParams[ModuleRouteListener::ORIGINAL_CONTROLLER];
                unset($rmParams[ModuleRouteListener::ORIGINAL_CONTROLLER]);
            }

            if (isset($rmParams[ModuleRouteListener::MODULE_NAMESPACE])) {
                unset($rmParams[ModuleRouteListener::MODULE_NAMESPACE]);
            }

            $params = array_merge($rmParams, $this->getParams());
        } else {
            $params = $this->getParams();
        }

        // Set current locale if it presents
        if ($this->getRouteMatch()) {
            $matches = $this->getRouteMatch();
            $currentLocaleAlias = $matches->getParam('locale') !== null ? $matches->getParam('locale') : false;
            if (!array_key_exists('locale', $params) && $currentLocaleAlias) {
                $params['locale'] = $currentLocaleAlias;
            }
        }

        if (($param = $this->getController()) != null) {
            $params['controller'] = $param;
        }

        if (($param = $this->getAction()) != null) {
            $params['action'] = $param;
        }

        switch (true) {
            case ($this->getRoute() !== null):
                $name = $this->getRoute();
                break;
            case ($this->getRouteMatch() !== null):
                $name = $this->getRouteMatch()->getMatchedRouteName();
                break;
            default:
                throw new Exception\DomainException('No route name could be found');
        }

        $options = array('name' => $name);

        // Add the fragment identifier if it is set
        $fragment = $this->getFragment();
        if (null !== $fragment) {
            $options['fragment'] = $fragment;
        }

        if (null !== ($query = $this->getQuery())) {
            $options['query'] = $query;
        }

        $url = $router->assemble($params, $options);

        return $this->hrefCache = $url;
    }

    /**
     * Adds a page to the container
     *
     * This method will inject the container as the given page's parent by
     * calling {@link Page\AbstractPage::setParent()}.
     *
     * @param  Page\AbstractPage|array|Traversable $page  page to add
     * @return AbstractContainer fluent interface, returns self
     * @throws Exception\InvalidArgumentException if page is invalid
     */
    public function addPage($page)
    {
        if ($page === $this) {
            throw new Exception\InvalidArgumentException(
                'A page cannot have itself as a parent'
            );
        }

        if (!$page instanceof Page\AbstractPage) {
            if (!is_array($page) && !$page instanceof Traversable) {
                throw new Exception\InvalidArgumentException(
                    'Invalid argument: $page must be an instance of '
                    . 'Zend\Navigation\Page\AbstractPage or Traversable, or an array'
                );
            }
            $page = AbstractPage::factory($page);
        }

        $hash = $page->hashCode();

        if (array_key_exists($hash, $this->index)) {
            // page is already in container
            return $this;
        }

        // adds page to container and sets dirty flag
        $this->pages[$hash] = $page;
        $this->index[$hash] = $page->getOrder();
        $this->dirtyIndex = true;

        // inject self as page parent
        $page->setParent($this);

        return $this;
    }
}
