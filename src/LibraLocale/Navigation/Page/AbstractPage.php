<?php
/**
 * Libra-CMS (http://www.ejoom.com/libra-cms/)
 */

namespace LibraLocale\Navigation\Page;

use Traversable;
use Zend\Navigation\Exception;
use Zend\Navigation\Page\AbstractPage as ZendAbstractPage;
use Zend\Stdlib\ArrayUtils;

/**
 * Base class for LibraLocae\Navigation\Page pages
 */
abstract class AbstractPage extends ZendAbstractPage
{
    /**
     * Factory for Zend\Navigation\Page classes
     *
     * A specific type to construct can be specified by specifying the key
     * 'type' in $options. If type is 'uri' or 'mvc', the type will be resolved
     * to Zend\Navigation\Page\Uri or Zend\Navigation\Page\Mvc. Any other value
     * for 'type' will be considered the full name of the class to construct.
     * A valid custom page class must extend Zend\Navigation\Page\AbstractPage.
     *
     * If 'type' is not given, the type of page to construct will be determined
     * by the following rules:
     * - If $options contains either of the keys 'action', 'controller',
     *   or 'route', a Zend\Navigation\Page\Mvc page will be created.
     * - If $options contains the key 'uri', a Zend\Navigation\Page\Uri page
     *   will be created.
     *
     * @param  array|Traversable $options  options used for creating page
     * @return AbstractPage  a page instance
     * @throws Exception\InvalidArgumentException if $options is not
     *                                            array/Traversable
     * @throws Exception\InvalidArgumentException if 'type' is specified
     *                                            but class not found
     * @throws Exception\InvalidArgumentException if something goes wrong
     *                                            during instantiation of
     *                                            the page
     * @throws Exception\InvalidArgumentException if 'type' is given, and
     *                                            the specified type does
     *                                            not extend this class
     * @throws Exception\InvalidArgumentException if unable to determine
     *                                            which class to instantiate
     */
    public static function factory($options)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (!is_array($options)) {
            throw new Exception\InvalidArgumentException(
                'Invalid argument: $options must be an array or Traversable'
            );
        }

        if (isset($options['type'])) {
            $type = $options['type'];
            if (is_string($type) && !empty($type)) {
                switch (strtolower($type)) {
                    case 'mvc':
                        $type = 'LibraLocale\Navigation\Page\Mvc';
                        break;
                    case 'uri':
                        $type = 'LibraLocale\Navigation\Page\Uri';
                        break;
                }

                if (!class_exists($type, true)) {
                    throw new Exception\InvalidArgumentException(
                        'Cannot find class ' . $type
                    );
                }

                $page = new $type($options);
                if (!$page instanceof ZendAbstractPage) {
                    throw new Exception\InvalidArgumentException(
                        sprintf(
                            'Invalid argument: Detected type "%s", which ' .
                            'is not an instance of Zend\Navigation\Page',
                            $type
                        )
                    );
                }
                return $page;
            }
        }

        $hasUri = isset($options['uri']);
        $hasMvc = isset($options['action']) || isset($options['controller'])
                || isset($options['route']);

        if ($hasMvc) {
            return new Mvc($options);
        } elseif ($hasUri) {
            return new Uri($options);
        } else {
            throw new Exception\InvalidArgumentException(
                'Invalid argument: Unable to determine class to instantiate'
            );
        }
    }
}
