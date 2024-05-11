<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Nestor\Entity;

use DateTime;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Proxy\Proxy;
use JuniWalk\ORM\Entity\Interfaces\Identified;
use JuniWalk\ORM\Entity\Traits as Tools;
use JuniWalk\Nestor\Enums\Type;
use JuniWalk\Utils\Arrays;
use JuniWalk\Utils\Enums\Color;
use JuniWalk\Utils\Format;
use JuniWalk\Utils\Json;
use Nette\Application\UI\Control;
use Nette\Application\UI\Link;
use Nette\Localization\Translator;
use Stringable;

#[ORM\MappedSuperclass]
abstract class Record implements Identified, Stringable
{
	use Tools\Identifier;
	use Tools\Ownerable;
	use Tools\Parametrized;
	use Tools\Finishable;
	use Tools\Hashable;

	#[ORM\Column(type: 'string', length: 16, enumType: Type::class)]
	protected Type $type = Type::Log;

	#[ORM\Column(type: 'string', length: 64)]
	protected string $event;

	#[ORM\Column(type: 'string')]
	protected string $message;

	/** @var class-string|null $target */
	#[ORM\Column(type: 'string', nullable: true, options: ['default' => null])]
	protected ?string $target = null;

	#[ORM\Column(type: 'json', nullable: true, options: ['default' => null])]
	protected mixed $targetId = null;

	#[ORM\Column(type: 'datetimetz')]
	protected DateTime $date;

	#[ORM\Column(type: 'string', length: 16, enumType: Color::class)]
	protected Color $level = Color::Secondary;

	#[ORM\Column(type: 'text', nullable: true)]
	protected ?string $note = null;


	final public function __construct(string $event, string $message)
	{
		$this->date = new DateTime;
		$this->message = $message;
		$this->event = $event;
	}


	public function __toString(): string
	{
		return strtr("[%type%, %level%] %target%(%targetId%) %event%: %message% (%params%)", [
			'%target%' => $this->getTarget(),
			'%targetId%' => $this->getTargetId(),
			'%type%' => $this->getType()->value,
			'%level%' => $this->getLevel()->value,
			'%event%' => $this->getEvent(),
			'%message%' => $this->getMessageFormatted(),
			'%params%' => Json::encode($this->params),
		]);
	}


	public function setType(Type $type): void
	{
		$this->type = $type;
	}


	public function getType(): Type
	{
		return $this->type;
	}


	public function setEvent(string $event): void
	{
		$this->event = $event;
	}


	public function getEvent(): string
	{
		return $this->event;
	}


	public function setMessage(string $message): void
	{
		$this->message = $message;
	}


	public function getMessage(): string
	{
		return $this->message;
	}


	public function getMessageFormatted(): string
	{
		return Format::tokens($this->message, $this->params);
	}


	public function getMessageTranslated(Translator $translator): Stringable|string
	{
		return $translator->translate($this->message, $this->getParamsUnified());
	}


	public function setTarget(object $target, mixed $targetId = null): void
	{
		if (!$targetId && $target instanceof Identified) {
			$targetId ??= $target->getId();
		}

		$this->target = $target::class;
		$this->targetId = $targetId;

		if ($target instanceof Proxy && $targetParent = get_parent_class($target)) {
			$this->target = $targetParent;
		}
	}


	/**
	 * @return class-string|null
	 */
	public function getTarget(): ?string
	{
		return $this->target;
	}


	public function getTargetId(): mixed
	{
		return $this->targetId;
	}


	public function createTarget(EntityManager $entityManager): ?object
	{
		if (!$this->target || $this->targetId) {
			return null;
		}

		return $entityManager->getReference($this->target, $this->targetId);
	}


	public function setDate(DateTime $date): void
	{
		$this->date = clone $date;
	}


	public function getDate(): DateTime
	{
		return clone $this->date;
	}


	public function setLevel(Color $level): void
	{
		$this->level = $level;
	}


	public function getLevel(): Color
	{
		return $this->level;
	}


	public function isFinishable(): bool
	{
		return $this->type == Type::Todo && !$this->isFinished;
	}


	/**
	 * @param array<string, mixed> $params
	 */
	public function setParams(array $params): void
	{
		$this->params = [];
		$this->addParams($params);
	}


	/**
	 * @param array<string, mixed> $params
	 */
	public function addParams(array $params): void
	{
		$params = Arrays::mapRecursive($params, fn($v) => Format::serializable($v));
		$params = array_filter($params, fn($v) => !is_null($v));
		$params = array_merge($params, $this->params ?? []);

		$this->params = $params;
	}


	/**
	 * @return array<string, mixed>
	 */
	public function getParamsUnified(): array
	{
		return Arrays::flatten($this->params);
	}


	public function setNote(?string $note): void
	{
		$this->note = $note ?: null;
	}


	public function getNote(): ?string
	{
		return $this->note;
	}


	abstract public function createLink(Control $control): string|Link|null;


	#[ORM\PreFlush]
	public function onPreFlush(PreFlushEventArgs $event): void
	{
		$this->hash ??= $this->getHash();
	}
}
