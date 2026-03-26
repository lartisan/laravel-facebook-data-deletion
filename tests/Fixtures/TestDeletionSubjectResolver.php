<?php

namespace Lartisan\FacebookDataDeletion\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Lartisan\FacebookDataDeletion\Contracts\ResolvesFacebookDeletionSubject;
use Lartisan\FacebookDataDeletion\Tests\Fixtures\Models\TestUser;

class TestDeletionSubjectResolver implements ResolvesFacebookDeletionSubject
{
    public function resolve(string $facebookUserId): ?Model
    {
        return TestUser::query()
            ->where('facebook_provider_id', $facebookUserId)
            ->first();
    }
}
