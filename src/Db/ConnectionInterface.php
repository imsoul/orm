<?php


namespace EasySwoole\ORM\Db;


use EasySwoole\Pool\AbstractPool;

interface ConnectionInterface
{
    function defer(float $timeout = null):?ClientInterface;
    function getClientPool():AbstractPool;
}