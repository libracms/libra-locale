<?php

/*
 * eJoom.com
 * This source file is subject to the new BSD license.
 */

namespace LibraLocale;

/**
 * Put enviroment formats
 * Based at linux man locale
 *
 * @author duke
 */
class LocaleEnv
{
    /**
     * Character classification and case conversion
     * @var type 
     */
    protected $ctype;

    /**
     * Collation order.
     * @var type
     */
    protected $collate;

    /**
     * Date and time formats.
     * @var type
     */
    protected $time;

    /**
     *  Non-monetary numeric formats.
     * @var type
     */
    protected $numeric;

    /**
     * Monetary formats.
     * @var type
     */
    protected $monetary;

    /**
     * Paper size (For creating pdf, etc).
     * @var type
     */
    protected $paper;

    /**
     * Name formats.
     * @var type
     */
    protected $name;

    /**
     * Address formats and location information.
     * @var type
     */
    protected $address;

    /**
     * Address formats and location information.
     * @var type
     */
    protected $telephone;

    /**
     * Measurement units (Metric or Other).
     * @var type
     */
    protected $measurement;

    /**
     * Metadata about the locale information.
     * @var type
     */
    protected $identification;
}
