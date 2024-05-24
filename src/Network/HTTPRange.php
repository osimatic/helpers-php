<?php

namespace Osimatic\Helpers\Network;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class HTTPRange
{
	/**
	 * @param Request $request
	 * @param int|null $totalSize
	 * @param LoggerInterface|null $logger
	 * @return array|null
	 */
	public static function getRangeFromRequest(Request $request, ?int $totalSize=null, ?LoggerInterface $logger=null): ?array
	{
		if (empty($range = $request->headers->get('Range'))) {
			return null;
		}

		$rangeData = explode('=', $range);

		if (empty($rangeUnit = $rangeData[0] ?? null)) {
			$logger?->info('No range-unit provided in header Range.');
//			header('HTTP/1.1 416 Requested Range Not Satisfiable');
//			header('Content-Range: bytes */' . filelength); // Required in 416.
			return null;
		}

		if (empty($rangeSet = $rangeData[1] ?? null)) {
			$logger?->info('No range-set provided in header Range.');
//			header('HTTP/1.1 416 Requested Range Not Satisfiable');
//			header('Content-Range: bytes */' . filelength); // Required in 416.
			return null;
		}

		$ranges = [];
		$enteredRanges = explode(',', $rangeSet);
		foreach ($enteredRanges as $range) {
			try {
				$ranges[] = self::parseRange($range, $totalSize);
			} catch (\Exception $e) {
				$logger?->info($e->getMessage());
//				header('HTTP/1.1 416 Requested Range Not Satisfiable');
//				header('Content-Range: bytes */' . filelength); // Required in 416.
			}
		}

		if (empty($ranges)) {
			return null;
		}
		return $ranges[0];
	}

	/**
	 * Parses the given range, returning a 2-tuple where the first value is the
	 * start and the second is the end.
	 * @param string $range The range string to parse.
	 * @param int|null $totalSize The total size of the entity.
	 * @return int[]
	 * @throws \Exception
	 */
	private static function parseRange(string $range, ?int $totalSize=null): array
	{
		$points = explode('-', $range, 2);

		if (!isset($points[1])) {
			// Assume the request is for a single item.
			$points[1] = $points[0];
		}

		$isValidRangeValue = fn (string $value): bool => ctype_digit($value) || $value === '';

		if (!array_filter($points, 'ctype_digit') || array_filter($points, $isValidRangeValue) !== $points) {
			throw new \Exception('Unable to parse range: '.$range);
		}

		$start = $points[0];
		$end = $points[1];

		if ('' === $end && null === $totalSize) {
			throw new \Exception('Unable to parse range: '.$range);
		}

		$end = $end !== '' ? (int) $end : $totalSize - 1;
		if (null !== $totalSize && $end >= $totalSize) {
			$end = $totalSize - 1;
		}

		if ('' === $start) {
			if (null !== $totalSize) {
				// Use the "suffix-byte-range-spec".
				$start = $totalSize - $end;
				$end = $totalSize - 1;
			}
			else {
				$start = 0;
			}
		}
		$start = (int) $start;

		if (null !== $totalSize && $start === $totalSize) {
			throw new \Exception('Unable to satisfy range: '.$range.'; length is zero');
		}

		if (null !== $totalSize && $start > $totalSize) {
			throw new \Exception('Unable to satisfy range: '.$range.'; start ('.$start.') is greater than size ('.$totalSize.')');
		}

		if ($end < $start) {
			throw new \Exception('The end value cannot be less than the start value: '.$range);
		}

		return [$start, $end];
	}
}