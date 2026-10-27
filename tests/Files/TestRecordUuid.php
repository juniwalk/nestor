<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2026
 * @license   MIT License
 */

namespace JuniWalk\Tests\Files;

use Doctrine\ORM\Mapping as ORM;
use JuniWalk\Nestor\Entity\Record;
use JuniWalk\ORM\Entity\Traits\IdentifierUUIDv7;
use Nette\Application\UI\Control;
use Nette\Application\UI\Link;

#[ORM\Entity, ORM\HasLifecycleCallbacks, ORM\Table(name: 'record')]
class TestRecordUuid extends Record
{
	use IdentifierUUIDv7;

	public function createLink(Control $control): string|Link|null
	{
		return null;
	}
}
