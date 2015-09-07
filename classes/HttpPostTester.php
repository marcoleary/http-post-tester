<?php

namespace HttpPostTester;

class HttpPostTester
{
	protected $strPathToData = __DIR__ . '/../data/';

	public function __construct()
	{
		
	}

	public function init()
	{
		$resHandle = opendir($this->strPathToData);

		while (false !== ($strFile = readdir($resHandle))) {
			if (in_array($strFile, ['.', '..'])) {
				continue;
			}

			if ('csv' !== pathinfo($strFile, PATHINFO_EXTENSION)) {
				continue;
			}

			if (false !== ($resFileHandle = fopen($this->strPathToData, 'r'))) {
				while (false !== ($arrRow = fgetcsv($resFileHandle))) {
					$this->doHttpPost($arrRow[0]);
				}

				fclose($handle);
			}
		}
	}

	public function doHttpPost($strUrl)
	{
		// $objGuzzle = new Guzzle\Guzzle();

		
	}
}
