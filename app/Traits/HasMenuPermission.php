<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasMenuPermission
{
    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermission(static::menuPermissionKey()) ?? false;
    }

    public static function menuPermissionKey(): string
    {
        $class = class_basename(static::class);
        if (str_ends_with($class, 'Resource')) {
            return 'resource_' . Str::snake(str_replace('Resource', '', $class));
        }
        return 'page_' . Str::snake($class);
    }

    public static function menuPermissionLabel(): string
    {
        return static::getNavigationLabel() ?? class_basename(static::class);
    }
}
