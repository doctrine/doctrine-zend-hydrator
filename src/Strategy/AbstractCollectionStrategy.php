<?php

declare(strict_types=1);

namespace Doctrine\Zend\Hydrator\Strategy;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Inflector\Inflector;
use InvalidArgumentException;
use Zend\Hydrator\Strategy\StrategyInterface;

abstract class AbstractCollectionStrategy implements StrategyInterface
{
    /**
     * @var string
     */
    protected $collectionName;

    /**
     * @var ClassMetadata
     */
    protected $metadata;

    /**
     * @var object
     */
    protected $object;

    /**
     * Set the name of the collection
     */
    public function setCollectionName(string $collectionName) : void
    {
        $this->collectionName = $collectionName;
    }

    /**
     * Get the name of the collection
     */
    public function getCollectionName() : string
    {
        return $this->collectionName;
    }

    /**
     * Set the class metadata
     */
    public function setClassMetadata(ClassMetadata $classMetadata) : void
    {
        $this->metadata = $classMetadata;
    }

    /**
     * Get the class metadata
     */
    public function getClassMetadata() : ClassMetadata
    {
        return $this->metadata;
    }

    /**
     * Set the object
     */
    public function setObject(object $object) : void
    {
        $this->object = $object;
    }

    /**
     * Get the object
     */
    public function getObject() : object
    {
        return $this->object;
    }

    /**
     * {@inheritDoc}
     */
    public function extract($value, ?object $object = null)
    {
        return $value;
    }

    /**
     * Return the collection by value (using the public API)
     *
     * @throws InvalidArgumentException
     */
    protected function getCollectionFromObjectByValue() : Collection
    {
        $object = $this->getObject();
        $getter = 'get' . Inflector::classify($this->getCollectionName());

        if (! method_exists($object, $getter)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The getter %s to access collection %s in object %s does not exist',
                    $getter,
                    $this->getCollectionName(),
                    get_class($object)
                )
            );
        }

        return $object->$getter();
    }

    /**
     * Return the collection by reference (not using the public API)
     */
    protected function getCollectionFromObjectByReference() : Collection
    {
        $object = $this->getObject();
        $refl = $this->getClassMetadata()->getReflectionClass();
        $reflProperty = $refl->getProperty($this->getCollectionName());

        $reflProperty->setAccessible(true);

        return $reflProperty->getValue($object);
    }


    /**
     * This method is used internally by array_udiff to check if two objects are equal, according to their
     * SPL hash. This is needed because the native array_diff only compare strings
     */
    protected function compareObjects(object $a, object $b) : int
    {
        return strcmp(spl_object_hash($a), spl_object_hash($b));
    }
}
