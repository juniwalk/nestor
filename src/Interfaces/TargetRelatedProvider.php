<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Nestor\Interfaces;

use JuniWalk\Nestor\Entity\Record;

interface TargetRelatedProvider
{
	/**
	 * @return Record[]
	 */
	public function getRecordRelated(): array;
}
