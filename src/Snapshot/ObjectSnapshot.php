<?php
/**
 * This file is part of the Totem package
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @copyright Baptiste Clavié <clavie.b@gmail.com>
 * @license   http://www.opensource.org/licenses/MIT-License MIT License
 */

namespace Totem\Snapshot;

use \ReflectionObject,

    \InvalidArgumentException;

use Totem\AbstractSnapshot;

/**
 * Represents a snapshot of an object at a given time
 *
 * @author Baptiste Clavié <clavie.b@gmail.com>
 */
class ObjectSnapshot extends AbstractSnapshot
{
    /** @var string object's hash */
    protected $oid;

    /**
     * Build this snapshot.
     *
     * @param object $object Object to fix at the current moment
     *
     * @throws InvalidArgumentException If this is not an object
     */
    public function __construct($object)
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException(sprintf('Object expected, had "%s"', gettype($object)));
        }

        $this->raw = $object;
        $this->oid = spl_object_hash($object);
        $refl      = new ReflectionObject($object);

        if (method_exists('\\ReflectionObject', 'isCloneable') && $refl->isCloneable()) {
            $this->raw = clone $object;
        }

        /** @var \ReflectionProperty $reflProperty */
        foreach ($refl->getProperties() as $reflProperty) {
            $reflProperty->setAccessible(true);
            $value = $reflProperty->getValue($object);

            $this->data[$reflProperty->getName()] = $value;
        }

        parent::normalize();
    }

    /** {@inheritDoc} */
    public function isComparable(AbstractSnapshot $snapshot)
    {
        if (!parent::isComparable($snapshot)) {
            return false;
        }

        return $snapshot->oid === $this->oid;
    }
}

