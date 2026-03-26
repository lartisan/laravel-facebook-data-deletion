<?php

namespace Lartisan\FacebookDataDeletion\Support;

use Illuminate\Database\Eloquent\Model;
use Lartisan\FacebookDataDeletion\Contracts\ResolvesFacebookDeletionSubject;

class NullDeletionSubjectResolver implements ResolvesFacebookDeletionSubject
{
    public function resolve(string $facebookUserId): ?Model
    {
        return null;
    }
}
