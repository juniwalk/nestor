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
		$record = new $entity($this->data['type'], $this->data['event']);

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
	 * @param  string  $event
	 * @return static
	 */
	public function withEvent(string $event): self
	{
		$this->data['event'] = $event;
		return $this;
	}


	/**
	 * @param  string|null  $message
	 * @return static
	 */
	public function withMessage(?string $message): self
	{
		$this->data['message'] = $message;
		return $this;
	}


	/**
	 * @param  string  $flag
	 * @return static
	 */
	public function withFlag(string $flag): self
	{
		$this->data['flag'] = $flag;
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
