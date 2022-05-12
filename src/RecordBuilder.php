<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Nestor;

use JuniWalk\Nestor\Entity\Record;

final class RecordBuilder
{
	/** @var Chronicler */
	private $chronicler;

	/** @var string[] */
	private $data;


	/**
	 * @param Chronicler  $chronicler
	 */
	public function __construct(Chronicler $chronicler)
	{
		$this->chronicler = $chronicler;
	}


	/**
	 * @return Record
	 */
	public function create(): Record
	{
		$entity = $this->chronicler->getEntityName();
		$record = new $entity($this->data['level'], $this->data['message']);

		foreach ($this->data as $key => $value) {
			$record->{'set'.$key}($value);
		}

		return $record;
	}


	/**
	 * @return void
	 */
	public function record(): void
	{
		$this->chronicler->record($this->create());
	}


	/**
	 * @param  string  $type
	 * @return static
	 */
	public function withType(string $type): self
	{
		$this->data['type'] = $type;
		return $this;
	}


	/**
	 * @param  string  $level
	 * @return static
	 */
	public function withLevel(string $level): self
	{
		$this->data['level'] = $level;
		return $this;
	}


	/**
	 * @param  string  $message
	 * @return static
	 */
	public function withMessage(string $message): self
	{
		$this->data['message'] = $message;
		return $this;
	}


	/**
	 * @param  string|null  $event
	 * @return static
	 */
	public function withEvent(?string $event): self
	{
		$this->data['event'] = $event;
		return $this;
	}


	/**
	 * @param  string  $name
	 * @param  mixed  $value
	 * @return static
	 */
	public function withParam(string $name, $value): self
	{
		$this->data['params'][$name] = $value;
		return $this;
	}
}
