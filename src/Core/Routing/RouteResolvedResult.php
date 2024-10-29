<?php
namespace App\Core\Routing;

final class RouteResolvedResult
{
    /**
     * @param string|string[]|\Closure $action
     * @param array<string,string> $routeParams
     * @param ?string[] $middlewares
     * @param ?string[] $excludedMiddlewares
     */
    public function __construct(
        private string|array|\Closure $action,
        private array $routeParams,
        private ?array $middlewares = null,
        private ?array $excludedMiddlewares = null,
    ) {
        
    }

    /**
     * @return string|string[]|\Closure
     */
    public function action() {
        return $this->action;
    }

    /**
     * @return array<string,string>
     */
    public function routeParams() {
        return $this->routeParams;
    }

    /**
     * @return string[]
     */
    public function middlewares() {
        return $this->middlewares ?? [];
    }
    
    /**
    * @return string[]
    */
   public function excludedMiddlewares() {
       return $this->excludedMiddlewares ?? [];
   }
}
