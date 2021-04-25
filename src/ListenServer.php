<?php


namespace Golly\QueryMonitor;


use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
use React\Socket\Server;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListenServer
 * @package Golly\QueryMonitor
 */
class ListenServer
{

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var Server
     */
    protected $socket;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * ListenQueries constructor.
     */
    public function __construct(string $uri)
    {
        $this->loop = Factory::create();
        $this->socket = new Server($uri, $this->loop);
    }

    /**
     * @return void
     */
    public function run()
    {
        $this->socket->on('connection', function (ConnectionInterface $connection) {
            $connection->on('data', function ($data) use ($connection) {
                $entity = unserialize($data);
                if ($entity instanceof QueryEntity) {
                    if ($entity->isSlowQuery()) {
                        $this->warn($entity->format());
                    } else {
                        $this->info($entity->format());
                    }
                } else {
                    $this->output->writeln((string)$entity);
                }
                $connection->close();
            });
        });

        $this->loop->run();
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    /**
     * @param string $string
     * @return void
     */
    protected function info(string $string)
    {
        $this->output->writeln("<info>{$string}</info>");
    }

    /**
     * @param string $string
     * @return void
     */
    protected function warn(string $string)
    {
        if (!$this->output->getFormatter()->hasStyle('warning')) {
            $style = new OutputFormatterStyle('yellow');
            $this->output->getFormatter()->setStyle('warning', $style);
        }
        $this->output->writeln("<warning>{$string}</warning>");
    }

    /**
     * @param int $count
     * @return void
     */
    protected function newLine($count = 1)
    {
        $this->output->writeln(str_repeat("\n", $count));
    }
}
