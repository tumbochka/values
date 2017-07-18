<?php
namespace Makasim\Values;

final class HookStorage
{
    /**
     * @var \Closure[]
     */
    private static $hooks = [];

    public static function clearAll()
    {
        self::$hooks = [];
    }

    public static function getAll()
    {
        return self::$hooks;
    }

    /**
     * @param object|string $objectOrClass
     * @param string        $hook
     * @param \Closure      $callback
     */
    public static function register($objectOrClass, $hook, \Closure $callback)
    {
        $hookId = is_object($objectOrClass) ? self::getHookId($objectOrClass) : (string) $objectOrClass;

        self::$hooks[$hook][$hookId][spl_object_hash($callback)] = $callback;
    }

    /**
     * @param string        $hook
     * @param \Closure      $callback
     */
    public static function registerGlobal($hook, \Closure $callback)
    {
        static::register('_global', $hook, $callback);
    }

    /**
     * @param object $objectOrClass
     * @param string $hook
     *
     * @return \Traversable|\Closure[]
     */
    public static function get($objectOrClass, $hook)
    {
        foreach (isset(self::$hooks[$hook]['_global']) ? self::$hooks[$hook]['_global'] : [] as $callback) {
            yield $callback;
        }

        $class = is_object($objectOrClass) ? get_class($objectOrClass) : (string) $objectOrClass;
        foreach (isset(self::$hooks[$hook][$class]) ? self::$hooks[$hook][$class] : [] as $callback) {
            yield $callback;
        }

        if (is_object($objectOrClass)) {
            foreach (isset(
                self::$hooks[$hook][self::getHookId($objectOrClass)]) ?
                         self::$hooks[$hook][self::getHookId($objectOrClass)] :
                         []
                     as $callback) {
                yield $callback;
            }
        }
    }

    /**
     * @param object $object
     *
     * @return string
     */
    public static function getHookId($object)
    {
        $func = (function($object) {
            if (false == property_exists($object, 'hookId')) {
                $object->hookId = null;
            }

            if (false == $object->hookId) {
                $object->hookId = get_class($object).':'.uniqid('', true);
            }

            return $object->hookId;
        });

        $bcl = $func->bindTo($object, $object);

        return $bcl($object);
    }

    private function __construct()
    {
    }
}