<?php
namespace App\Core\Http\Controller;

use App\Constants\Delimiter;
use App\Core\Exceptions\GuardActionNotFoundException;
use App\Core\Exceptions\GuardNotFoundException;
use App\Core\Exceptions\UnauthorizedActionException;
use App\Core\Http\Guard\HasGuard;
use App\Utils\Arrays;
use App\Utils\Strings;

/**
 * The base class for Controller with various utility.\
 * This class implements <i>HasGuard</i> and provides a fast way to incorporate Guards into the controllers.
 */
class Controller implements HasGuard
{
    private ?object $guard = null;

    public function getPossibleGuards(): array {
        $reflector = new \ReflectionClass($this);
        $namespace = $reflector->getNamespaceName();
        $controllerName = $reflector->getShortName();
        return $this->getGuardsFromControllerNamespaceAndName($namespace, $controllerName);
    }

    private function getGuardsFromControllerNamespaceAndName(string $namespace, string $controllerName) {
        $guardName1 = $controllerName . 'Guard';
        $guardName2 = Strings::rtrimSubstr($controllerName, 'Controller') . 'Guard';
        $guardNames = [$guardName1, $guardName2];
        $possible = static::getPossibleNames($namespace, $guardNames);

        $namespaceTokens = explode(Delimiter::NAMESPACE, $namespace);
        if (count($namespaceTokens) === 1 && $namespaceTokens[0] === '') {
            return $possible;
        }

        array_pop($namespaceTokens);
        $searchNamespaces = ['Guards', 'Guard'];
        foreach ($searchNamespaces as $name) {
            $guardNamespaceTokens = array_merge($namespaceTokens, [$name]);
            $guardNamespace = implode(Delimiter::NAMESPACE, $guardNamespaceTokens);
            $news = static::getPossibleNames($guardNamespace, $guardNames);
            array_push($possible, ...$news);
        }
        return $possible;
    }

    private static function getPossibleNames(string $namespace, array $guardNames) {
        return array_map(
            fn(string $guardName) => $namespace . Delimiter::NAMESPACE . $guardName,
            $guardNames
        );
    }

    public function setGuard(object $guard): void {
        $this->guard = $guard;
    }

    /**
     * Authorize an action with the guard. This method throws a
     * 403 Forbidden with <i>UnauthorizedActionException</i> if the authorization failed.
     * @param string $action The action to authorize.
     * @param mixed... $args The arguments to pass to the Guard.
     * @return void This method throws on an authorization failure and does not return.
     * @throws UnauthorizedActionException If the action failed the authorization.
     * @throws GuardNotFoundException If no Guard exists.
     * @throws GuardActionNotFoundException If no suitable Guard's method is found for the action.
     */
    protected function authorize(string $action, mixed ...$args): void {
        if (!$this->test($action, ...$args)) {
            throw new UnauthorizedActionException($action);
        }
    }

    /**
     * Authorize an action with the guard if a suitable method is defined. This method throws a
     * 403 Forbidden with <i>UnauthorizedActionException</i> if the authorization failed. If
     * no suitable method for the action is defined, it is treated as a pass.
     * @param string $action The action to authorize.
     * @param mixed... $args The arguments to pass to the Guard.
     * @return void This method throws on an authorization failure and does not return.
     * @throws UnauthorizedActionException If the action failed the authorization.
     * @throws GuardNotFoundException If no Guard exists.
     */
    protected function authorizeIfDefined(string $action, mixed ...$args): void {
        if (!$this->testIfDefined($action, ...$args)) {
            throw new UnauthorizedActionException($action);
        }
    }

    /**
     * Check if an action passes the guard.
     * @param string $action The action to check.
     * @param mixed... $args The arguments to pass to the Guard.
     * @return bool Return true if passed. False otherwise.
     * @throws GuardNotFoundException If no Guard exists.
     * @throws GuardActionNotFoundException If no suitable Guard's method is found for the action.
     */
    protected function test(string $action, mixed ...$args): bool {
        if (!$this->guard) {
            $class = static::class;
            throw new GuardNotFoundException("Guard not exist for Controller [$class]");
        }
        return $this->invokeGuard($action, $args);
    }

    /**
     * Check if an action passes the guard. If no suitable method is defined for the
     * action, it is treated as a pass.
     * @param string $action The action to check.
     * @param mixed... $args The arguments to pass to the Guard.
     * @return bool Return true if passed. False otherwise.
     * @throws GuardNotFoundException If no Guard exists.
     */
    protected function testIfDefined(string $action, mixed ...$args): bool {
        if (!$this->guard) {
            $class = static::class;
            throw new GuardNotFoundException("Guard not exist for Controller [$class]");
        }

        try {
            return $this->invokeGuard($action, $args);
        }
        catch (GuardActionNotFoundException $e) {
            return true;
        }
    }

    private function invokeGuard(string $action, array $args): bool {
        $reflector = new \ReflectionObject($this->guard);
        $methods = static::getCorrectSignatureMethods($reflector);
        $methodName = $this->getGuardMethodName($action);
        $guardMethod = $this->findGuardMethod($methods, $methodName);
        if (!$guardMethod) {
            $guardName = $reflector->getName();
            $message = "No suitable method with name [$methodName] for action [$action]"
                     . " found in Guard [$guardName]";
            throw new GuardActionNotFoundException($message);
        }
        try {
            $result = $guardMethod->invokeArgs($this->guard, $args);
            return boolval($result);
        }
        catch (\ReflectionException $e) {
            return true;
        }
    }

    /**
     * @return \ReflectionMethod[]
     */
    private static function getCorrectSignatureMethods(\ReflectionObject $reflector) {
        $methods = $reflector->getMethods();
        return Arrays::filterReindex(
            $methods,
            fn (\ReflectionMethod $method) => static::isCorrectSignatureMethod($method)
        );
    }

    private static function isCorrectSignatureMethod(\ReflectionMethod $method) {
        return $method->isPublic() && !$method->isAbstract();
    }

    /**
     * @param \ReflectionMethod[] $methods
     * @return \ReflectionMethod|false
     */
    private function findGuardMethod(array $methods, string $methodName) {
        foreach ($methods as $method) {
            if ($method->getName() === $methodName) {
                return $methods;
            }
        }
        return false;
    }
    
    /**
     * Get Guard's method name for the corresponding action.
     * @param string $action The action to authorize.
     * @return string The Guard's method name.
     */
    protected function getGuardMethodName(string $action): string {
        $action = trim($action);
        $words = preg_split('/[\\\ \/\.\-_|]/', $action);
        $capitalizedWords = array_map('ucfirst', $words);
        return $this->getGuardMethodNamePrefix() . implode('', $capitalizedWords);
    }

    /**
     * Get the prefix for Guard's method names
     * @return string The prefix
     */
    protected function getGuardMethodNamePrefix(): string {
        return 'can';
    }
}
