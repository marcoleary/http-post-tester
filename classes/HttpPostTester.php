<?php

namespace HttpPostTester;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use GuzzleHttp;
use Teapot\StatusCode;

class HttpPostTester
{
	const EXPECTED_RESPONSE = 'OK';
	
	protected $strPathToData = __DIR__ . '/../data/';

	protected $objLogger;

	public function __construct()
	{
		date_default_timezone_set('Europe/London');

		$this->objLogger = new Logger(__CLASS__);
        	$this->objLogger->pushHandler(new StreamHandler(sprintf('%s/../logs/results_%s.log', __DIR__, date('YmdHis')), Logger::WARNING));
        	$this->objLogger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));
	}

	public function init()
	{
		$this->objLogger->addDebug('Started!');

		$resHandle = opendir($this->strPathToData);

		while (false !== ($strFile = readdir($resHandle))) {
			if (in_array($strFile, ['.', '..'])) {
				continue;
			}

			if ('csv' !== pathinfo($strFile, PATHINFO_EXTENSION)) {
				continue;
			}

			if (false !== ($resFileHandle = fopen($this->strPathToData . $strFile, 'r'))) {
				while (false !== ($arrRow = fgetcsv($resFileHandle))) {
					$this->doHttpPost($arrRow[0]);
				}
				fclose($resFileHandle);
			}
		}

		$this->objLogger->addDebug('Finished!');
	}

	public function doHttpPost($strUrl)
	{
		$objHttp = new GuzzleHttp\Client();

        	$objResult = $objHttp->post($strUrl, [
            		GuzzleHttp\RequestOptions::ALLOW_REDIRECTS => true,
            		GuzzleHttp\RequestOptions::CONNECT_TIMEOUT =>  5,
            		GuzzleHttp\RequestOptions::HTTP_ERRORS => false,
            		GuzzleHttp\RequestOptions::QUERY => []
        	]);

        	if (StatusCode::OK === (int) $objResult->getStatusCode()) {
            		$this->objLogger->addDebug(sprintf('[Result_OK] 200 OK from %s', $strUrl), [
				'body' => (string) $objResult->getBody()
			]);

			if (0 !== stripos((string) $objResult->getBody(), self::EXPECTED_RESPONSE)) {
				$this->objLogger->addWarning(sprintf('[Result_WARN] %d but not OK from %s', $objResult->getStatusCode(), $strUrl), [
					'body' => (string) $objResult->getBody()
				]);
			}
        	} else {
			$this->objLogger->addCritical(sprintf('[Result_CRIT] %d from %s', $objResult->getStatusCode(), $strUrl), [
				'body' => (string) $objResult->getBody()
			]);
		}
	}
}
