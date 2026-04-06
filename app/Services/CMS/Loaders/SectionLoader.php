<?php

namespace App\Services\CMS\Loaders;

use Illuminate\Http\Request;

abstract class SectionLoader
{
    protected $tenantId;

    public function __construct(int $tenantId)
    {
        $this->tenantId = $tenantId;
    }

    abstract public function load(string $tab, Request $request): array;
}
