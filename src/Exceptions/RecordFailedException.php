<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Nestor\Exceptions;

use JuniWalk\Nestor\Entity\Record;
use Throwable;

final class RecordFailedException extends NestorException
{
	private Record $record;


	public static function fromRecord(Record $record, Throwable $previous): static
	{
		$self = new static($previous->getMessage(), $previous->getCode(), $previous);
		$self->record = $record;

		return $self;
	}


	public function getRecord(): Record
	{
		return $this->record;
	}


	public function createLogFromRecord(): string
	{
		return (string) $this->record;
	}
}
