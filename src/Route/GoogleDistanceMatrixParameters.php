<?php

namespace Osimatic\Route;

class GoogleDistanceMatrixParameters
{
	/** @var TransitTravelMode[] */
	private array $transitModes = [];

	private bool $avoidTolls = false;
	private bool $avoidHighways = false;
	private bool $avoidFerries = false;
	private bool $avoidIndoor = false;

	public function getTransitModes(): array
	{
		return $this->transitModes;
	}

	public function setTransitModes(array $transitModes): void
	{
		$this->transitModes = $transitModes;
	}

	public function isAvoidTolls(): bool
	{
		return $this->avoidTolls;
	}

	public function setAvoidTolls(bool $avoidTolls): void
	{
		$this->avoidTolls = $avoidTolls;
	}

	public function isAvoidHighways(): bool
	{
		return $this->avoidHighways;
	}

	public function setAvoidHighways(bool $avoidHighways): void
	{
		$this->avoidHighways = $avoidHighways;
	}

	public function isAvoidFerries(): bool
	{
		return $this->avoidFerries;
	}

	public function setAvoidFerries(bool $avoidFerries): void
	{
		$this->avoidFerries = $avoidFerries;
	}

	public function isAvoidIndoor(): bool
	{
		return $this->avoidIndoor;
	}

	public function setAvoidIndoor(bool $avoidIndoor): void
	{
		$this->avoidIndoor = $avoidIndoor;
	}

}