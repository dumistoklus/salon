<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 25.11.11
 * Time: 14:27
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Common\DBAccess;
use \Orgup\Application\Exception\DBAccess\FromNotInitialized;
use \Orgup\Common\String;

abstract class ActiveRecord extends DBAccess
{
    const ASC_ORDER = 'ASC';
    const DESC_ORDER = 'DESC';

    const WHERE_MORE = '<';
    const WHERE_LESS = '>';
    const WHERE_EQUALS = '=';
    const WHERE_LIKE = ' LIKE ';

    private $limit = array();
    private $orderBy = array();
    private $where = array();
    private $from;
    private $sql;

    private $baseSQL;

    private $count;

    private $queryResult;
    /**
     * @param $start
     * @return ActiveRecord
     */
    public function start($start)
    {
        $this->limit['start'] = $start;
        return $this;
    }
    /**
     * @param $limit
     * @return ActiveRecord
     */
    public function limit($limit)
    {
        $this->limit['limit'] = $limit;

        return $this;
    }
    /**
     * @param $field
     * @param string $type
     * @return ActiveRecord
     */
    public function orderBy($field, $type = ActiveRecord::DESC_ORDER)
    {
        $this->orderBy['field'] = $field;
        $this->orderBy['type'] = $type;

        return $this;
    }
    /**
     * @param $field
     * @param $need
     * @param string $where
     * @return ActiveRecord
     */
    public function where($field, $need, $where = ActiveRecord::WHERE_EQUALS, $quote = true)
    {
        $this->where['field'][] = $field;
        $this->where['need'][] = $need;
        $this->where['where'][] = $where;
        $this->where['quote'][] = $quote;

        return $this;
    }
    /**
     * @param $from
     * @return ActiveRecord
     */
    protected function from($from, $as = '')
    {
        $this->from = '`'.$from.'`';
        if($as != '')
        {
            $this->from .= ' AS '.$as. ' ';
        }

        return $this;
    }

    protected function sql($sql)
    {
        $this->baseSQL = $sql;
    }

    public function resultSQL()
    {
        return $this->sql;
    }

    public function table()
    {
        return $this->from;
    }

    public function find()
    {
        if($this->queryResult === null)
        {
            if($this->from === null && $this->baseSQL === null) {
                throw new FromNotInitialized();
            }

            if($this->baseSQL == null)
            {
                $this->sql = new String("SELECT * FROM $this->from ");
            }
            else
            {
                $this->sql = new String($this->baseSQL);
            }

            $this->whereSQL($this->sql);

            $this->orderBySql($this->sql);

            $this->limitSql($this->sql);

            $stmt = $this->getDB()->prepare($this->sql);

            $this->whereParam($stmt);

            $stmt->execute();

            $this->queryResult = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }

        return $this->queryResult;
    }

    private function whereSQL(String &$sql)
    {
        if(!empty($this->where))
        {
            $sql->append(' WHERE ');

            $whereCount = sizeof($this->where['field']);

            for($i = 0; $i < $whereCount; $i++)
            {
                $field = $this->where['field'][$i];

                if($this->where['quote'][$i])
                {
                    $field = '`'.$field.'`';
                }

                $sql->append($field.$this->where['where'][$i].' :need'.$i.' ');

                if($i != $whereCount - 1)
                {
                    $sql->append(' AND ');
                }
            }
        }
    }

    private function whereParam( \Doctrine\DBAL\Driver\Statement &$stmt)
    {
        if(!empty($this->where))
        {
            $whereCount = sizeof($this->where['field']);

            for($i = 0; $i < $whereCount; $i++)
            {
                $need = $this->where['need'][$i];

                if($this->where['where'][$i] == ActiveRecord::WHERE_LIKE)
                {
                    $need = '%'.$need.'%';
                }

                $stmt->bindValue('need'.$i, $need);
            }
        }
    }

    private function orderBySql(String &$sql)
    {
        if(!empty($this->orderBy))
        {
            $sql->append(' ORDER BY `'.$this->orderBy['field'].'` '.$this->orderBy['type'].' ');
        }
    }

    private function limitSql(String &$sql)
    {
        if(!empty($this->limit))
        {
            $sql->append(' LIMIT ');
            if($startIsset = isset($this->limit['start']))
            {
                $sql->append($this->limit['start']);
            }

            if(isset($this->limit['limit']))
            {
                if($startIsset)
                {
                    $sql->append(',');
                }

                $sql->append($this->limit['limit']);
            }
        }
    }

    public function countOfAll()
    {
        if($this->count === null)
        {
            $sql = new String("SELECT COUNT(*) as cnt FROM $this->from ");
            $this->whereSQL($sql);
            $stmt = $this->getDB()->prepare($sql);
            $this->whereParam($stmt);
            $stmt->execute();
            $count = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->count = $count[0]['cnt'];
        }

        return $this->count;
    }

    protected function result()
    {
        return $this->find();
    }

    public function asArray()
    {
        return $this->result();
    }

    abstract public function iterator();
}
