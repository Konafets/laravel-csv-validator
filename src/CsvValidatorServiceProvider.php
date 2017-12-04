<?php namespace Konafets\CsvValidator;

use Illuminate\Support\ServiceProvider;

/**
 * Class CsvValidatorServiceProvider
 *
 * @package Konafets\CsvValidator
 * @author Stefano Kowalke <info@arroba-it.de>
 */
class CsvValidatorServiceProvider extends ServiceProvider
{
    /** @var bool Loading of the provider should be deferred */
    protected $defer = true;

    public function register()
    {
        $this->app->singleton('laravel-csv-validator', function () {
            return new CsvValidator();
        });
    }
}
