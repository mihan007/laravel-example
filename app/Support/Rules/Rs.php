<?php

namespace App\Support\Rules;

use Illuminate\Contracts\Validation\Rule;

class Rs implements Rule
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
        $rs = (string) $value;
        $result = false;
        if (! $rs) {
            $error_code = 1;
            $this->errorMessage = 'Р/С пуст';
        } elseif (preg_match('/[^0-9]/', $rs)) {
            $error_code = 2;
            $this->errorMessage = 'Р/С может состоять только из цифр';
        } elseif (strlen($rs) !== 20) {
            $error_code = 3;
            $this->errorMessage = 'Р/С может состоять только из 20 цифр';
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
