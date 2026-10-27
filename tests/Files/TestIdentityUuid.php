<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Tests\Files;

use Doctrine\ORM\Mapping as ORM;
use Nette\Security\IIdentity;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity, ORM\Table(name: 'identity')]
class TestIdentityUuid implements IIdentity
{
	#[ORM\Id, ORM\Column(type: 'uuid')]
	private UuidInterface $id;

	public function __construct()
	{
		$this->id = Uuid::uuid7();
	}


	public function __clone(): void
	{
		unset($this->id);
	}


	public function getId(): string
	{
		return $this->id->toString();
	}

	public function getRoles(): array
	{
		return [];
	}
}
