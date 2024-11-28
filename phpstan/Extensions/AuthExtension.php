<?php

declare(strict_types=1);

namespace App\PHPStan;

use Illuminate\Support\Facades\Auth;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\Native\NativeMethodReflection;
use PHPStan\Reflection\Php\PhpMethodReflection;
use PHPStan\Type\StringType;

class AuthExtension implements MethodsClassReflectionExtension
{
    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        // Add support for the "token" method in the Auth facade
        return $classReflection->getName() === Auth::class && $methodName === 'token';
    }

    public function getMethod(ClassReflection $classReflection, string $methodName): NativeMethodReflection
    {
        // Define the signature for the "token" method
        return new PhpMethodReflection(
            $methodName,
            $classReflection,
            [],
            new StringType(),
            null
        );
    }
}
