<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Nestor;

use DateTimeInterface;
use JuniWalk\Nestor\Entity\Record;
use JuniWalk\Nestor\Enums\Type;
use JuniWalk\Nestor\Exceptions\RecordNotValidException;
use JuniWalk\Nestor\Interfaces\ParamsProvider;
use JuniWalk\Nestor\Interfaces\TargetProvider;
use JuniWalk\Utils\Format;
use JuniWalk\Utils\Enums\Casing;
use JuniWalk\Utils\Enums\Color;
use JuniWalk\Utils\Strings;
use Nette\Security\IIdentity as Identity;
use Throwable;

final class RecordBuilder
{
	public const RequiredFields = ['event', 'message'];

	private string $event;
	private string $message;
	private DateTimeInterface $date;
	private Type $type;
	private Color $level;
	private ?string $note;
	private bool $finished;
	private ?object $target;
	private ?Identity $owner;

	/** @var array<string, mixed> */
	private array $params;

	public function __construct(
		private readonly Chronicler $chronicler,
	) {
	}


	/**
	 * @throws RecordNotValidException
	 */
	public function create(): Record
	{
		$entityName = $this->chronicler->getEntityName();
		$package = array_diff_key(get_object_vars($this), [
			'chronicler' => true,
		]);

		foreach (static::RequiredFields as $field) {
			if (isset($package[$field])) {
				continue;
			}

			throw RecordNotValidException::fromRecord($field, $package);
		}

		$record = new $entityName($this->event, $this->message);

		foreach ($package as $key => $value) {
			if (!method_exists($record, 'set'.$key)) {
				throw RecordNotValidException::fromMethod($key, $record);
			}

			$record->{'set'.$key}($value);
		}

		return $record;
	}


	public function record(?string $period = null): void
	{
		$this->chronicler->record($this, $period);
	}


	public function withType(Type $type): static
	{
		$this->type = $type;
		return $this;
	}


	public function withLevel(Color $level): static
	{
		$this->level = $level;
		return $this;
	}


	public function withMessage(string $message): static
	{
		$this->message = $message;
		return $this;
	}


	public function withNote(?string $note): static
	{
		$this->note = $note;
		return $this;
	}


	public function withError(?Throwable $exception, ?bool $isFinished = null): static
	{
		$this->finished ??= $isFinished ?? !isset($exception);

		if (!$exception instanceof Throwable) {
			return $this;
		}

		$className = Format::className($exception, Casing::Pascal);

		$this->note = $className.': '.$exception->getMessage();
		return $this;
	}


	public function withEvent(string $event): static
	{
		$this->event = $event;
		return $this;
	}


	public function withTarget(?object $target): static
	{
		if ($target instanceof ParamsProvider) {
			$this->withParams($target->getRecordParams());
		}

		if ($target instanceof TargetProvider) {
			$target = $target->getRecordTarget() ?? $target;
		}

		$this->target = $target;
		return $this;
	}


	public function withDate(DateTimeInterface $date): static
	{
		$this->date = $date;
		return $this;
	}


	public function withOwner(?Identity $owner): static
	{
		$this->owner = $owner;
		return $this;
	}


	public function withFinished(bool $isFinished): static
	{
		$this->finished = $isFinished;
		return $this;
	}


	/**
	 * @param array<string, mixed> $params
	 */
	public function withParams(array $params): static
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

		$this->params ??= [];
		$this->params += $params;

		return $this;
	}


	public function withParam(string $name, mixed $value): static
	{
		$this->params[$name] = $value;
		return $this;
	}


	public function clearParams(): static
	{
		unset($this->params);
		return $this;
	}
}
