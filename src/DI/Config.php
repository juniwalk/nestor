<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Nestor\DI;

class Config
{
	/** @var class-string */
	public string $entityName;

	/** @var non-empty-string */
	public string $messageFormat = '{className}-{action}';

	public bool $watchActivity = false;
}
