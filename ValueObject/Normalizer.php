<?php

namespace Siso\Bundle\ContentLoaderBundle\ValueObject;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

/**
 * Normalizer for serializing value objects as array.
 *
 * Based on PropertyNormalizer from Symfony 2.6.
 * After updating to 2.6 it could be possible to use original PropertyNormalizer
 * instead of this class.
 */
class Normalizer extends SerializerAwareNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $reflectionObject = new \ReflectionObject($object);
        $attributes = [];

        foreach ($reflectionObject->getProperties() as $property) {
            // Override visibility
            if (!$property->isPublic()) {
                $property->setAccessible(true);
            }
            $attributeValue = $property->getValue($object);

            if (null !== $attributeValue && !is_scalar($attributeValue)) {
                try {
                    $attributeValue = $this->serializer->normalize($attributeValue, $format, $context);
                } catch (\Exception $e) {
                    $message = $e->getMessage();
                }

            }

            $propertyName = $property->name;
            $attributes[$propertyName] = $attributeValue;
        }

        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return is_object($data) && $this->supports(get_class($data));
    }

    /**
     * Checks if the given class has any non-static property.
     *
     * @param string $class
     *
     * @return bool
     */
    private function supports($class)
    {
        $class = new \ReflectionClass($class);
        // We look for at least one non-static property
        foreach ($class->getProperties() as $property) {
            if (!$property->isStatic()) {
                return true;
            }
        }

        return false;
    }
}