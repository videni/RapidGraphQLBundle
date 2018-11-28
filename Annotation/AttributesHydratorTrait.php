<?php


declare(strict_types=1);


namespace Videni\Bundle\RestBundle\Annotation;

use Videni\Bundle\RestBundle\Exception\InvalidArgumentException;
use Doctrine\Common\Inflector\Inflector;

trait AttributesHydratorTrait
{
    /**
     * @var array
     */
    public $attributes = [];

    /**
     * @throws InvalidArgumentException
     */
    private function hydrateAttributes(array $values)
    {
        if (isset($values['attributes'])) {
            $this->attributes = $values['attributes'];
            unset($values['attributes']);
        }

        foreach ($values as $key => $value) {
            if (!property_exists($this, $key)) {
                throw new InvalidArgumentException(sprintf('Unknown property "%s" on annotation "%s".', $key, self::class));
            }

            (new \ReflectionProperty($this, $key))->isPublic() ? $this->$key = $value : $this->attributes += [Inflector::tableize($key) => $value];
        }
    }
}
