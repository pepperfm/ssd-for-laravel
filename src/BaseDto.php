<?php

declare(strict_types=1);

namespace Pepperfm\Ssd;

use Illuminate\Support\Arr;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Pepperfm\Ssd\Attributes\{MapName, ToIterable};

abstract class BaseDto implements Arrayable, \JsonSerializable
{
    private const REGEX_NUMERIC = '/([^\d])(\d++)/';

    /**
     * @var array<class-string, array{
     *     properties: array<string, array{
     *         reflection: \ReflectionProperty,
     *         mapName: ?string,
     *         toIterable: ?ToIterable,
     *         typeName: ?string,
     *         allowsNull: bool
     *     }>,
     *     lookup: array<string, string>
     * }>
     */
    private static array $metadataCache = [];

    public function __construct(mixed ...$params)
    {
        $payload = $this->normalizeParams($params);
        $metadata = static::metadata();

        foreach ($payload as $key => $value) {
            $resolved = self::resolvePropertyName($key, $metadata['lookup']);

            if ($resolved === null || !isset($metadata['properties'][$resolved])) {
                continue;
            }

            $this->assignValue($resolved, $value, $metadata['properties'][$resolved]);
        }
    }

    public static function make(...$params): static
    {
        return new static(...$params);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        $metadata = static::metadata();
        $propertyName = self::resolvePropertyName($name, $metadata['lookup']);

        if ($propertyName !== null && property_exists($this, $propertyName)) {
            $reflection = $metadata['properties'][$propertyName]['reflection'];

            if (!$reflection->isInitialized($this)) {
                return null;
            }

            return $this->$propertyName;
        }

        throw new \OutOfBoundsException("Property $name does not exist in " . static::class);
    }

    /**
     * @param array|Arrayable $params
     *
     * @template TKey of int
     * @template TValue
     *
     * @return Collection<TKey, TValue>
     */
    public static function collect(array|Arrayable $params): Collection
    {
        $collection = collect();
        foreach ($params as $key => $item) {
            $collection->put($key, static::make($item));
        }

        return $collection;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $metadata = static::metadata();
        $result = [];

        foreach ($metadata['properties'] as $name => $info) {
            $reflection = $info['reflection'];

            if (!$reflection->isInitialized($this)) {
                continue;
            }

            $value = $this->$name;
            if ($value instanceof self) {
                $value = $value->toArray();
            } elseif ($value instanceof Arrayable) {
                $value = $value->toArray();
            } elseif (is_array($value)) {
                $value = $this->normalizeOutputArray($value);
            }

            $key = $info['mapName'] ?? str($name)
                ->camel()
                ->replaceMatches(self::REGEX_NUMERIC, '\\1_\\2')
                ->value();

            $result[$key] = $value;
        }

        return $result;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    final public function except(...$keys): array
    {
        return Arr::except($this->toArray(), ...$keys);
    }

    final public function only(...$keys): array
    {
        return Arr::only($this->toArray(), ...$keys);
    }

    /**
     * @return array{
     *     properties: array<string, array{
     *          reflection: \ReflectionProperty,
     *          mapName: ?string,
     *          toIterable: ?ToIterable,
     *          typeName: ?string,
     *          allowsNull: bool
     *    }>,
     *    lookup: array<string, string>
     * }
     */
    private static function metadata(): array
    {
        $class = static::class;

        if (isset(self::$metadataCache[$class])) {
            return self::$metadataCache[$class];
        }

        $reflection = new \ReflectionClass($class);
        $properties = [];
        $lookup = [];

        foreach ($reflection->getProperties() as $property) {
            $name = $property->getName();

            $mapAttribute = $property->getAttributes(MapName::class);
            $mapName = $mapAttribute ? $mapAttribute[0]->newInstance()->name : null;

            $toIterableAttr = $property->getAttributes(ToIterable::class);
            $toIterable = $toIterableAttr ? $toIterableAttr[0]->newInstance() : null;

            $type = $property->getType();
            $typeName = $type && !$type->isBuiltin() ? $type->getName() : null;
            $allowsNull = !$type || $type->allowsNull();

            $properties[$name] = [
                'reflection' => $property,
                'mapName' => $mapName,
                'toIterable' => $toIterable,
                'typeName' => $typeName,
                'allowsNull' => $allowsNull,
            ];

            $aliases = [
                $name,
                str($name)->camel()->value(),
                str($name)->snake()->value(),
            ];

            if ($mapName !== null) {
                $aliases[] = $mapName;
                $aliases[] = str($mapName)->camel()->value();
                $aliases[] = str($mapName)->snake()->value();
            }

            foreach (array_unique($aliases) as $alias) {
                $lookup[strtolower($alias)] = $name;
            }
        }

        return self::$metadataCache[$class] = [
            'properties' => $properties,
            'lookup' => $lookup,
        ];
    }

    /**
     * @param array<int, mixed> $params
     *
     * @return array<string|int, mixed>
     */
    private function normalizeParams(array $params): array
    {
        if (count($params) === 1) {
            $value = $params[0];

            if ($value instanceof Arrayable) {
                return $value->toArray();
            }

            if ($value instanceof \Traversable) {
                return iterator_to_array($value);
            }

            if (is_array($value)) {
                return $value;
            }

            return (array) $value;
        }

        return $params;
    }

    private static function resolvePropertyName(int|string $key, array $lookup): ?string
    {
        $candidates = [strtolower((string) $key)];

        if (!is_int($key)) {
            $candidates[] = strtolower(str((string) $key)->camel()->value());
            $candidates[] = strtolower(str((string) $key)->snake()->value());
        }

        foreach (array_unique($candidates) as $candidate) {
            if (isset($lookup[$candidate])) {
                return $lookup[$candidate];
            }
        }

        return null;
    }

    /**
     * @param array{
     *     reflection: \ReflectionProperty,
     *     mapName: ?string,
     *     toIterable: ?ToIterable,
     *     typeName: ?string,
     *     allowsNull: bool
     * } $meta
     */
    private function assignValue(string $propertyName, mixed $value, array $meta): void
    {
        if (!property_exists($this, $propertyName)) {
            return;
        }

        if ($value === null) {
            if ($meta['allowsNull']) {
                $this->$propertyName = null;
            }

            return;
        }

        if ($value instanceof Arrayable) {
            $value = $value->toArray();
        }

        $toIterable = $meta['toIterable'];
        $typeName = $meta['typeName'];

        if ($toIterable && ($items = $this->toIterableArray($value)) !== null) {

            $converted = array_map(function ($item) use ($toIterable) {
                return $this->convertIterableItem($item, $toIterable);
            }, $items);

            if ($toIterable->castType) {
                $this->$propertyName = new $toIterable->castType($converted);

                return;
            }
            if ($typeName && class_exists($typeName) && is_a($typeName, \ArrayAccess::class, true)) {
                $this->$propertyName = new $typeName($converted);

                return;
            }

            $this->$propertyName = $converted;

            return;
        }

        if ($typeName && class_exists($typeName)) {
            if (is_a($typeName, self::class, true)) {
                $this->$propertyName = $value instanceof $typeName ? $value : $typeName::make($value);

                return;
            }

            if ($value instanceof $typeName) {
                $this->$propertyName = $value;

                return;
            }

            if (
                is_a($typeName, \ArrayAccess::class, true) &&
                ($items = $this->toIterableArray($value)) !== null
            ) {
                $this->$propertyName = new $typeName($items);

                return;
            }
        }

        $this->$propertyName = $value;
    }

    private function convertIterableItem(mixed $value, ToIterable $attribute): mixed
    {
        $class = $attribute->type;

        if ($value instanceof $class) {
            return $value;
        }

        if (is_a($class, self::class, true)) {
            return $class::make($value);
        }

        return $value;
    }

    private function toIterableArray(mixed $value): ?array
    {
        if (is_array($value)) {
            return $value;
        }
        if ($value instanceof Arrayable) {
            return $value->toArray();
        }
        if ($value instanceof \Traversable) {
            return iterator_to_array($value);
        }

        return null;
    }

    private function normalizeOutputArray(array $value): array
    {
        foreach ($value as $key => $item) {
            if ($item instanceof self) {
                $value[$key] = $item->toArray();
                continue;
            }
            if ($item instanceof Arrayable) {
                $value[$key] = $item->toArray();
                continue;
            }
            if (is_array($item)) {
                $value[$key] = $this->normalizeOutputArray($item);
            }
        }

        return $value;
    }
}
