<?php

namespace Akhela\WebsiteCarbonCalculator;

use Exception;
use Akhela\WebsiteCarbonCalculator\Traits\HelpersTraits;
use Akhela\WebsiteCarbonCalculator\Traits\UrlTraits;

use Sabre\Uri\InvalidUriException;


class WebsiteCarbonCalculator {

	use HelpersTraits;
	use UrlTraits;

	const KWG_PER_GB = 1.805;
	const RETURNING_VISITOR_PERCENTAGE = 0.75;
	const FIRST_TIME_VIEWING_PERCENTAGE = 0.25;
	const PERCENTAGE_OF_DATA_LOADED_ON_SUBSEQUENT_LOAD = 0.02;
	const CARBON_PER_KWG_GRID = 475;
	const CARBON_PER_KWG_RENEWABLE = 33.4;
	const PERCENTAGE_OF_ENERGY_IN_DATACENTER = 0.1008;
	const PERCENTAGE_OF_ENERGY_IN_TRANSMISSION_AND_END_USER = 0.8992;
	const CO2_GRAMS_TO_LITRES = 0.5562;

	const PAGESPEED_URL = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';
	const GREENWEB_URL = 'https://api.thegreenwebfoundation.org/greencheck';

	private $pagespeedApiKey;

	public function __construct(string $pagespeedApiKey='')
	{
		$this->pagespeedApiKey = $pagespeedApiKey;
	}

	/**
	 * @param string $url
	 * @param array $options
	 * @return array
	 * @throws InvalidUriException
	 * @throws Exception
	 */
	public function calculateByURL( string $url, array $options=[] ): array
	{
		// Get the URL form the query string
		$url = self::normaliseURL($url);

		// Validate it's a legit URL. The normalise function simply add http if there isn't one as google needs it later on
		if ($url === '' || self::urlHostAndPath($url) === '')
			throw new Exception('Invalid URL');

		$options = array_merge(['pagespeedApiKey'=>$this->pagespeedApiKey], $options);
		$entry = self::getEntry($url, $options);

		if(empty($entry))
			throw new Exception('Something went wrong');

		return $entry;
	}


	/**
	 * @throws Exception
	 */
	public static function isGreenHost(string $url): bool
	{
		// Send all the urls we need to process off together
		$results = self::asyncRequests([
			'greenweb' => [
				'url' => self::GREENWEB_URL.'/' . self::urlHost($url),
			]
		]);

		// Check if the green web foundation gave us an answer
		return property_exists($results['greenweb'], 'green') && (bool)$results['greenweb']->green;
	}


	/**
	 * @param string $url
	 * @param array $options
	 * @return object
	 * @throws InvalidUriException
	 */
	public static function getLighthouseData(string $url, array $options=[]): object
	{
		// Setup the default parameters required for Google
		$pageSpeedParameters = [
			'url' => self::normaliseURL($url)
		];

		// Add the Google page speed api key if it exists
		if( $pagespeedApiKey = $options['pagespeedApiKey']??false )
			$pageSpeedParameters['key'] = $pagespeedApiKey;

		// Send all the urls we need to process off together
		$results = self::asyncRequests([
			'pagespeedapi' => [
				'url' => self::PAGESPEED_URL.'?' . http_build_query($pageSpeedParameters),
			]
		]);

		// If google page speed api didnt work
		if(empty($results['pagespeedapi']->lighthouseResult->audits->{'network-requests'}->details->items))
			throw new Exception('Google page speed API results is empty');

		return $results['pagespeedapi'];
	}


	/**
	 * @param string $url
	 * @param array $options
	 * @return array
	 * @throws InvalidUriException
	 */
	private static function getEntry(string $url, array $options=[]): array
	{
		$entry = self::makeEntry($url, $options);

		if(empty($entry))
			return [];

		$statistics = self::computeStatistics($entry['bytesTransferred'], $entry['isGreenHost']);

		return array_merge($entry, $statistics);
	}

	/**
	 * @param string $url
	 * @param array $options
	 * @return array
	 * @throws InvalidUriException|Exception
	 */
	private static function makeEntry(string $url, array $options=[]): array
	{
		$lighthouseData = self::getLighthouseData($url, $options);

		if( !isset($options['isGreenHost']) )
			$isGreenHost = self::isGreenHost($url);
		else
			$isGreenHost = $options['isGreenHost']??false;

		// If google page speed api didnt work
		if(empty($lighthouseData->lighthouseResult->audits->{'network-requests'}->details->items))
			throw new Exception('Google page speed API results is empty');

		// Calc the transfer size
		$bytesTransfered = self::calculateTransferedBytes(
			$lighthouseData->lighthouseResult->audits->{'network-requests'}->details->items
		);

        $count = $performanceScore = 0;
        foreach ($lighthouseData->lighthouseResult->audits as $audit){

            if( is_numeric($audit->score) )
            {

                $performanceScore += $audit->score;
                $count ++;
            }
        }

        $performanceScore = $performanceScore/$count;

		// This returns a sanitised version of the entry
		return [
			'url'   => $url,
            'isGreenHost' => $isGreenHost,
            'bytesTransferred' => $bytesTransfered,
			'networkRequests' => count($lighthouseData->lighthouseResult->audits->{'network-requests'}->details->items),
            'domSize' => $lighthouseData->lighthouseResult->audits->{'dom-size'}->numericValue,
            'performanceScore' => round($performanceScore*100)/100,
			'loadingExperience' => $lighthouseData->loadingExperience->overall_category,
			'speedIndex' => round($lighthouseData->lighthouseResult->audits->{'speed-index'}->numericValue),
			'firstMeaningfulPaint' => round($lighthouseData->lighthouseResult->audits->{'first-meaningful-paint'}->numericValue),
			'interactive' => round($lighthouseData->lighthouseResult->audits->{'interactive'}->numericValue),
			'bootupTime' => round($lighthouseData->lighthouseResult->audits->{'bootup-time'}->numericValue),
			'serverResponseTime' => round($lighthouseData->lighthouseResult->audits->{'server-response-time'}->numericValue),
			'mainthreadWork' => round($lighthouseData->lighthouseResult->audits->{'mainthread-work-breakdown'}->numericValue)
		];
	}

	// -----------------------------------------------------------------------------------------------------------------
	// -----------------------------------------------------------------------------------------------------------------
	// Helper functions for calculating emissions
	// -----------------------------------------------------------------------------------------------------------------
	// -----------------------------------------------------------------------------------------------------------------
	/**
	 * @param string $bytes
	 * @param bool $green
	 * @return array
	 */
	public static function computeStatistics(string $bytes, bool $green): array
	{
		$bytesAdjusted = self::adjustDataTransfer($bytes);
		$energy = self::energyConsumption($bytesAdjusted);
		$co2 = $green ?  self::getCo2Renewable($energy) : self::getCo2Grid($energy);

		return [
			'energy' => $energy,
			'co2PerPageview' => $co2
		];
	}

	/**
	 * @param int $val
	 * @return int
	 */
	public static function adjustDataTransfer(int $val): int
	{
		return ($val * self::RETURNING_VISITOR_PERCENTAGE) + (self::PERCENTAGE_OF_DATA_LOADED_ON_SUBSEQUENT_LOAD * $val * self::FIRST_TIME_VIEWING_PERCENTAGE);
	}

	/**
	 * Convert bytes to KWH
	 * @param int $bytes
	 * @return float
	 */
	public static function energyConsumption(int $bytes): float
	{
		return $bytes * (self::KWG_PER_GB / 1073741824);
	}

	/**
	 * Get C02 with classic server electricity
	 * @param float $energy
	 * @return float
	 */
	public static function getCo2Grid(float $energy): float
	{
		return $energy * self::CARBON_PER_KWG_GRID;
	}

	/**
	 * Get C02 with renewable server electricity
	 * @param float $energy
	 * @return float
	 */
	public static function getCo2Renewable(float $energy): float
	{
		return (($energy * self::PERCENTAGE_OF_ENERGY_IN_DATACENTER) * self::CARBON_PER_KWG_RENEWABLE) + (($energy * self::PERCENTAGE_OF_ENERGY_IN_TRANSMISSION_AND_END_USER) * self::CARBON_PER_KWG_GRID);
	}

	/**
	 * Convert C02 grams to litres
	 * @param float $co2
	 * @return float
	 */
	public static function co2ToLitres(float $co2): float
	{
		return $co2 * self::CO2_GRAMS_TO_LITRES;
	}
}
