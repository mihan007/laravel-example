<?php

namespace App\Support\Rules;

use Illuminate\Contracts\Validation\Rule;

class Inn implements Rule
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
        $inn = $value;
        $result = false;
        $inn = (string) $inn;
        if (preg_match('/[^0-9]/', $inn)) {
            $this->errorMessage = 'ИНН может состоять только из цифр';
        } elseif (! in_array($inn_length = strlen($inn), [10, 12])) {
            $this->errorMessage = 'ИНН может состоять только из 10 или 12 цифр';
        } else {
            $check_digit = function ($inn, $coefficients) {
                $n = 0;
                foreach ($coefficients as $i => $k) {
                    $n += $k * (int) $inn[$i];
                }

                return $n % 11 % 10;
            };
            switch ($inn_length) {
                case 10:
                    $n10 = $check_digit($inn, [2, 4, 10, 3, 5, 9, 4, 6, 8]);
                    if ($n10 === (int) $inn[9]) {
                        $result = true;
                    }
                    break;
                case 12:
                    $n11 = $check_digit($inn, [7, 2, 4, 10, 3, 5, 9, 4, 6, 8]);
                    $n12 = $check_digit($inn, [3, 7, 2, 4, 10, 3, 5, 9, 4, 6, 8]);
                    if (($n11 === (int) $inn[10]) && ($n12 === (int) $inn[11])) {
                        $result = true;
                    }
                    break;
            }
            if (! $result) {
                $this->errorMessage = 'Неправильный ИНН';
            }
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
