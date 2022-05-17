<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Nestor\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JuniWalk\Nestor\Utils\Arrays;
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
	protected $type = 'log';

	/**
	 * @ORM\Column(type="string", length=64)
	 * @var string
	 */
	protected $event;

	/**
	 * @ORM\Column(type="string")
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
	protected $level = 'secondary';

	/**
	 * @ORM\Column(type="boolean")
	 * @var bool
	 */
	protected $isFinished = false;

	/**
	 * @ORM\Column(type="json", nullable=true)
	 * @var string[]
	 */
	protected $params;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 * @var string|null
	 */
	protected $note;


	/**
	 * @param string  $event
	 * @param string  $message
	 */
	final public function __construct(string $event, string $message)
	{
		$this->date = new DateTime;
		$this->message = $message;
		$this->event = $event;
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
	 * @param  string  $message
	 * @return void
	 */
	public function setMessage(string $message): void
	{
		$this->message = $message;
	}


	/**
	 * @return string
	 */
	public function getMessage(): string
	{
		return $this->message;
	}


	/**
	 * @return string
	 */
	public function getMessageFormatted(): string
	{
		$replace = Arrays::flatten($this->params);
		$replace = Arrays::tokenize($replace);
		return strtr($this->message, $replace);
	}


	/**
	 * @return DateTime
	 */
	public function getDate(): DateTime
	{
		return clone $this->date;
	}


	/**
	 * @param  string  $level
	 * @return void
	 */
	public function setLevel(string $level): void
	{
		$this->level = $level;
	}


	/**
	 * @return string
	 */
	public function getLevel(): string
	{
		return $this->level;
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
	 * @return bool
	 */
	public function isFinishable(): bool
	{
		return $this->type == 'todo' && !$this->isFinished;
	}


	/**
	 * @param  string[]  $params
	 * @return void
	 */
	public function setParams(iterable $params): void
	{
		$this->params = $params ?: null;
	}


	/**
	 * @return string[]
	 */
	public function getParams(): iterable
	{
		return $this->params ?: [];
	}


	/**
	 * @return string[]
	 */
	public function getParamsUnified(): iterable
	{
		return Arrays::flatten($this->params);
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
