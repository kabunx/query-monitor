<?php


namespace Golly\QueryMonitor;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Log;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;

/**
 * Class DispatchQueries
 * @package Golly\QueryMonitor
 */
class DispatchQueries
{
    /**
     * @var
     */
    protected $app;

    /**
     * @var string
     */
    protected $uri;

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var Connector
     */
    protected $connector;

    /**
     * @var int
     */
    protected $number = 0;

    /**
     * DispatchQueries constructor.
     */
    public function __construct()
    {
        $this->loop = Factory::create();
        $this->connector = new Connector($this->loop);
        $this->uri = config('query-monitor.uri', '0.0.0.0:1605');
    }

    /**
     * @param Application $app
     */
    public function init(Application $app)
    {
        $this->send($this->getRunningInfo($app));
    }

    /**
     * @param QueryExecuted $query
     */
    public function query(QueryExecuted $query)
    {
        $this->number++;
        $entity = new QueryEntity($query->sql, $query->bindings, $query->time, $this->number);
        $this->send($entity);
    }

    /**
     * @param $data
     */
    protected function send($data)
    {
        $this->connector->connect(
            $this->uri
        )->then(
            function (ConnectionInterface $connection) use ($data) {
                $connection->write(serialize($data));
            },
            function (\Throwable $e) {
                Log::error($e->getMessage());
            }
        );

        $this->loop->run();
    }

    /**
     * @param Application $app
     * @return string
     */
    protected function getRunningInfo(Application $app)
    {
        if ($app->runningInConsole()) {
            $command = (array)$app['request']->server('argv', []);

            return sprintf('[CONSOLE] %s', implode(' ', $command));
        }

        return sprintf('[HTTP]%s %s', $app['request']->method(), $app['request']->path());
    }
}
