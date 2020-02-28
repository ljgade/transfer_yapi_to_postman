<?php

final class Faker
{
    /**
     * ['type' => 'fake method'] map
     *
     * @var array
     */
    private $fakers = [
        'null' => 'fakeNull',
        'boolean' => 'fakeBoolean',
        'integer' => 'fakeInteger',
        'number' => 'fakeNumber',
        'string' => 'fakeString',
        'array' => 'fakeArray',
        'object' => 'fakeObject'
    ];

    /**
     * generate json object
     * @param $schema
     * @return mixed
     * @throws Exception
     */
    public function generate($schema)
    {
        if (!$schema instanceof \stdClass) {
            throw new \InvalidArgumentException(gettype($schema));
        }
        $type = $schema->type;
        if (!isset($this->fakers[$type])) {
            throw new \Exception("unsupported type: " . $type);
        }
        $faker = [$this, $this->fakers[$type]];
        if (is_callable($faker)) {
            return call_user_func($faker, $schema);
        }
        throw new \LogicException;
    }

    /**
     * @param stdClass $schema
     * @return string[] Property names
     */
    public function getProperties(\stdClass $schema): array
    {
        return array_keys((array)$schema->properties) ?? [];
    }

    /**
     * @return null
     */
    private function fakeNull()
    {
        return null;
    }

    /**
     * @return bool
     */
    private function fakeBoolean(): bool
    {
        return rand(1, 10) % 2 == 0;
    }

    /**
     * @param stdClass $schema
     * @return int
     */
    private function fakeInteger(\stdClass $schema): int
    {
        return rand(0, 1000);
    }

    /**
     * @param stdClass $schema
     * @return int
     */
    private function fakeNumber(\stdClass $schema)
    {
        return rand(0, 1000);
    }

    /**
     * @param stdClass $schema
     * @return string
     */
    private function fakeString(\stdClass $schema): string
    {
        return $schema->description ?? '';
    }

    /**
     * @param stdClass $schema
     * @return array
     * @throws Exception
     */
    private function fakeArray(\stdClass $schema): array
    {
        if (!isset($schema->items)) {
            $subSchemas = [];
            // List
        } elseif (is_object($schema->items)) {
            $subSchemas = [$schema->items];
            // Tuple
        } elseif (is_array($schema->items)) {
            $subSchemas = $schema->items;
        } else {
            throw new \Exception('Unsupported Array: ' . json_encode($schema));
        }
        $dummies = [];
        $itemSize = count($subSchemas);
        for ($i = 0; $i < $itemSize; $i++) {
            $subSchema = $subSchemas[$i % count($subSchemas)];
            $dummies[] = $this->generate($subSchema);
        }
        return ($schema->uniqueItems ?? false) ? array_unique($dummies) : $dummies;
    }

    /**
     * @param stdClass $schema
     * @return stdClass
     * @throws Exception
     */
    private function fakeObject(\stdClass $schema): \stdClass
    {
        $properties = $schema->properties ?? new \stdClass();
        $propertyNames = $this->getProperties($schema);
        $dummy = new \stdClass();
        foreach ($propertyNames as $key) {
            if (isset($properties->{$key})) {
                $subSchema = $properties->{$key};
                $dummy->{$key} = $this->generate($subSchema);
            }
        }
        return $dummy;
    }
}