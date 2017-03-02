<?php
namespace Makasim\Values;

trait CastTrait
{
    protected function registerCastHooks()
    {
        $castValueHook = function($object, $key, $value) {
            return $this->castValue($value);
        };

        $castToHook = function($object, $key, $value, $default, $castTo) {
            return $castTo ? $this->cast($value, $castTo) : $value;
        };

        register_hook($this, 'pre_set_value', $castValueHook);
        register_hook($this, 'pre_add_value', $castValueHook);
        register_hook($this, 'post_get_value', $castToHook);
    }

    /**
     * @param mixed $value
     * @param string $castTo
     * 
     * @return mixed
     */
    protected function cast($value, $castTo)
    {
        if (\DateTime::class == $castTo) {
            if (is_numeric($value)) {
                $value = \DateTime::createFromFormat('U', $value);
            } elseif (is_array($value)) {
                $value = \DateTime::createFromFormat('U', $value['unix']);
            } else {
                $value = new \DateTime($value);
            }
        } else if (\DateInterval::class == $castTo) {
            if (is_array($value)) {
                $value = new \DateInterval($value['interval']);
            } else {
                $value = new \DateInterval($value);
            }
        } else {
            settype($value, $castTo);
        }
        
        return $value;
    }

    /**
     * @param mixed $value
     * 
     * @return mixed 
     */
    protected function castValue($value)
    {
        if ($value instanceof \DateTime) {
            $value = [
                'unix' => (int) $value->format('U'),
                'iso' => (string) $value->format(DATE_ISO8601),
            ];
        } elseif ($value instanceof \DateInterval) {
            $value = [
                'interval' => $value->format('P%yY%mM%dDT%HH%IM%SS'),
                'days' => $value->days,
                'y' => $value->y,
                'm' => $value->m,
                'd' => $value->d,
                'h' => $value->h,
                'i' => $value->i,
                's' => $value->s,
            ];
        }
        
        return $value;
    }
}