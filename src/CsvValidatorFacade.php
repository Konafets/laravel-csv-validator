<?php namespace Konafets\CsvValidator;

use Illuminate\Support\Facades\Facade;

/**
 * Class CsvValidatorFacade
 *
 * @package Konafets\CsvValidator
 * @author Stefano Kowalke <info@arroba-it.de>
 */
class CsvValidatorFacade extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-csv-validator';
    }
}
