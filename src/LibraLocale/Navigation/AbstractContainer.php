<?php
/**
 * Libra-CMS (http://www.ejoom.com/libra-cms/)
 */

namespace LibraLocale\Navigation;

use Traversable;
use Zend\Navigation\AbstractContainer as ZendAbstractContainer;
use Zend\Navigation\Exception;

/**
 * Zend\Navigation\Container
 *
 * AbstractContainer class for LibraLocale\Navigation\Page classes.
 */
abstract class AbstractContainer extends ZendAbstractContainer
{
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
            $page = Page\AbstractPage::factory($page);
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
