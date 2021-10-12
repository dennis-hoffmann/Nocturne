<?php

namespace App\Elastic;

use \ONGR\ElasticsearchBundle\Mapping\Converter as BaseConverter;
use ONGR\ElasticsearchBundle\Result\ObjectIterator;

/**
 * This class is copied from the ONGR ElasticSearchBundle.
 * It fixes the ObjectIterator trying to iterate over an not existent
 * collection of embedded objects in case there is only one object indexed.
 *
 * Class Converter
 * @package App\Elastic
 */
class Converter extends BaseConverter
{
    private $propertyMetadata = [];

    public function addClassMetadata(string $class, array $metadata): void
    {
        $this->propertyMetadata[$class] = $metadata;
    }

    public function convertArrayToDocument(string $namespace, array $raw)
    {
        if (!isset($this->propertyMetadata[$namespace])) {
            throw new \Exception("Cannot convert array to object of class `$namespace`.");
        }

        return $this->denormalize($raw, $namespace);
    }

    public function convertDocumentToArray($document): array
    {
        $class = get_class($document);

        if (!isset($this->propertyMetadata[$class])) {
            throw new \Exception("Cannot convert object of class `$class` to array.");
        }

        return $this->normalize($document);
    }

    protected function normalize($document, $metadata = null)
    {
        $metadata = $metadata ?? $this->propertyMetadata[get_class($document)];
        $result = [];

        foreach ($metadata as $field => $fieldMeta) {
            $getter = $fieldMeta['getter'];
            $value = $fieldMeta['public'] ? $document->{$fieldMeta['name']} : $document->$getter();

            if ($fieldMeta['embeded']) {
                if (is_iterable($value)) {
                    foreach ($value as $item) {
                        $result[$field][] = $this->normalize($item, $fieldMeta['sub_properties']);
                    }
                } else {
                    $result[$field] = $this->normalize($value, $fieldMeta['sub_properties']);
                }
            } else {
                if ($value instanceof \DateTime) {
                    $value = $value->format(\DateTime::ISO8601);
                }
                $result[$field] = $value;
            }
        }

        return $result;
    }

    protected function denormalize(array $raw, string $namespace)
    {
        $metadata = $this->propertyMetadata[$namespace];
        $object = new $namespace();

        foreach ($raw as $field => $value) {
            $fieldMeta = $metadata[$field];
            $setter = $fieldMeta['setter'];

            if ($fieldMeta['embeded']) {
                $this->addClassMetadata($fieldMeta['class'], $fieldMeta['sub_properties']);
                // Check if it is an associative array
                if ($this->hasStringKeys($value)) {
                    $iterator = new ObjectIterator($fieldMeta['class'], [$value], $this);
                } else {
                    $iterator = new ObjectIterator($fieldMeta['class'], $value, $this);
                }

                if ($fieldMeta['public']) {
                    $object->{$fieldMeta['name']} = $iterator;
                } else {
                    $object->$setter($iterator);
                }
            } else {
                if ($fieldMeta['type'] == 'date') {
                    $value = \DateTime::createFromFormat(\DateTime::ISO8601, $value) ?: null;
                }
                if ($fieldMeta['public']) {
                    $object->{$fieldMeta['name']} = $value;
                } else {
                    if ($fieldMeta['identifier']) {
                        $setter = function ($field, $value) {
                            $this->$field = $value;
                        };

                        $setter = \Closure::bind($setter, $object, $object);
                        $setter($fieldMeta['name'], $value);
                    } else {
                        $object->$setter($value);
                    }
                }
            }
        }

        return $object;
    }

    protected function hasStringKeys(array $array) {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }

}