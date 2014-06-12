<?php

namespace Energy\Mapper;

class Energy
{
    public $power_usage_low;
    public $power_usage_hi;
    public $power_return_low;
    public $power_return_hi;
    public $current_power_usage;
    public $current_power_return;
    public $gas_usage;
    public $datetime;

    public function exchangeArray($data)
    {
        $this->power_usage_low = (isset($data['power_usage_low'])) ? (float) $data['power_usage_low'] : null;
        $this->power_usage_hi  = (isset($data['power_usage_hi'])) ? (float) $data['power_usage_hi'] : null;
        $this->power_return_low  = (isset($data['power_return_low'])) ? (float) $data['power_return_low'] : null;
        $this->power_return_hi  = (isset($data['power_return_hi'])) ? (float) $data['power_return_hi'] : null;
        $this->current_power_usage  = (isset($data['current_power_usage'])) ? (float) $data['current_power_usage'] : null;
        $this->current_power_return  = (isset($data['current_power_return'])) ? (float) $data['current_power_return'] : null;
        $this->gas_usage  = (isset($data['gas_usage'])) ? (float) $data['gas_usage'] : null;
        $this->datetime  = (isset($data['datetime'])) ? $data['datetime'] : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}
