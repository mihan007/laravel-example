<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 30.05.2018
 * Time: 21:25.
 */

use Illuminate\Database\Eloquent\Model;

/**
 * @param $class
 * @param array $attributes
 * @param null $amount
 * @return Model
 */
function create($class, $attributes = [], $amount = null)
{
    return $class::factory()->count($amount)->create($attributes);
}

/**
 * @param $class
 * @param array $attributes
 * @param null $amount
 * @return Model
 */
function make($class, $attributes = [], $amount = null)
{
    return $class::factory()->count($amount)->make($attributes);
}
