<?php

namespace HttpPostTester;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Teapot\StatusCode;

class HttpPostTester
{
    // this is the response we expect back from the tested URLs
    const EXPECTED_RESPONSE = 'OK';

    protected $strPathToData = __DIR__ . '/../data/';

    protected $log;

    protected $guzzle;

    public function __construct(Client $guzzle, Logger $log)
    {
        $this->guzzle = $guzzle;
        $this->log = $log;
    }

    public function init()
    {
        $this->log->addDebug('Started!');

        $handle = opendir($this->strPathToData);

        while (false !== ($fileName = readdir($handle))) {
            if (in_array($fileName, ['.', '..']) || 'csv' !== pathinfo($fileName, PATHINFO_EXTENSION)) {
                continue;
            }

            if (false !== ($file = fopen($this->strPathToData . $fileName, 'r'))) {
                while (false !== ($row = fgetcsv($file))) {
                    $this->doHttpPost($row[0]);
                }
                fclose($file);
            }
        }

        $this->log->addDebug('Finished!');
    }

    public function doHttpPost($url)
    {
        $result = $this->guzzle->post($url, [
            RequestOptions::ALLOW_REDIRECTS => true,
            RequestOptions::CONNECT_TIMEOUT =>  5,
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::QUERY => []
        ]);

        if (StatusCode::OK === (int) $result->getStatusCode()) {
            $this->log->addDebug(sprintf('[Result_OK] 200 OK from %s', $url), [
                'body' => (string) $result->getBody()
            ]);

            if (0 !== stripos((string) $result->getBody(), self::EXPECTED_RESPONSE)) {
                $this->log->addWarning(sprintf('[Result_WARN] %d but not OK from %s', $result->getStatusCode(), $url), [
                    'body' => (string) $result->getBody()
                ]);
            }
        } else {
            $this->log->addCritical(sprintf('[Result_CRIT] %d from %s', $result->getStatusCode(), $url), [
                'body' => (string) $result->getBody()
            ]);
        }
    }
}
