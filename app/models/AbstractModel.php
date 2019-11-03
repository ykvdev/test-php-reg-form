<?php declare(strict_types=1);

namespace app\models;

use ParagonIE\EasyDB\EasyDB;
use ParagonIE\EasyDB\EasyStatement;

class AbstractModel
{
    /** @var string */
    public $dbTable;

    /** @var string */
    public $dbPk;

    /** @var array */
    public $dbFields;

    /** @var array */
    protected $config;

    /** @var \app\services\Container */
    protected $services;

    /** @var EasyDB */
    protected $db;

    public function __construct(array $config, \app\services\Container $services)
    {
        $this->config = $config;
        $this->services = $services;
        $this->db = $services->db();
    }

    /**
     * @param array|int $conditions
     *
     * @return bool
     */
    public function isExists($conditions) : bool
    {
        return (bool)$this->getRow($conditions, $this->dbPk);
    }

    /**
     * @param array|int $conditions
     * @param string|array $selectFields
     *
     * @return array|null
     */
    public function getRow($conditions, $selectFields = '*') : ?array
    {
        $conditions = is_array($conditions) ? $conditions : [$this->dbPk => $conditions];
        $result = $this->getRows($conditions, $selectFields, 1);
        return $result[0] ?? null;
    }

    /**
     * @param array $conditions
     * @param string|array $selectFields
     * @param null|int $limit
     * @param null|int $offset
     *
     * @return array
     */
    public function getRows(array $conditions = [], $selectFields = '*', $limit = null, $offset = null) : array
    {
        $selectFields = is_array($selectFields) ? '`' . implode('`, `', $selectFields) . '`' : $selectFields;

        if($conditions) {
            $where = EasyStatement::open();
            $fieldNumber = 1;
            foreach ($conditions as $field => $value) {
                if ($fieldNumber == 1) {
                    $where->with($field . ' = ?', $value);
                } else {
                    $where->andWith($field . ' = ?', $value);
                }

                $fieldNumber++;
            }
        } else {
            $where = null;
        }

        $statement = "SELECT {$selectFields} FROM {$this->dbTable}"
            . ($conditions ? ' WHERE ' . $where : '')
            . ($limit ? ' LIMIT ' . $limit : '')
            . ($offset ? ' OFFSET ' . $offset : '');
        $result = $where
            ? $this->db->safeQuery($statement, $where->values())
            : $this->db->safeQuery($statement);

        return $result;
    }

    /**
     * Returns last insert ID
     * 
     * @param array $data
     *
     * @return string
     */
    protected function insert(array $data) : string
    {
        $this->db->insert($this->dbTable, $this->filterFieldsList($data));

        return $this->db->lastInsertId();
    }

    /**
     * Returns affected rows count
     *
     * @param array $changes
     * @param array|int $conditions
     *
     * @return int
     */
    protected function update(array $changes, $conditions) : int
    {
        $conditions = is_array($conditions) ? $conditions : [$this->dbPk => $conditions];
        $result = $this->db->update($this->dbTable, $this->filterFieldsList($changes), $conditions);

        return $result;
    }

    /**
     * Returns affected rows count
     *
     * @param array|int $conditions
     *
     * @return int
     */
    protected function delete($conditions) : int
    {
        return $this->db->delete($this->dbTable,
            is_array($conditions) ? $conditions : [$this->dbPk => $conditions]);
    }

    private function filterFieldsList(array $data) : array
    {
        return array_intersect_key($data, array_flip($this->dbFields));
    }
}