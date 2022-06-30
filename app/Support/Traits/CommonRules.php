<?php


namespace App\Support\Traits;

trait CommonRules
{
    public function rules()
    {
        return self::commonRules(request());
    }

    public static function liveWireRules($component)
    {
        return self::commonRules($component);
    }
}
