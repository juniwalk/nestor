<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Nestor;

use JuniWalk\Nestor\Entity\Record;
use JuniWalk\Nestor\Exceptions\RecordFailedException;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Doctrine\ORM\ORMException;

final class Chronicler
{
	/** @var EntityManager */
	private $entityManager;

	/** @var string */
	private $entityName;


	/**
	 * @param  string  $entityName
	 * @param  EntityManager  $entityManager
	 * @throws RecordNotValidException
	 */
	public function __construct(
		string $entityName,
		EntityManager $entityManager
	) {
		if (!is_subclass_of($entityName, Record::class)) {
			throw new RecordNotValidException;
		}

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
	 * @param  string  $event
	 * @param  string  $message
	 * @param  mixed[]  $params
	 * @return void
	 */
	public function log(string $event, string $message, iterable $params = []): void
	{
		$log = new $this->entityName('log', $event, $message);
		$log->setParams($params);

		$this->record($log);
	}


	/**
	 * @param  Record  $record
	 * @return void
	 * @throws RecordFailedException
	 */
	public function record(Record $record): void
	{
		try {
			$this->entityManager->persist($record);
			$this->entityManager->flush($record);

		} catch (DBALException|ORMException $e) {
			throw RecordFailedException::from($e);
		}

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
