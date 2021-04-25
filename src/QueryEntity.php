<?php


namespace Golly\QueryMonitor;

/**
 * Class QueryEntity
 * @package Golly\QueryMonitor
 */
class QueryEntity
{
    /**
     * The SQL query that was executed.
     *
     * @var string
     */
    protected $sql;

    /**
     * The array of query bindings.
     *
     * @var array
     */
    protected $bindings;

    /**
     * The number of milliseconds it took to execute the query.
     *
     * @var float
     */
    protected $time;

    /**
     * @var int
     */
    protected $number = 1;

    /**
     * @var int
     */
    protected $slowTime = 100;

    /**
     * 输出模版
     *
     * @var string
     */
    protected $output = '[[[query_number]]][[[query_time]]] [[query]]';

    /**
     * QueryEntity constructor.
     * @param string $sql
     * @param array $bindings
     * @param float $time
     * @param int $number
     */
    public function __construct(string $sql, array $bindings, float $time, int $number)
    {
        $this->sql = $sql;
        $this->bindings = $bindings;
        $this->time = $time;
        $this->number = $number;
    }

    /**
     * @return bool
     */
    public function isSlowQuery(): bool
    {
        return $this->time > $this->slowTime;
    }

    /**
     * The number of milliseconds it took to execute the query.
     *
     * @return string
     */
    public function time(): string
    {
        if ($this->time < 1) {
            return round($this->time * 1000) . 'μs';
        } elseif ($this->time < 1000) {
            return round($this->time, 2) . 'ms';
        }

        return round($this->time / 1000, 2) . 's';
    }

    /**
     * @return string
     */
    public function format(): string
    {
        foreach ($this->bindings as &$binding) {
            if (is_string($binding)) {
                $binding = "'{$binding}'";
            }
        }
        $sql = vsprintf(
            str_replace('?', '%s', $this->sql),
            $this->bindings
        );
        $replace = [
            '[[query_number]]' => $this->number,
            '[[query_time]]' => $this->time(),
            '[[query]]' => $sql
        ];

        return str_replace(array_keys($replace), array_values($replace), $this->output);
    }
}
