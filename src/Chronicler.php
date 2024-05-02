<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Nestor;

use DateTime;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use JuniWalk\Nestor\Entity\Record;
use JuniWalk\Nestor\Entity\RecordRepository;
use JuniWalk\Nestor\Enums\Type;
use JuniWalk\Nestor\Exceptions\RecordExistsException;
use JuniWalk\Nestor\Exceptions\RecordFailedException;
use JuniWalk\Nestor\Exceptions\RecordNotValidException;
use Throwable;

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


	/**
	 * @param array<string, mixed> $params
	 */
	public function log(string $event, string $message, array $params = []): void
	{
		$record = $this->createRecord($event, $message, $params)
			->withType(Type::Log);

		$this->record($record);
	}


	/**
	 * @param array<string, mixed> $params
	 */
	public function todo(string $event, string $message, array $params = []): void
	{
		$record = $this->createRecord($event, $message, $params)
			->withType(Type::Todo);

		$this->record($record);
	}


	/**
	 * @throws RecordExistsException
	 * @throws RecordFailedException
	 */
	public function record(Record|RecordBuilder $record, ?string $period = null): void
	{
		if ($record instanceof RecordBuilder) {
			$record = $record->create();
		}

		if ($period && $this->isRecorded($record, $period)) {
			throw RecordExistsException::fromRecord($record, $period);
		}

		try {
			$this->entityManager->persist($record);
			$this->entityManager->flush($record);	// @phpstan-ignore-line

		} catch (Throwable $e) {
			throw RecordFailedException::fromRecord($record, $e);
		}
	}


	public function isRecorded(Record $record, ?string $period = null): bool
	{
		$result = $this->recordRepository->findOneBy(function($qb) use ($record, $period) {
			$qb->andWhere('e.hash = :hash')->setParameter('hash', $record->getHash());

			if (is_null($period)) {
				return $qb;
			}

			$dateEnd = new DateTime('-'.trim($period, '+-'));
			$dateStart = new DateTime;

			$qb->andWhere('e.date < :dateStart AND e.date > :dateEnd')
				->setParameter('dateStart', $dateStart->setTime(23, 59, 59))
				->setParameter('dateEnd', $dateEnd->setTime(0, 0, 0));
		});

		return $result instanceof Record;
	}


	/**
	 * @param array<string, mixed> $params
	 */
	public function createRecord(string $event, string $message, array $params = []): RecordBuilder
	{
		return (new RecordBuilder($this))
			->withMessage($message)
			->withEvent($event)
			->withParams($params);
	}
}
