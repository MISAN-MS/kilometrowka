<?php

class DB
{
    const CREATE_TIMESTAMP = 'created_at';
    const UPDATED_TIMESTAMP = 'updated_at';
    const DELETED_TIMESTAMP = 'deleted_at';
    const TIMESTAMP_DATE_FORMAT = 'Y-m-d H:i:s';

    private $dsn;
    private $username;
    private $passwd;
    private $options;
    private $connection;
    private $sql;
    private $where = [];
    private $join = [];
    private $limit = '';
    private $groupBy = '';
    private $orderBy = '';
    private $withDeleted = false;
    private $params = [];

    public function __construct($dsn = '', $username = '', $passwd = '', $options = [])
    {
        $this->dsn = $dsn?:('mysql:dbname=' . conf('DB_NAME') . ';host=' . conf('DB_HOST'));
        $this->username = $username?:conf('DB_USER');
        $this->passwd = $passwd?:conf('DB_PASS');
        $this->options = $options?:[];
        try {
            $this->connection = new PDO($this->dsn, $this->username, $this->passwd, $this->options);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $ex) {
            echo ("MySQL connection error Ex001");
        }
        return $this;
    }

    public function get($fetch = PDO::FETCH_CLASS)
    {
        $this->buildQuery();
        try {
            $stmt = $this->connection->prepare($this->sql);
            $stmt->execute();
        } catch (PDOException $ex) {
            echo $ex;
        }
        return $stmt->fetchAll($fetch);
    }

    public function query($query)
    {
        $this->sql = $query;
        return $this;
    }

    public function select($table, $collumns = [])
    {
        $this->sql = 'SELECT ';
        if(count($collumns) == 0) {
            $this->sql .= '* ';
        } else {
            $this->sql .= implode(', ', $collumns);
        }
        $this->sql .= ' FROM ' . $table;
        return $this;
    }

    public function insert($table, $values = [])
    {
        $createdTime = new DateTime();
        $values = array_merge($values, [self::CREATE_TIMESTAMP => $createdTime->format(self::TIMESTAMP_DATE_FORMAT)]);
        $this->sql = 'INSERT INTO ' . $table . '(' . implode(', ', array_keys($values)) . ') VALUES (';
        foreach ($values as $key=>$value) {
            $this->setParam($key, $value);
            $this->sql .= ':' . $key . ', ';
        }
        $this->sql = rtrim($this->sql, ', ');
        $this->sql .= ')';
        return $this;
    }

    public function update($table, $values = [])
    {
        $updatedTime = new DateTime();
        $values = array_merge($values, [self::UPDATED_TIMESTAMP => $updatedTime->format(self::TIMESTAMP_DATE_FORMAT)]);
        $this->sql = 'UPDATE ' . $table . ' SET (';
        foreach ($values as $key=>$value) {
            $this->setParam($key, $value);
            $this->sql .= $key . ' = :' . $key . ', ';
        }
        $this->sql = rtrim($this->sql, ', ');
        $this->sql .= ')';
        return $this;
    }

    public function delete($table)
    {
        $deleteTime = new DateTime();
        $this->update($table, [self::DELETED_TIMESTAMP => $deleteTime->format(self::TIMESTAMP_DATE_FORMAT)]);
        return $this;
    }

    public function where($coll, $value, $test = '=')
    {
        $this->whereParts('AND', $coll, $value, $test);
        return $this;
    }

    public function orWhere($coll, $value, $test = '=')
    {
        $this->whereParts('OR', $coll, $value, $test);
        return $this;
    }

    public function IJoin($joinTable, $joinTableCollumn, $onCollumns, $test = '=')
    {
        $this->joinParts('INNER', $joinTable, $joinTableCollumn . $test . $onCollumns);
        return $this;
    }

    public function LJoin($joinTable, $joinTableCollumn, $onCollumns, $test = '=')
    {
        $this->joinParts('LEFT', $joinTable, $joinTableCollumn . $test . $onCollumns);
        return $this;
    }

    public function RJoin($joinTable, $joinTableCollumn, $onCollumns, $test = '=')
    {
        $this->joinParts('RIGHT', $joinTable, $joinTableCollumn . $test . $onCollumns);
        return $this;
    }

    public function limit($limit = 10)
    {
        $this->limit = 'LIMIT :limitResult';
        $this->setParam('limitResult', $limit);
        return $this;
    }

    public function group($collumns)
    {

        if(!is_array($collumns)) {
            $this->groupBy = 'GROUP BY ' . $collumns;
        } else {
            $this->groupBy = 'GROUP BY ' . implode( ', ', $collumns);
        }
        return $this;
    }

    public function order($collumns, $direction = 'ASC')
    {
        $this->orderBy = 'ORDER BY ' . $collumns . ' ' . $direction;
        return $this;
    }

    public function withDeleted()
    {
        $this->withDeleted = true;
        return $this;
    }

    private function buildQuery()
    {
        if(!empty($this->join)) {
            $this->sql .= ' ' . implode(' ', $this->join);
        }
        if(!$this->withDeleted) {
            $this->sql .= ' WHERE ' . self::DELETED_TIMESTAMP . ' IS NULL';
        } else {
            $this->sql .= ' WHERE 1=1';
        }
        if(!empty($this->where)) {
            if(isset($this->where['AND'])) {
                $this->sql .= ' AND ' . implode(' AND ', $this->where['AND']);
            }
            if(isset($this->where['OR'])) {
                $this->sql .= ' OR ' . implode(' OR ', $this->where['OR']);
            }
        }
        $this->sql .= ' ' . $this->groupBy . ' ' . $this->orderBy . ' ' . $this->limit . ';';
        return $this;
    }

    private function setParam($key, $param)
    {
        $this->params[$key] = $param;
    }

    private function setParams($params)
    {
        foreach ($params as $key => $param) {
            $this->setParam($key, $param);
        }
    }

    private function whereParts($type, $coll, $value, $test)
    {
        $this->where[$type][] = $coll . ' ' . $test . ' :' . $coll;
        $this->setParam($coll, $value);
    }

    private function joinParts($type, $joinTable, $onColumnsTest)
    {
        $this->join[] = $type . ' JOIN ' . $joinTable . ' ON (' . $onColumnsTest . ')';
    }
}