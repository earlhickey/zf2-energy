<?php

namespace Energy\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Energy\Service\Energy;
use Zend\Stdlib\DateTime;
use Energy\Mapper\EnergyDay;

class IndexController extends AbstractActionController
{
    protected $energy;
    protected $energyDay;
    protected $dateTime;

    /**
     * Current power
     */
    public function indexAction()
    {
        // today
        $dateTime = new \DateTime();
        //$dateTime->setDate(2014, 10, 09);
        $records = $this->getEnergyTable()->fetchByDay($dateTime);
        $totalRecords = $records->count();

        // declare vars and arrays
        $firstPowerUsage = null;
        $firstPowerReturn = null;
        $firstGas = null;
        $lastPowerUsage = null;
        $lastPowerReturn = null;
        $lastGas = null;
        $lastUpdate = null;
        $powerUsage = array();
        $powerReturn = array();
        foreach ($records as $key => $record) {
            if ($key == 0) { // record
                $firstPowerUsage = $record->power_usage_low + $record->power_usage_hi;
                $firstPowerReturn = $record->power_return_low + $record->power_return_hi;
                $firstGas = $record->gas_usage;
            }
            if ($key == $totalRecords - 1) { // last record
                $lastPowerUsage = $record->power_usage_low + $record->power_usage_hi;
                $lastPowerReturn = $record->power_return_low + $record->power_return_hi;
                $lastGas = $record->gas_usage;
                $lastUpdate = $record->datetime;
            }
            $powerUsage[] = $record->current_power_usage;
            $powerReturn[] = $record->current_power_return;
        }

        // by day
        $min = new \DateInterval('P30D');
        $min->invert = 1; //Make it negative.
        $max = new \DateInterval('P1D');
        $max->invert = 1; //Make it negative.
        $minDate = new \DateTime();
        $minDate->add($min);
        $maxDate = new \DateTime();
        $maxDate->add($max);
        $days = $this->getEnergyDayTable()->fetchByDay($minDate, $maxDate);

        foreach ($days as $day) {
            $powerUsageByDay[] = "['" . $day->date . "'," . $day->power_usage_total . "]";
            $powerReturnByDay[] = -1 * $day->power_return_total;
            $powerAverageByDay[] = $day->power_usage_total - $day->power_return_total;
            $gasByDay[] = "['" . $day->date . "'," . $day->gas_usage . "]";
        }

        // add today to byDay
        $powerUsageByDay[] = "['" . date("Y-m-d") . "'," . round($lastPowerUsage - $firstPowerUsage, 2) . "]";
        $powerReturnByDay[] = -1 * round($lastPowerReturn - $firstPowerReturn, 2);
        $powerAverageByDay[] = round(($lastPowerUsage - $firstPowerUsage) - ($lastPowerReturn - $firstPowerReturn), 2);
        $gasByDay[] = "['" . date("Y-m-d") . "'," . round($lastGas - $firstGas, 2) . "]";

        // by month
        $months = $this->getEnergyDayTable()->fetchByMonth();
        foreach ($months as $month) {
            $powerUsageByMonth[] = "['" . $month->date . "'," . $month->power_usage_total . "]";
            $powerReturnByMonth[] = -1 * $month->power_return_total;
            $powerAverageByMonth[] = $month->power_usage_total - $month->power_return_total;
            $gasByMonth[] = "['" . $month->date . "'," . $month->gas_usage . "]";
        }

        return array(
            'lastUpdate' => $lastUpdate,
            'currentUsage' => end($powerUsage),
            'currentReturn' => end($powerReturn),
            'todayUsage' => round($lastPowerUsage - $firstPowerUsage, 2),
            'todayReturn' => round($lastPowerReturn - $firstPowerReturn, 2),
            'todayGas' => round($lastGas - $firstGas, 2),

            'powerUsage' => implode(',', $powerUsage),
            'powerReturn' => implode(',', $powerReturn),

            'powerUsageByDay' => implode(',', $powerUsageByDay),
            'powerReturnByDay' => implode(',', $powerReturnByDay),
            'powerAverageByDay' => implode(',', $powerAverageByDay),
            'gasByDay' => implode(',', $gasByDay),

            'powerUsageByMonth' => implode(',', $powerUsageByMonth),
            'powerReturnByMonth' => implode(',', $powerReturnByMonth),
            'powerAverageByMonth' => implode(',', $powerAverageByMonth),
            'gasByMonth' => implode(',', $gasByMonth),
        );
    }


    /**
     * Calculate totals by hour
     */
    public function hoursAction()
    {
        $dateTime = new \DateTime();
        $dateTime->setDate(2014, 11, 06);

        $records = $this->getEnergyTable()->fetchByDay($dateTime);
        $records = $records->toArray();

        $hour = 0;
        $topOfHours = array();
        foreach ($records as $record) {
            // record datetime to datetime object for comparing
            $recordDate = new \DateTime($record['datetime']);

            // save first record of every hour
            if ($hour == $recordDate->format('G')) {
                $topOfHours[] = $record;
                $hour ++;
            }
        }
        //var_dump($topOfHours);

        $diffs = array();
        $n = count($topOfHours);
        for ($i = 1, $n; $i < $n; $i++) {
            $power_usage_low = $topOfHours[$i]['power_usage_low'] - $topOfHours[$i-1]['power_usage_low'];
            $power_usage_hi = $topOfHours[$i]['power_usage_hi'] - $topOfHours[$i-1]['power_usage_hi'];
            $diffs['power_usage_low'][] = round($power_usage_low, 2);
            $diffs['power_usage_hi'][] = round($power_usage_hi, 2);
            $diffs['power_usage_total'][] = round($power_usage_low + $power_usage_hi, 2);
            $power_return_low = $topOfHours[$i]['power_return_low'] - $topOfHours[$i-1]['power_return_low'];
            $power_return_hi = $topOfHours[$i]['power_return_hi'] - $topOfHours[$i-1]['power_return_hi'];
            $diffs['power_return_low'][] = round($power_return_low, 2);
            $diffs['power_return_hi'][] = round($power_return_hi, 2);
            $diffs['power_return_total'][] = round($power_return_low + $power_return_hi, 2);
            $gas_usage = $topOfHours[$i]['gas_usage'] - $topOfHours[$i-1]['gas_usage'];
            $diffs['gas_usage_total'][] = round($gas_usage, 2);
        }
        var_dump($topOfHours, $diffs['power_usage_total'], $diffs['power_return_total'], $diffs['gas_usage_total']);

        return array(
            'dateTime' => $dateTime,
            'powerUsage' => implode(',', $diffs['power_usage_total']),
            'powerReturn' => implode(',', $diffs['power_return_total'])
        );
    }

    public function getEnergyTable()
    {
        if (!$this->energy) {
            $sm = $this->getServiceLocator();
            $this->energy = $sm->get('Energy\Mapper\EnergyTable');
        }
        return $this->energy;
    }

    public function getEnergyDayTable()
    {
        if (!$this->energyDay) {
            $sm = $this->getServiceLocator();
            $this->energyDay = $sm->get('Energy\Mapper\EnergyDayTable');
        }
        return $this->energyDay;
    }

}
