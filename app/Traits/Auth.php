<?php

namespace App\Traits;

use Hash;
use Str;

trait Auth
{
  public function generateSignature() {
    return Str::random(40);
  }

  public function generateSignatures(string $type) {
    $signature = $this->generateSignature();
    $uuid = Str::uuid()->toString();
    $calculatedHash = Hash::make(
        $uuid .
            ':' . config('speedi.system.' . $type . '_salt_1') .
            ':' . config('speedi.system.' . $type . '_salt_2')
    );

    return [ 'signature' => $signature, 'hash' => $calculatedHash];
  }
}