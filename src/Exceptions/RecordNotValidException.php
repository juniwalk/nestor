<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Nestor\Exceptions;

use JuniWalk\Nestor\Entity\Record;
use JuniWalk\Nestor\RecordBuilder;

/**
 * @phpstan-import-type RecordStructure from RecordBuilder
 */
final class RecordNotValidException extends NestorException
{
	/**
	 * @param RecordStructure $record
	 */
	public static function fromRecord(string $field, array $record): static
	{
		return new static('Missing field "'.$field.'" in record structure: '.json_encode($record));
	}


	public static function fromMethod(string $method, Record $record): static
	{
		return new static('Method set'.ucfirst($method).' is not available in '.$record::class);
	}
}
