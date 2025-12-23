<?php

namespace Cronbeat;

class RedirectException extends \Exception {
    /** @var array<string, string> */
    private array $headers;

    /**
     * @param array<string, string> $headers
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        array $headers,
        string $message = 'Redirect',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->headers = $headers;
    }

    /** @return array<string, string> */
    public function getHeaders(): array {
        return $this->headers;
    }
}
