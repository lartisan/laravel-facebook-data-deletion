<?php

namespace Lartisan\FacebookDataDeletion\Contracts;

use Illuminate\Database\Eloquent\Model;

interface ResolvesFacebookDeletionSubject
{
    public function resolve(string $facebookUserId): ?Model;
}
