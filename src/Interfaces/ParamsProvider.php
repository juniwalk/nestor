<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\Nestor\Interfaces;

use JuniWalk\Nestor\Enums\Action;

interface ParamsProvider
{
	/**
	 * @return array<string, mixed>
	 */
	public function getRecordParams(?Action $action = null): array;
}
