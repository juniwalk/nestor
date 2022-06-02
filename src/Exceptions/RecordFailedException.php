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
	/** @var Record */
	private $record;


	/**
	 * @param  Record  $record
	 * @param  Throwable  $previous
	 * @return static
	 */
	public static function fromRecord(Record $record, Throwable $previous): self
	{
		$self = new static($previous->getMessage(), $previous->getCode(), $previous);
		$self->record = $record;

		return $self;
	}


	/**
	 * @return Record
	 */
	public function getRecord(): Record
	{
		return $this->record;
	}


	/**
	 * @return string
	 */
	public function createLogFromRecord(): string
	{
		return (string) $this->record;
	}
}
