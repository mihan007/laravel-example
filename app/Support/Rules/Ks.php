<?php

namespace App\Support\Rules;

use Illuminate\Contracts\Validation\Rule;

class Ks implements Rule
{
    private $errorMessage;

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $ks = (string) $value;
        $result = false;
        if (! $ks) {
            $error_code = 1;
            $this->errorMessage = 'К/С пуст';
        } elseif (preg_match('/[^0-9]/', $ks)) {
            $error_code = 2;
            $this->errorMessage = 'К/С может состоять только из цифр';
        } elseif (strlen($ks) !== 20) {
            $error_code = 3;
            $this->errorMessage = 'К/С может состоять только из 20 цифр';
        } else {
            $result = true;
        }

        return $result;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->errorMessage;
    }
}
