<?php

declare(strict_types=1);

namespace Larastan\Larastan\Types;

use Illuminate\View\Factory;
use PHPStan\Type\AcceptsResult;
use PHPStan\Type\CompoundType;
use PHPStan\Type\IsSuperTypeOfResult;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

use function count;
use function view;

/**
 * The custom 'view-string' type class. It's a subset of the string type. Every string that passes the
 * view()->exists($string) test is a valid view-string type.
 */
class ViewStringType extends StringType
{
    public function describe(VerbosityLevel $level): string
    {
        return 'view-string';
    }

    public function accepts(Type $type, bool $strictTypes): AcceptsResult
    {
        if ($type instanceof CompoundType) {
            return $type->isAcceptedBy($this, $strictTypes);
        }

        $constantStrings = $type->getConstantStrings();

        if (count($constantStrings) === 1) {
            /** @var Factory $view */
            $view = view();

            return AcceptsResult::createFromBoolean($view->exists($constantStrings[0]->getValue()));
        }

        if ($type instanceof self) {
            return AcceptsResult::createYes();
        }

        if ($type->isString()->yes()) {
            return AcceptsResult::createMaybe();
        }

        return AcceptsResult::createNo();
    }

    public function isSuperTypeOf(Type $type): IsSuperTypeOfResult
    {
        $constantStrings = $type->getConstantStrings();

        if (count($constantStrings) === 1) {
            /** @var Factory $view */
            $view = view();

            return IsSuperTypeOfResult::createFromBoolean($view->exists($constantStrings[0]->getValue()));
        }

        if ($type instanceof self) {
            return IsSuperTypeOfResult::createYes();
        }

        if ($type->isString()->yes()) {
            return IsSuperTypeOfResult::createMaybe();
        }

        if ($type instanceof CompoundType) {
            return $type->isSubTypeOf($this);
        }

        return IsSuperTypeOfResult::createNo();
    }

    /** @param  mixed[] $properties */
    public static function __set_state(array $properties): Type
    {
        return new self();
    }
}
