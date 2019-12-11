<?php


namespace EasySwoole\ORM\Db;


use EasySwoole\Mysqli\Client;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\Exception\Exception;

class MysqliClient extends Client implements ClientInterface
{

    public function query(QueryBuilder $builder, bool $rawQuery = false): Result
    {
        $result = new Result();
        $ret = null;
        $errno = 0;
        $error = '';
        try{
            if($rawQuery){
                $ret = $this->rawQuery($builder->getLastQuery(),$this->config->getTimeout());
            }else{
                $stmt = $this->mysqlClient()->prepare($builder->getLastPrepareQuery(),$this->config->getTimeout());
                if($stmt){
                    $ret = $stmt->execute($builder->getLastBindParams(),$this->config->getTimeout());
                }else{
                    $ret = false;
                }
            }

            $errno = $this->mysqlClient()->errno;
            $error = $this->mysqlClient()->error;
            $insert_id     = $this->mysqlClient()->insert_id;
            $affected_rows = $this->mysqlClient()->affected_rows;
            /*
             * 重置mysqli客户端成员属性，避免下次使用
             */
            $this->mysqlClient()->errno = 0;
            $this->mysqlClient()->error = '';
            $this->mysqlClient()->insert_id     = 0;
            $this->mysqlClient()->affected_rows = 0;
            //结果设置
            $result->setResult($ret);
            $result->setLastError($error);
            $result->setLastErrorNo($errno);
            $result->setLastInsertId($insert_id);
            $result->setAffectedRows($affected_rows);
        }catch (\Throwable $throwable){
            throw $throwable;
        }finally{
            if($errno){
                /*
                    * 断线的时候回收链接
                */
                if(in_array($errno,[2006,2013])){
                    $this->close();
                }
                throw new Exception($error);
            }

        }
        return $result;
    }
}