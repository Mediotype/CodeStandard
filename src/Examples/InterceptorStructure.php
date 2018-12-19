<?php
/**
 * @author    Mediotype Development <diveinto@mediotype.com>
 * @copyright 2018 Mediotype. All Rights Reserved.
 */

namespace Mediotype\Module\Plugin;

/**
 * Class InterceptorStructureMess
 *
 * @see InterceptedClass Intercepted class
 */
class InterceptorStructure
{
    /**
     * Before set Name
     *
     * @see InterceptedClass::setName() Intercepted method
     * @param object $subject
     * @param mixed ...$parameters
     * @return array
     */
    public function beforeSetName($subject, ...$parameters)
    {
        return $parameters;
    }

    /**
     * After set Name
     *
     * @see InterceptedClass::setName() Intercepted method
     * @param object $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterSetName($subject, $result)
    {
        return $result;
    }

    /**
     * Around set Name
     *
     * @see InterceptedClass::setName() Intercepted method
     * @param object $subject
     * @param callable $super
     * @param mixed ...$parameters
     * @return mixed
     */
    public function aroundSetName($subject, callable $super, ...$parameters)
    {
        return $super(...$parameters);
    }

    /**
     * Looks like an intercepted method but isn't
     *
     * @return void
     */
    private function beforeDoingSomething()
    {
        echo 'Not actually an intercepted method';
    }

    /**
     * Doesn't look like an intercepted method
     *
     * @return void
     */
    private function doingSomethingElse()
    {
        echo 'Not an intercepted method';
    }
}
