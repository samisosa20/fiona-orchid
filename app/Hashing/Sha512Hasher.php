<?php

namespace App\Hashing;

use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Hashing\AbstractHasher;

class Sha512Hasher extends AbstractHasher implements Hasher
{
    protected $salt = '';

    public function __construct(array $options = [])
    {
        $this->salt = $options['salt'] ?? $this->salt;
    }

    public function make($value, array $options = []): string
    {
        return hash('sha512', $this->salt . $value);
    }

    public function check($value, $hashedValue, array $options = []): bool
    {
        return $this->make($value) === $hashedValue;
    }

    public function needsRehash($hashedValue, array $options = []): bool
    {
        return false;
    }
}
