<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Nestor;

use JuniWalk\Nestor\Entity\Record;
use JuniWalk\Nestor\Exceptions\RecordNotValidException;
use Nette\Utils\Strings;

final class RecordBuilder
{
	/** @var Chronicler */
	private $chronicler;

	/** @var string[] */
	private $record;


	/**
	 * @param Chronicler  $chronicler
	 */
	public function __construct(Chronicler $chronicler)
	{
		$this->chronicler = $chronicler;
	}


	/**
	 * @return Record
	 * @throws RecordNotValidException
	 */
	public function create(): Record
	{
		$entityName = $this->chronicler->getEntityName();
		$record = new $entityName(
			$this->record['event'],
			$this->record['message']
		);

		foreach ($this->record as $key => $value) {
			if (!method_exists($record, 'set'.$key)) {
				throw new RecordNotValidException;
			}

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
		$this->record['type'] = $type;
		return $this;
	}


	/**
	 * @param  string  $level
	 * @return static
	 */
	public function withLevel(string $level): self
	{
		$this->record['level'] = $level;
		return $this;
	}


	/**
	 * @param  string  $message
	 * @return static
	 */
	public function withMessage(string $message): self
	{
		$this->record['message'] = $message;
		return $this;
	}


	/**
	 * @param  string|null  $event
	 * @return static
	 */
	public function withEvent(?string $event): self
	{
		$this->record['event'] = $event;
		return $this;
	}


	/**
	 * @param  mixed[]  $params
	 * @return static
	 */
	public function withParams(iterable $params): self
	{
		foreach ($params as $key => $value) {
			if (!$matches = Strings::match($key, '/record\.(\w+)/i')) {
				continue;
			}

			if (!method_exists($this, 'with'.$matches[1])) {
				continue;
			}

			$this->{'with'.$matches[1]}($value);
			unset($params[$key]);
		}

		$this->record['params'] = $params;
		return $this;
	}


	/**
	 * @param  string  $name
	 * @param  mixed  $value
	 * @return static
	 */
	public function withParam(string $name, $value): self
	{
		$this->record['params'][$name] = $value;
		return $this;
	}
}
