<?php

namespace Luminee\Migrations;

use Exception;
use Throwable;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Foundation\Application;
use Luminee\Migrations\Console\Supporters\Input;
use Luminee\Migrations\Console\Supporters\Output;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Luminee\Migrations\Console\Supporters\Application as Chariot;

class Kernel
{
    /**
     * The application implementation.
     *
     * @var Application
     */
    protected $app;

    /**
     * The Artisan application instance.
     *
     * @var Chariot
     */
    protected $chariot;

    /**
     * The bootstrap classes for the application.
     *
     * @var array
     */
    protected $bootstrapperList = [
        \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
        \Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
        \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
        \Illuminate\Foundation\Bootstrap\RegisterFacades::class,
        \Illuminate\Foundation\Bootstrap\SetRequestForConsole::class,
        \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
        \Illuminate\Foundation\Bootstrap\BootProviders::class,
    ];

    /**
     * Create a new console kernel instance.
     *
     * @param Application $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Run the console application.
     *
     * @param Input $input
     * @param Output $output
     * @return int
     */
    public function handle(Input $input, $output = null)
    {
        try {
            $this->bootstrap();
            return $this->getChariot()->run($input, $output);
        } catch (Exception $e) {
            $this->reportException($e);
            $this->renderException($output, $e);
            return 1;
        } catch (Throwable $e) {
            $e = new FatalThrowableError($e);
            $this->reportException($e);
            $this->renderException($output, $e);
            return 1;
        }
    }

    /**
     * Bootstrap the application for artisan commands.
     *
     * @return void
     */
    public function bootstrap()
    {
        if (!$this->app->hasBeenBootstrapped()) {
            $this->app->bootstrapWith($this->bootstrapperList);
        }

        $this->app->loadDeferredProviders();
    }

    /**
     * Get the Artisan application instance.
     *
     * @return Chariot
     */
    protected function getChariot()
    {
        if (is_null($this->chariot)) {
            return $this->chariot = (new Chariot($this->app));
        }

        return $this->chariot;
    }

    /**
     * Terminate the application.
     *
     * @return void
     */
    public function terminate()
    {
        $this->app->terminate();
    }

    /**
     * Report the exception to the exception handler.
     *
     * @param Exception $e
     * @return void
     */
    protected function reportException(Exception $e)
    {
        $this->app[ExceptionHandler::class]->report($e);
    }

    /**
     * Report the exception to the exception handler.
     *
     * @param Output $output
     * @param Exception $e
     * @return void
     */
    protected function renderException(Output $output, Exception $e)
    {
        $this->app[ExceptionHandler::class]
            ->renderForConsole($output->getInterface(), $e);
    }

}
