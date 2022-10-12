<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Nestor\Exceptions;

use JuniWalk\Nestor\Entity\Record;

final class RecordExistsException extends NestorException
{
	private Record $record;


	public static function fromRecord(Record $record, string $period): static
	{
		$self = new static('Record already exists in period of '.$period);
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
