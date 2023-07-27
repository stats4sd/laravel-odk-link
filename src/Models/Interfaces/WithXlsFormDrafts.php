<?php

namespace Stats4sd\OdkLink\Models\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Stats4sd\OdkLink\Services\OdkLinkService;

interface WithXlsFormDrafts
{

    // If it can have a draft, it must have an owner
    public function owner(): MorphTo;

    public function getDraftQrCodeStringAttribute(): ?string;

    public function deployDraft(OdkLinkService $service): void;


    public function getOdkLink(): ?string;
}
