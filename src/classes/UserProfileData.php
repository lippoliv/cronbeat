<?php

namespace Cronbeat;

final class UserProfileData {
    public function __construct(
        private string $username,
        private ?string $name,
        private ?string $email,
    ) {}

    public function getUsername(): string {
        return $this->username;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function getEmail(): ?string {
        return $this->email;
    }
}
