<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Nestor;

use JuniWalk\Nestor\Entity\Record;
use Doctrine\ORM\EntityManagerInterface as EntityManager;

class Chronicler
{
	/** @var EntityManager */
	private $entityManager;

	/** @var string */
	private $entityName;


	/**
	 * @param string  $entityName
	 * @param EntityManager  $entityManager
	 */
	public function __construct(
		string $entityName,
		EntityManager $entityManager
	) {
		$this->entityManager = $entityManager;
		$this->entityName = $entityName;
	}


	/**
	 * @return string
	 */
	public function getEntityName(): string
	{
		return $this->entityName;
	}


	/**
	 * @param  Record  $record
	 * @return void
	 */
	public function record(Record $record): void
	{
		// persist
		// flush
	}


	/**
	 * @param  string  $type
	 * @param  string  $event
	 * @param  string|null  $message
	 * @return RecordBuilder
	 */
	public function createRecord(string $type, string $event, string $message = null): RecordBuilder
	{
		return (new RecordBuilder($this))
			->withMessage($message)
			->withEvent($event)
			->withType($type);
	}
}
