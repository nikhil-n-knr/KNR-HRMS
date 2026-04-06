<?php

namespace App\Services\CRM\Loaders;

use Illuminate\Http\Request;

abstract class SectionLoader
{
    protected $tenantId;

    public function __construct($tenantId)
    {
        $this->tenantId = $tenantId;
    }

    /**
     * Load data for a specific tab
     */
    abstract public function load(string $tab, Request $request): array;
}
