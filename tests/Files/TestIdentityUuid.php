<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Tests\Files;

use Doctrine\ORM\Mapping as ORM;
use Nette\Security\IIdentity;
use Ramsey\Uuid\Doctrine\UuidV7Generator;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity, ORM\Table(name: 'identity')]
class TestIdentityUuid implements IIdentity
{
	#[ORM\Id, ORM\GeneratedValue(strategy: 'CUSTOM'), ORM\CustomIdGenerator(class: UuidV7Generator::class)]
	#[ORM\Column(type: 'uuid')]
	private UuidInterface $id;

	public function getId(): UuidInterface
	{
		return $this->id;
	}

	public function getRoles(): array
	{
		return [];
	}
}
