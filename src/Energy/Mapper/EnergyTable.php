<?php

namespace Energy\Mapper;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

class EnergyTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function fetchByDay(\DateTime $dateTime)
    {
        $resultSet = $this->tableGateway->select(function (Select $select) use ($dateTime) {
            $select->where->like('datetime', $dateTime->format('Y-m-d') . '%');
            $select->order('datetime ASC');
        });
        return $resultSet;
    }

    public function save(Energy $energy)
    {
        $data = array(
            'power_usage_low' => $energy->power_usage_low,
            'power_usage_hi'  => $energy->power_usage_hi,
            'power_return_low'  => $energy->power_return_low,
            'power_return_hi'  => $energy->power_return_hi,
            'current_power_usage'  => $energy->current_power_usage,
            'current_power_return'  => $energy->current_power_return,
            'gas_usage'  => $energy->gas_usage,
            'datetime'  => $energy->datetime,
        );

        $this->tableGateway->insert($data);
    }


}
