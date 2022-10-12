<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Nestor;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Doctrine\ORM\ORMException;
use JuniWalk\Nestor\Entity\Record;
use JuniWalk\Nestor\Entity\RecordRepository;
use JuniWalk\Nestor\Enums\Type;
use JuniWalk\Nestor\Exceptions\RecordFailedException;
use JuniWalk\Nestor\Exceptions\RecordNotValidException;
use JuniWalk\Utils\Strings;

final class Chronicler
{
	/**
	 * @throws RecordNotValidException
	 */
	public function __construct(
		private readonly string $entityName,
		private readonly EntityManager $entityManager,
		private readonly RecordRepository $recordRepository,
	) {
		if (!is_subclass_of($entityName, Record::class)) {
			throw new RecordNotValidException;
		}
	}


	public function getEntityName(): string
	{
		return $this->entityName;
	}


	public function log(string $event, string $message, iterable $params = []): void
	{
		$record = $this->createRecord($event, $message, $params)
			->withType(Type::Log);

		$this->record($record->create());
	}


	public function todo(string $event, string $message, iterable $params = []): void
	{
		$record = $this->createRecord($event, $message, $params)
			->withType(Type::Todo);

		$this->record($record->create());
	}


	/**
	 * @throws RecordFailedException
	 */
	public function record(Record $record): void
	{
		try {
			$this->entityManager->persist($record);
			$this->entityManager->flush($record);

		} catch (DBALException|ORMException $e) {
			throw RecordFailedException::fromRecord($record, $e);
		}
	}


	public function isRecorded(Record $record, string $period = null): bool
	{
		$result = $this->recordRepository->findOneBy(function($qb) use ($record, $period) {
			$qb->andWhere('e.hash = :hash')->setParameter('hash', $record->getHash());

			if (is_null($period)) {
				return $qb;
			}

			$dateEnd = new DateTime('-'.Strings::trim($period, '+-'));
			$dateStart = new DateTime;

			$qb->andWhere('e.date BETWEEN :dateStart AND :dateEnd')
				->setParameter('dateStart', $dateStart->setTime(0, 0, 0))
				->setParameter('dateEnd', $dateEnd->setTime(23, 59, 59));
		});

		return $result instanceof Record;
	}


	public function createRecord(string $event, string $message, iterable $params = []): RecordBuilder
	{
		return (new RecordBuilder($this))
			->withMessage($message)
			->withEvent($event)
			->withParams($params);
	}
}
