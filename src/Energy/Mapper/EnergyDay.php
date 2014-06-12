<?php

namespace Energy\Mapper;

class EnergyDay
{
    public $power_usage_low;
    public $power_usage_hi;
    public $power_return_low;
    public $power_return_hi;
    public $gas_usage;
    public $date;

    public function exchangeArray($data)
    {
        $this->power_usage_low = (isset($data['power_usage_low'])) ? (float) $data['power_usage_low'] : null;
        $this->power_usage_hi  = (isset($data['power_usage_hi'])) ? (float) $data['power_usage_hi'] : null;
        $this->power_return_low  = (isset($data['power_return_low'])) ? (float) $data['power_return_low'] : null;
        $this->power_return_hi  = (isset($data['power_return_hi'])) ? (float) $data['power_return_hi'] : null;
        $this->gas_usage  = (isset($data['gas_usage'])) ? (float) $data['gas_usage'] : null;
        $this->date  = (isset($data['date'])) ? $data['date'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}
