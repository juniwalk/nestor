<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Nestor\Exceptions;

use JuniWalk\Nestor\Entity\Record;
use JuniWalk\Nestor\RecordBuilder;

final class RecordNotValidException extends NestorException
{
	public static function fromRecord(string $field, RecordBuilder $record): static
	{
		return new static('Missing field "'.$field.'" in record structure: '.json_encode($record));
	}


	public static function fromMethod(string $method, Record $record): static
	{
		return new static('Method set'.ucfirst($method).' is not available in '.$record::class);
	}
}
