<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Nestor\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\Strings;

/**
 * @ORM\MappedSuperclass
 */
abstract class Record
{
	/**
	 * @ORM\Column(type="string", length=16)
	 * @var string
	 */
	protected $type;

	/**
	 * @ORM\Column(type="string", length=64)
	 * @var string
	 */
	protected $event;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string|null
	 */
	protected $message;

	/**
	 * @ORM\Column(type="datetimetz")
	 * @var DateTime
	 */
	protected $date;

	/**
	 * @ORM\Column(type="string", length=16)
	 * @var string
	 */
	protected $flag = 'secondary';

	/**
	 * @ORM\Column(type="boolean")
	 * @var bool
	 */
	protected $isFinished = false;

	/**
	 * @ORM\Column(type="json")
	 * @var string[]
	 */
	protected $params;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 * @var string|null
	 */
	protected $note;


	/**
	 * @param string  $type
	 * @param string  $event
	 */
	final public function __construct(string $type, string $event)
	{
		$this->date = new DateTime;
		$this->event = $event;
		$this->type = $type;
	}


	/**
	 * @param  string  $type
	 * @return void
	 */
	public function setType(string $type): void
	{
		$this->type = $type;
	}


	/**
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}


	/**
	 * @param  string  $event
	 * @return void
	 */
	public function setEvent(string $event): void
	{
		$this->event = $event;
	}


	/**
	 * @return string
	 */
	public function getEvent(): string
	{
		return $this->event;
	}


	/**
	 * @param  string|null  $message
	 * @return void
	 */
	public function setMessage(?string $message): void
	{
		$this->message = $message ?: null;
	}


	/**
	 * @return string|null
	 */
	public function getMessage(): ?string
	{
		return $this->message;
	}


	/**
	 * @return DateTime
	 */
	public function getDate(): DateTime
	{
		return clone $this->date;
	}


	/**
	 * @param  string  $flag
	 * @return void
	 */
	public function setFlag(string $flag): void
	{
		$this->flag = $flag;
	}


	/**
	 * @return string
	 */
	public function getFlag(): string
	{
		return $this->flag;
	}


	/**
	 * @param  bool  $isFinished
	 * @return void
	 */
	public function setFinished(bool $isFinished): void
	{
		$this->isFinished = $isFinished;
	}


	/**
	 * @return bool
	 */
	public function isFinished(): bool
	{
		return $this->isFinished;
	}


	/**
	 * @param  string[]  $params
	 * @return void
	 */
	public function setParams(iterable $params): void
	{
		$this->params = $params;
	}


	/**
	 * @return string[]
	 */
	public function getParams(): iterable
	{
		return $this->params ?: [];
	}


	/**
	 * @param  string|null  $note
	 * @return void
	 */
	public function setNote(?string $note): void
	{
		$this->note = $note ?: null;
	}


	/**
	 * @return string|null
	 */
	public function getNote(): ?string
	{
		return $this->note;
	}
}
