<?php

declare(strict_types=1);

namespace Pepperfm\Ssd;

use Illuminate\Support\Arr;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Pepperfm\Ssd\Attributes\{SnakeCaseProperties, ToIterable, MapName};

abstract class BaseDto implements Arrayable, \JsonSerializable
{
    /**
     * @param mixed $params
     */
    public function __construct(mixed ...$params)
    {
        if (gettype($params) !== 'array') {
            throw new \InvalidArgumentException('Cant spread param type');
        }
        if (count($params) === 1) {
            if (is_array($params[0])) {
                $params = $params[0];
            } else {
                if ($params[0] instanceof Arrayable) {
                    $params = $params[0]->toArray();
                } else {
                    $params = (array) $params[0];
                }
            }
        }
        // check this
        //  elseif (count($params) > 1) {
        //     $params = array_combine(array_map('strtolower', array_keys($params)), array_values($params));
        // }

        $r = new \ReflectionClass($this);
        // if (!empty($r->getAttributes(SnakeCaseProperties::class))) {
        //     $this::$propertyCase = 'snake';
        // }
        foreach ($params as $key => $param) {
            $caseKey = str($key)->camel()->value();
            foreach ($r->getProperties() as $property) {
                foreach ($property->getAttributes(MapName::class) as $attribute) {
                    $mappedName = $attribute->newInstance()->name;
                    $this->$mappedName = $param;
                    unset($params[$key]);
                }
            }
            if ($r->hasProperty($caseKey)) {
                $prop = $r->getProperty($caseKey);
                if ($prop->getType()) {
                    $currentClass = $prop->getType()->getName();
                    if (property_exists($this, $caseKey)) {
                        if (is_subclass_of($currentClass, self::class)) {
                            $this->$caseKey = $currentClass::make($param);
                        } elseif ((class_exists($currentClass) && (new $currentClass()) instanceof \ArrayAccess)) {
                            if (!empty($prop->getAttributes())) {
                                /** @var \ReflectionAttribute $attribute */
                                $attribute = head($prop->getAttributes(ToIterable::class));
                                $result = [];
                                foreach ($param as $item) {
                                    $result[] = $attribute->newInstance()->type::make($item);
                                }
                                if ($castType = $attribute->newInstance()->castType) {
                                    $this->$caseKey = new $castType($result);
                                    continue;
                                }
                                $this->$caseKey = new $currentClass($result);
                                continue;
                            }

                            $this->$caseKey = new $currentClass($param);
                        } elseif (is_array($param)) {
                            /** @var \ReflectionAttribute $attribute */
                            $attribute = head($prop->getAttributes(ToIterable::class));
                            $result = [];
                            foreach ($param as $paramKey => $item) {
                                if ($attribute && filled($attribute)) {
                                    $result[$paramKey] = $attribute->newInstance()->type::make($item);
                                } else {
                                    $result[$paramKey] = $item;
                                }
                            }
                            $this->$caseKey = $result;
                        } else {
                            $this->$caseKey = $param;
                        }
                    }
                }
            }
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
        $caseKey = str($name)->camel()->value();
        if (property_exists($this, $caseKey)) {
            return $this->$caseKey;
        }

        throw new \OutOfBoundsException("Property $name does not exist in " . $this::class);
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
        $array = [];
        foreach ((array) $this as $key => $item) {
            $caseKey = str($key)->camel()->replaceMatches('/([^\d])(\d++)/', '\1_\2')->value();
            $array[$caseKey] = $item;
        }

        return $array;
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
}
