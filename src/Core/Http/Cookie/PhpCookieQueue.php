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
        int $seconds,
        ?CookieOptions $options = null
    ): void {
        if ($seconds < 0) {
            $this->enqueueDestroy($name, $options);
            return;
        }
        $timeCallback = fn() => $seconds !== 0 ? time() + $seconds : 0;
        $value = $this->cookieWriter->write(trim($value));
        $command = fn() => setcookie($name, $value, static::makeOptions($timeCallback(), $options));
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
