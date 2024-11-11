<?php
namespace App\Core\Http\Cookie;

class PhpCookieQueue implements CookieQueue
{
    /**
     * @var array<\Closure():mixed>
     */
    private array $queue = [];

    public function __construct(private readonly CookieWriter $cookieWriter) {
        
    }
    
    #[\Override]
    public function enqueueSend(
        string $name,
        string $value,
        int $expires,
        ?CookieOptions $options = null
    ): void {
        if ($expires < 0) {
            $this->enqueueDestroy($name, $options);
            return;
        }
        $value = $this->cookieWriter->write(trim($value));
        $command = fn() => setcookie($name, $value, static::makeOptions($expires, $options));
        $this->queue[] = $command;
    }

    #[\Override]
    public function enqueueDestroy(string $name, ?CookieOptions $options = null): void {
        $command = fn() => setcookie($name, '', static::makeOptions(time() - 1, $options));
        $this->queue[] = $command;
    }

    private static function makeOptions(int $expires, ?CookieOptions $options) {
        $options = $options?->toArray() ?? [];
        return [...$options, 'expires' => $expires];
    }

    #[\Override]
    public function dispatch(): void {
        foreach ($this->queue as $command) {
            call_user_func($command);
        }
        $this->queue = [];
    }
}
