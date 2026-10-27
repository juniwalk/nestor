<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Tests\Files;

use Doctrine\ORM\Mapping as ORM;
use Nette\Security\IIdentity;

#[ORM\Entity, ORM\Table(name: 'identity')]
class TestIdentity implements IIdentity
{
	#[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
	private int $id;

	public function getId(): int
	{
		return $this->id;
	}

	public function getRoles(): array
	{
		return [];
	}
}
