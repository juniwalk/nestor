<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\Nestor\Interfaces;

interface ParamsProvider
{
	public function getRecordParams(): array;
}
