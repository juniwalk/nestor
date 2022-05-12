<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Nestor\Exceptions;

use Throwable;

final class RecordFailedException extends NestorException
{
	/**
	 * @param  Throwable  $e
	 * @param  string|null  $message
	 * @return static
	 */
	public static function from(Throwable $e, string $message = null): self
	{
		return new static($message ?: $e->getMessage(), $e->getCode(), $e);
	}
}
