<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Tests\Files;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Tools\ResolveTargetEntityListener;
use Doctrine\ORM\Tools\SchemaTool;
use JuniWalk\Nestor\Chronicler;
use JuniWalk\Nestor\Entity\Record;
use Nette\Security\IIdentity;
use Ramsey\Uuid\Doctrine\UuidType;

/**
 * File based SQLite is used because Chronicler opens its own connection
 * and in-memory database is private to each connection.
 */
final class TestDatabase
{
	public readonly EntityManager $entityManager;
	public readonly Chronicler $chronicler;
	private string $file;

	/**
	 * @param class-string<Record> $recordClass
	 * @param class-string<IIdentity> $identityClass
	 */
	public function __construct(
		public readonly string $recordClass = TestRecord::class,
		public readonly string $identityClass = TestIdentity::class,
	) {
		if (!Type::hasType('uuid')) {
			Type::addType('uuid', UuidType::class);
		}

		$this->file = tempnam(sys_get_temp_dir(), 'nestor');

		$config = new Configuration;
		$config->setMetadataDriverImpl(new AttributeDriver([__DIR__]));
		$config->setProxyDir(sys_get_temp_dir());
		$config->setProxyNamespace('NestorTestProxies');
		$config->enableNativeLazyObjects(true);

		$connection = DriverManager::getConnection([
			'driver' => 'pdo_sqlite',
			'path' => $this->file,
		], $config);

		$resolver = new ResolveTargetEntityListener;
		$resolver->addResolveTargetEntity(IIdentity::class, $this->identityClass, []);

		$this->entityManager = new EntityManager($connection, $config);
		$this->entityManager->getEventManager()->addEventListener(Events::loadClassMetadata, $resolver);

		$tool = new SchemaTool($this->entityManager);
		$tool->createSchema([
			$this->entityManager->getClassMetadata($this->recordClass),
			$this->entityManager->getClassMetadata($this->identityClass),
		]);

		$this->chronicler = new Chronicler($this->recordClass, $this->entityManager);
	}


	public function __destruct()
	{
		$this->entityManager->getConnection()->close();
		@unlink($this->file);
	}


	/**
	 * @return list<Record>
	 */
	public function fetchRecords(): array
	{
		$this->entityManager->clear();
		return $this->entityManager->getRepository($this->recordClass)->findBy([], ['id' => 'ASC']);
	}
}
