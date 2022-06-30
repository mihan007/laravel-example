<?php

namespace App\Domain\Beget;

use Illuminate\Support\Facades\Config;

/**
 * Created by PhpStorm.
 * User: Gesparo
 * Date: 01.06.2017
 * Time: 12:55.
 */
class DatabaseApi
{
    /**
     * Username for connecting to beget server.
     *
     * @var string
     */
    protected $username = '';

    /**
     * Password for connecting to beget server.
     *
     * @var string
     */
    protected $password = '';

    /**
     * Url to beget api server.
     *
     * @var string
     */
    protected $apiUrl = 'https://api.beget.ru/api/mysql/';

    /**
     * DatabaseApi constructor.
     */
    public function __construct()
    {
        $this->username = Config::get('beget.username');
        $this->password = Config::get('beget.password');
    }

    /**
     * Get list of all databases.
     *
     * @return array
     */
    public function getList()
    {
        $url = "{$this->apiUrl}getList?login={$this->username}&passwd={$this->password}&output_format=json";

        return json_decode(file_get_contents($url), true);
    }

    /**
     * Add new database.
     *
     * @param $databaseSuffix
     * @param $databasePassword
     * @return mixed
     */
    public function addDb($databaseSuffix, $databasePassword)
    {
        // Add database name must be lover then 17 symbols
        $suffixLimit = 16 - (strlen($this->username) + 1);

        if (strlen($databaseSuffix) > $suffixLimit) {
            return false;
        }

        $inputData = [
            'suffix' => $databaseSuffix,
            'password' => $databasePassword,
        ];

        $url = "{$this->apiUrl}addDb?login={$this->username}&passwd={$this->password}&input_format=json&output_format=json";
        $url .= '&input_data='.urlencode(json_encode($inputData));

        $result = json_decode(file_get_contents($url), true);

        // waiting for real creation database
        if ('success' === $result['status'] || 'success' === $result['answer']['status']) {
            $sleepIterator = 0;
            $sleepLimit = 5; // seconds

            $databaseName = $this->username.'_'.$databaseSuffix;
            $connection = null;

            while ($sleepIterator < $sleepLimit) {
                // trying to connect to created database
                $connection = @mysqli_connect('localhost', $databaseName, $databasePassword);

                // if success
                if ($connection) {
                    break;
                }

                sleep(1);

                $sleepIterator++;
            }

            // if connection not established
            if ($sleepIterator == $sleepLimit && ! $connection) {
                $result['answer']['status'] = 'error';
                $result['answer']['error'] = 'Beget could not create the database';
            }
        }

        return $result;
    }
}
