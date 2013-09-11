<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace LibraLocale\Navigation\Service;

use LibraLocale\Navigation\Navigation;
use Zend\Navigation\Service\AbstractNavigationFactory as ZendAbstractNavigationFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Abstract navigation factory
 */
abstract class AbstractNavigationFactory extends ZendAbstractNavigationFactory
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return \LibraLocale\Navigation\Navigation
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $pages = $this->getPages($serviceLocator);
        return new Navigation($pages);
    }
}
