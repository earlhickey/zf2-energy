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
        // get 5 min intervals
        $dateTime = new \DateTime();
        //$dateTime->setDate(2014, 06, 09);
        $records = $this->getEnergyTable()->fetchByDay($dateTime);

        //var_dump($dateTime, $records);

        // create arrays
        $powerUsage = array();
        $powerReturn = array();
        foreach ($records as $record) {
            $powerUsage[] = $record->current_power_usage;
            $powerReturn[] = $record->current_power_return;
        }

        // get daily indervals
        $min = new \DateInterval('P30D'); 
        $min->invert = 1; //Make it negative.
        $max = new \DateInterval('P1D'); 
        $max->invert = 1; //Make it negative.
        $minDate = new \DateTime();
        $minDate->add($min);
        $maxDate = new \DateTime();
        $maxDate->add($max);
        $days = $this->getEnergyDayTable()->fetchByDay($minDate, $maxDate);
        //var_dump($min, $minDate, $maxDate);

        // create arrays
        $powerUsageTotal = array();
        $powerReturnTotal = array();
        $gasTotal = array();
        foreach ($days as $day) {
            $powerUsageTotal[] = "['" . $day->date . "'," . round($day->power_usage_low + $day->power_usage_hi, 2) . "]";
            $powerReturnTotal[] = -1 * round($day->power_return_low + $day->power_return_hi, 2);
            $gasTotal[] = "['" . $day->date . "'," .round($day->gas_usage, 2) . "]";
        }

        //echo implode(',', $power);
        return array(
            'powerUsage' => implode(',', $powerUsage),
            'powerReturn' => implode(',', $powerReturn),
            'powerUsageTotal' => implode(',', $powerUsageTotal),
            'powerReturnTotal' => implode(',', $powerReturnTotal),
            'gasTotal' => implode(',', $gasTotal),
        );
    }


    /**
     * Calculate totals by hour
     */
    public function hoursAction()
    {
        $dateTime = new \DateTime();
        $dateTime->setDate(2014, 06, 06);

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
        }
        var_dump($topOfHours, $diffs['power_usage_total'], $diffs['power_return_total']);

        return array(
            'dateTime' => $dateTime,
            'powerUsage' => implode(',', $diffs['power_usage_total']),
            'powerReturn' => implode(',', $diffs['power_return_total'])
        );
    }


    /**
     * Calculate totals by day
     */
    /*public function daysAction()
    {
        $dateTime = new \DateTime();
        $dateTime->setDate(2014, 02, 15);

        while ($dateTime->format('Y-m-d') <= '2014-06-09') {
            $records = $this->getEnergyTable()->fetchByDay($dateTime);
            //var_dump($dateTime);

            // calculate day totals
            $records = $records->toArray();
            $first = reset($records);
            $last = end($records);

            //var_dump($first, $last);

            // create data array
            $energy = array();
            $energy['power_usage_low'] = round($last['power_usage_low'] - $first['power_usage_low'], 3);
            $energy['power_usage_hi'] = round($last['power_usage_hi'] - $first['power_usage_hi'], 3);
            $energy['power_return_low'] = round($last['power_return_low'] - $first['power_return_low'], 3);
            $energy['power_return_hi'] = round($last['power_return_hi'] - $first['power_return_hi'], 3);
            $energy['gas_usage'] = round($last['gas_usage'] - $first['gas_usage'], 2);
            $energy['date'] = $dateTime->format('Y-m-d');

            // totals for debug purpose
            $powerUsageTotal = round($energy['power_usage_low'] + $energy['power_usage_hi'], 3);
            $powerReturnTotal = round($energy['power_return_low'] + $energy['power_return_hi'], 3);

            //var_dump($powerUsageTotal, $powerReturnTotal, $energy['gas_usage']);

            $energyDay = new EnergyDay();
            $energyDay->exchangeArray($energy);
            var_dump($energyDay);
            // insert
            //$result = $this->getEnergyDayTable()->save($energyDay);

        	$dateTime->add(new \DateInterval('P1D'));
        }

    }*/

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
