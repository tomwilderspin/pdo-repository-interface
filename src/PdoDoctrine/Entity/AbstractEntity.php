<?php
/**
 * AbstractEntity.php.
 */

namespace PdoDoctrine\Entity;


abstract class AbstractEntity implements EntityInterface
{

    public function toArray()
    {
        $properties = get_object_vars($this);
        $result = array();
        foreach ($properties as $key => $value) {
            $getter = 'get' . ucwords($key);
            if (method_exists($this, $getter)) {
                $value = $this->$getter();

                is_null($value)?: $result[$key] = $value;
            }
        }
        return $result;
    }

}