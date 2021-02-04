<?php
namespace js\tools\commons\datetime;

use DateInterval;
use InvalidArgumentException;

class ShortInterval
{
	private const PATTERN = '~^'
	. '(?!$)' // See https://stackoverflow.com/a/24542398/540394 for explanation on this.
	. '(?:(?<year>\d+)y)?'
	. '(?:(?<month>\d+)m)?'
	. '(?:(?<day>\d+)d)?'
	. '(?:(?<hour>\d+)h)?'
	. '(?:(?<minute>\d+)[m|i])?'
	. '(?:(?<second>\d+)s)?'
	. '$~';
	private const DATE_UNITS = ['year' => 'Y', 'month' => 'M', 'day' => 'D'];
	private const TIME_UNITS = ['hour' => 'H', 'minute' => 'M', 'second' => 'S'];
	private const DATE_INTERVAL_PROPERTIES = [
		'year'   => 'y',
		'month'  => 'm',
		'day'    => 'd',
		'hour'   => 'h',
		'minute' => 'i',
		'second' => 's',
	];
	
	/**
	 * @param string $shortInterval A common short notation for intervals, e.g. "1h20m" or "1y4m15d".
	 * @return DateInterval
	 */
	public static function parse(string $shortInterval): DateInterval
	{
		preg_match(self::PATTERN, $shortInterval, $matches);
		
		if (empty($matches))
		{
			throw new InvalidArgumentException('Invalid interval: "' . $shortInterval . '"');
		}
		
		return self::buildInterval($matches);
	}
	
	/**
	 * @param DateInterval $interval
	 * @return string The interval converted into a common short notation, e.g. "1h20m" or "1y4m15d".
	 */
	public static function build(DateInterval $interval): string
	{
		$shortInterval = '';
		$unitMap = [
			'i' => (($interval->d > 0) || ($interval->h > 0)) ? 'm' : 'i',
		];
		
		foreach (self::DATE_INTERVAL_PROPERTIES as $unit => $propertyName)
		{
			if ($interval->$propertyName > 0)
			{
				$shortInterval .= $interval->$propertyName . ($unitMap[$propertyName] ?? $propertyName);
			}
		}
		
		return $shortInterval;
	}
	
	private static function buildInterval(array $matches): DateInterval
	{
		$mapDateUnits = function (array $unitMap) use ($matches): string
		{
			$interval = '';
			
			foreach ($unitMap as $unit => $dateIntervalAlias)
			{
				if (!empty($matches[$unit])) // Could be not set, could be "" because PHP.
				{
					$interval .= $matches[$unit] . $dateIntervalAlias;
				}
			}
			
			return $interval;
		};
		
		$intervalDate = $mapDateUnits(self::DATE_UNITS);
		$intervalTime = $mapDateUnits(self::TIME_UNITS);
		
		$interval = 'P' . ($intervalDate ?: '') . ($intervalTime ? 'T' . $intervalTime : '');
		
		return new DateInterval($interval);
	}
}
