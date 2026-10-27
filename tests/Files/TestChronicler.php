<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Tests\Files;

use JuniWalk\Nestor\Chronicler;
use ReflectionClass;

final class TestChronicler
{
	/**
	 * Chronicler without database connection, only entity name is available.
	 */
	public static function create(string $entityName = TestRecord::class): Chronicler
	{
		$reflection = new ReflectionClass(Chronicler::class);
		$chronicler = $reflection->newInstanceWithoutConstructor();
		$reflection->getProperty('entityName')->setValue($chronicler, $entityName);

		return $chronicler;
	}
}
