<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 10.09.2018
 * Time: 14:16.
 */

namespace App\Support\Helper;

use Illuminate\Contracts\Support\Arrayable;

class SessionReportMessage implements Arrayable
{
    /**
     * @var string
     */
    private $status;
    /**
     * @var string
     */
    private $text;

    public function __construct(SessionReportStatus $status, string $text)
    {
        $this->status = $status;
        $this->text = $text;
    }

    public function getReportVariableName()
    {
        return 'message';
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return ['status' => (string) $this->status, 'text' => $this->text];
    }
}
