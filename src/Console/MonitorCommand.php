<?php


namespace Golly\QueryMonitor\Console;

use Golly\QueryMonitor\ListenServer;
use Illuminate\Console\Command;

/**
 * Class MonitorCommand
 * @package Golly\QueryMonitor\Console
 */
class MonitorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'query:monitor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show in real-time SQL Queries';

    /**
     * @return int
     */
    public function handle(): int
    {
        $uri = config('query-monitor.uri');
        $this->info('Listen queries on ' . $uri);
        $listener = new ListenServer($uri);
        $listener->setOutput($this->output);
        $listener->run();

        return 0;
    }
}
