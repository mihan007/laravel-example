<?php
/**
 * Created by PhpStorm.
 * User: gesparo
 * Date: 19.09.2018
 * Time: 13:57.
 */

namespace App\Domain\Beget;

use Ixudra\Curl\Facades\Curl;

class BackupApi extends Api
{
    private $login;
    private $password;

    private $baseUrl = 'https://api.beget.com/api/';

    public function __construct(string $login, string $password)
    {
        $this->login = $login;
        $this->password = $password;
    }

    public function downloadMysql(array $databases, $backupId = '')
    {
        $url = $this->baseUrl;
        $url .= 'backup/downloadMysql'."?login={$this->login}&passwd={$this->password}&input_format=json&output_format=json&input_data=";
        $url .= urlencode(json_encode(['bases' => $databases]));

        $response = Curl::to($url)
            ->asJsonResponse(true)
            ->post();

        return $response;
    }
}
