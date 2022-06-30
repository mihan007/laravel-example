<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 14.08.2018
 * Time: 9:41.
 */

namespace App\Support\Interfaces;

interface GoalCounterInterface
{
    /**
     * Get company id and date from instance
     * It is necessary for creation goal counter for special company and date
     * Should return array
     *  - company_id,
     *  - for_date as date string.
     *
     * @return array
     */
    public function getGoalCounterData() :array;
}
