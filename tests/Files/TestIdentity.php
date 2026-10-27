<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Tests\Files;

use Doctrine\ORM\Mapping as ORM;
use JuniWalk\ORM\Entity\Interfaces\Identified;
use JuniWalk\ORM\Entity\Traits\IdentifierIdentity;
use Nette\Security\IIdentity;

#[ORM\Entity, ORM\Table(name: 'identity')]
class TestIdentity implements IIdentity, Identified
{
	use IdentifierIdentity;


	public function getRoles(): array
	{
		return [];
	}
}
