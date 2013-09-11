<?php
/**
 * Libra-CMS (http://www.ejoom.com/libra-cms/)
 */

namespace LibraLocale\Navigation;

use Traversable;
use Zend\Navigation\Exception;

/**
 * A simple container class for {@link LibraLocale\Navigation\Page} pages
 */
class Navigation extends AbstractContainer
{
    /**
     * Creates a new navigation container
     *
     * @param  array|Traversable $pages    [optional] pages to add
     * @throws Exception\InvalidArgumentException  if $pages is invalid
     */
    public function __construct($pages = null)
    {
        if ($pages && (!is_array($pages) && !$pages instanceof Traversable)) {
            throw new Exception\InvalidArgumentException(
                'Invalid argument: $pages must be an array, an '
                . 'instance of Traversable, or null'
            );
        }

        if ($pages) {
            $this->addPages($pages);
        }
    }
}
