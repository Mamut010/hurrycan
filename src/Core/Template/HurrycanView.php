<?php
namespace App\Core\Template;

use App\Core\Exceptions\UnsupportedOperationException;
use App\Core\Exceptions\ViewRenderException;
use App\Core\Template\Contracts\RenderableView;
use App\Utils\Arrays;
use Closure;

class HurrycanView implements RenderableView
{
    private ?\Closure $beforeRenderHook = null;

    public function __construct(
        private string $viewName,
        private string $file,
        private array $parameters
    ) {

    }

    #[\Override]
    public function getName(): string {
        return $this->viewName;
    }

    #[\Override]
    public function with(string $key, mixed $value): self {
        $this->parameters[$key] = $value;
        return $this;
    }

    #[\Override]
    public function render(): string {
        if ($this->beforeRenderHook) {
            call_user_func($this->beforeRenderHook);
        }
        
        $context = $this->createViewContext();
        return $this->renderWithContext($context);
    }

    public function setBeforeRenderHook(callable $beforeRenderHook) {
        $this->beforeRenderHook = Closure::fromCallable($beforeRenderHook);
    }

    private function createViewContext(): array {
        return array_merge($this->parameters, [
            '_view' => new class ($this->parameters) implements \ArrayAccess {
                public function __construct(private readonly array $parameters) {
                    
                }

                public function __get(mixed $name) {
                    return is_string($name) ? $this->get($name) : null;
                }

                public function __invoke(mixed $arg) {
                    return $this->__get($arg);
                }

                public function get(string $key) {
                    return Arrays::getOrDefaultExists($this->parameters, $key);
                }

                #[\Override]
                public function offsetExists(mixed $offset): bool {
                    return array_key_exists($offset, $this->parameters);
                }

                #[\Override]
                public function offsetGet(mixed $offset): mixed {
                    return $this->__get($offset);
                }

                #[\Override]
                public function offsetUnset(mixed $offset): void {
                    throw new UnsupportedOperationException('View data is readonly');
                }

                #[\Override]
                public function offsetSet(mixed $offset, mixed $value): void {
                    throw new UnsupportedOperationException('View data is readonly');
                }
            }
        ]);
    }

    private function renderWithContext(array $context) {
        try {
            ob_start();
            extract($context);
            include $this->file; //NOSONAR
            return ob_get_clean();
        }
        catch (\Throwable $e) {
            throw new ViewRenderException("Unable to render view [$this->viewName]", 0, $e);
        }
    }
}
