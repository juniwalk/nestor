<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2024
 * @license   MIT License
 */

namespace JuniWalk\Nestor\Entity\Subscribers;

use Closure;
use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Doctrine\ORM\Events;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\Proxy\Proxy;
use JuniWalk\Nestor\Chronicler;
use JuniWalk\Nestor\Entity\Attributes\ActivityOverride;
use JuniWalk\Nestor\Entity\Attributes\TargetIgnore;
use JuniWalk\Nestor\Entity\Record;
use JuniWalk\Nestor\Enums\Action;
use JuniWalk\Nestor\Interfaces\ParamsProvider;
use JuniWalk\Nestor\Interfaces\TargetProvider;
use JuniWalk\Utils\Format;
use Nette\Security\IIdentity as Identity;
use Nette\Security\User as LoggedInUser;
use ReflectionClass;
use Throwable;

class ActivitySubscriber implements EventSubscriber
{
	/** @var array<string, bool> */
	private static array $ignored = [];

	/** @var array<string, array{object, Action, array<string, mixed>, mixed}> */
	private array $items = [];
	private bool $isFlushing = false;
	private ?Identity $user;

	/**
	 * @param non-empty-string $messageFormat
	 */
	final public function __construct(
		private readonly string $messageFormat,
		private readonly Chronicler $chronicler,
		private readonly LoggedInUser $loggedInUser,
		private readonly EntityManager $entityManager,
	) {
		$this->user = $loggedInUser->getIdentity();
	}


	public static function setIgnored(object $target, bool $ignore = true): void
	{
		self::$ignored[spl_object_hash($target)] = $ignore;
	}


	/**
	 * @return string[]
	 */
	final public function getSubscribedEvents(): array
	{
		return [
			Events::onFlush,
			Events::postFlush,
		];
	}


	public function onFlush(EventArgs $event): void
	{
		$uow = $this->entityManager->getUnitOfWork();

		if ($this->isFlushing) {
			return;
		}

		foreach ($uow->getScheduledEntityInsertions() as $entity) {
			$this->process(Action::Create, $entity);
		}

		foreach ($uow->getScheduledEntityUpdates() as $entity) {
			$this->process(Action::Update, $entity);
		}

		foreach ($uow->getScheduledEntityDeletions() as $entity) {
			$this->process(Action::Delete, $entity);
		}

		foreach ($uow->getScheduledCollectionUpdates() as $collection) {
			$this->process(Action::Update, $collection);
		}

		foreach ($uow->getScheduledCollectionDeletions() as $collection) {
			$this->process(Action::Delete, $collection);
		}
	}


	public function postFlush(EventArgs $event): void
	{
		if ($this->isFlushing || empty($this->items)) {
			return;
		}

		$this->items = array_diff_key($this->items, array_filter(self::$ignored));
		$this->isFlushing = true;

		foreach ($this->items as [$target, $action, $params, $targetId]) {
			$message = Format::tokens($this->messageFormat, [
				'className' => Format::className($target),
				'action' => $action->value,
			]);

			if ($target instanceof ParamsProvider) {
				$params = array_merge($params, $target->getRecordParams($action));
			}

			$record = $this->chronicler->createRecord($action->value, $message)->create();
			$record->setTarget($target, $targetId);
			$record->setLevel($action->color());
			$record->setOwner($this->user);
			$record->setParams($params);
			$record->setFinished(true);

			if ($target instanceof TargetProvider && $target = $target->getRecordTarget()) {
				$record->setTarget($target);
			}

			$this->entityManager->persist($record);
		}

		$this->entityManager->flush();
		$this->isFlushing = false;
		$this->items = self::$ignored = [];
	}


	private function process(Action $action, object $target): void
	{
		$changes = $this->findChanges($action, $target);
		$hash = spl_object_hash($target);

		if ($target instanceof PersistentCollection) {
			$target = $target->getOwner() ?? $target;
		}

		$class = new ReflectionClass($target);

		if ($class->getAttributes(TargetIgnore::class)) {
			return;
		}

		if (!$changes || $target instanceof Record) {
			return;
		}

		$this->items[$hash] = [$target, $action, $changes, null];

		try {
			$fields = $this->entityManager
				->getClassMetadata($target::class)
				->getIdentifierValues($target);

			if (sizeof($fields) < 2) {
				$fields = current($fields);
			}

			$this->items[$hash][3] = $fields;

		} catch (Throwable) {
		}
	}


	/**
	 * @return array<string, mixed>
	 */
	private function findChanges(Action $action, object $target): array
	{
		$uow = $this->entityManager->getUnitOfWork();
		$changes = [];

		if ($target instanceof PersistentCollection) {
			$fieldName = $target->getMapping()['fieldName'];
			$changes[$fieldName] = [
				$target->getSnapshot(),
				$target->getValues(),
			];

			$target = $target->getOwner() ?? $target;
		}

		$changes = array_merge($changes, $uow->getEntityChangeSet($target));

		if ($action == Action::Create) foreach ($changes as $key => [$old, $new]) {
			$changes[$key] = $new;
		}

		if ($action == Action::Delete && !$changes) {
			$changes = Closure::fromCallable(fn() => get_object_vars($this))
				->call($target);

			if ($target instanceof Proxy) {
				$changes = array_diff_key($changes, [
					'lazyPropertiesDefaults' => null,
					'lazyPropertiesNames' => null,
					'__isInitialized__' => null,
					'__initializer__' => null,
					'__cloner__' => null,
				]);
			}
		}

		foreach ($this->findOverrides($target) as $fieldName => $override) {
			$changes = $override->process($changes, $fieldName);
		}

		return $changes;
	}


	/**
	 * @return array<string, ActivityOverride>
	 */
	private function findOverrides(object $target): array
	{
		$class = new ReflectionClass($target);
		$result = [];

		if ($target instanceof Proxy && $classParent = $class->getParentClass()) {
			$class = $classParent;
		}

		foreach ($class->getProperties() as $property) {
			foreach ($property->getAttributes(ActivityOverride::class) as $attribute) {
				$result[$property->getName()] = $attribute->newInstance();
			}
		}

		return $result;
	}
}
