<?php

namespace Cronbeat;

class RedirectException extends \Exception {
    private array $headers;

    public function __construct(
        array $headers,
        string $message = 'Redirect',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->headers = $headers;
    }

    public function getHeaders(): array {
        return $this->headers;
    }
}
