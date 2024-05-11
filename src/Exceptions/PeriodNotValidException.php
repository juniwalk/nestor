<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Nestor\Exceptions;

final class PeriodNotValidException extends NestorException
{
	public static function fromPeriod(string $period): static
	{
		return new static('Given period should modify date into past, "'.$period.'" given.');
	}
}
