<?php
namespace App\Core\Template;

use App\Core\Exceptions\ViewRenderException;
use App\Core\Template\Contracts\RenderableView;
use App\Utils\Arrays;

class HurrycanView implements RenderableView
{
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
        $context = $this->createViewContext();
        return $this->renderWithContext($context);
    }

    private function createViewContext(): array {
        return array_merge($this->parameters, array(
            '_view' => new class ($this->parameters) {
                public function __construct(private array $parameters)
                {
                    
                }

                public function get(string $key)
                {
                    return Arrays::getOrDefaultExists($this->parameters, $key);
                }
            }
        ));
    }

    private function renderWithContext(array $context) {
        try {
            ob_start();
            extract($context);
            include $this->file; //NOSONAR
            return ob_get_clean();
        }
        catch (\Exception $e) {
            throw new ViewRenderException("Unable to render view [$this->viewName]", 0, $e);
        }
    }
}
