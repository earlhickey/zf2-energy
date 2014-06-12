<?php

namespace Energy\Mapper;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

class EnergyDayTable
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

    public function fetchByDay(\DateTime $minDate, \DateTime $maxDate)
    {
        $resultSet = $this->tableGateway->select(function (Select $select) use ($minDate, $maxDate) {
            $select->where->between('date', $minDate->format('Y-m-d'), $maxDate->format('Y-m-d'));
            $select->order('date ASC');
        });
        return $resultSet;
    }

    public function save(EnergyDay $energyDay)
    {
        $data = array(
            'power_usage_low' => $energyDay->power_usage_low,
            'power_usage_hi' => $energyDay->power_usage_hi,
            'power_return_low' => $energyDay->power_return_low,
            'power_return_hi' => $energyDay->power_return_hi,
            'gas_usage' => $energyDay->gas_usage,
            'date' => $energyDay->date,
        );

        $this->tableGateway->insert($data);
    }


}
