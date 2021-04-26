<?php


namespace Golly\QueryMonitor;


use Golly\QueryMonitor\Console\MonitorCommand;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

/**
 * Class QueryServiceProvider
 * @package Golly\QueryMonitor
 */
class QueryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MonitorCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__ . '/../config/query-monitor.php' => config_path('query-monitor.php'),
        ]);
        $dispatch = new DispatchQueries();
        $dispatch->init($this->app);
        DB::listen(function ($query) use ($dispatch) {
            $dispatch->query($query);
        });
    }

}
