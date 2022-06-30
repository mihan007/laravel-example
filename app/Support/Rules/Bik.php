<?php

namespace App\Support\Rules;

use Illuminate\Contracts\Validation\Rule;

class Bik implements Rule
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
        $bik = (string) $value;
        $result = false;
        if (! $bik) {
            $error_code = 1;
            $this->errorMessage = 'БИК пуст';
        } elseif (preg_match('/[^0-9]/', $bik)) {
            $error_code = 2;
            $this->errorMessage = 'БИК может состоять только из цифр';
        } elseif (strlen($bik) !== 9) {
            $error_code = 3;
            $this->errorMessage = 'БИК может состоять только из 9 цифр';
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
