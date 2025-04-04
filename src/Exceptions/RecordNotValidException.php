<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Nestor\Exceptions;

use JuniWalk\Nestor\Entity\Record;

final class RecordNotValidException extends NestorException
{
	/**
	 * @param array<string, mixed> $package
	 */
	public static function fromRecord(string $field, array $package): static
	{
		return new static('Missing field "'.$field.'" in record structure: '.json_encode($package));
	}


	public static function fromMethod(string $method, Record $record): static
	{
		return new static('Method set'.ucfirst($method).' is not available in '.$record::class);
	}
}
