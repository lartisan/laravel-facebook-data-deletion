<?php

namespace Lartisan\FacebookDataDeletion\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Lartisan\FacebookDataDeletion\Contracts\DeletesFacebookDeletionSubjectData;
use Lartisan\FacebookDataDeletion\Models\FacebookDataDeletionRequest;
use Lartisan\FacebookDataDeletion\Tests\Fixtures\Models\TestUser;

class TestDeletionHandler implements DeletesFacebookDeletionSubjectData
{
    public function delete(FacebookDataDeletionRequest $request, ?Model $subject): void
    {
        if (! $subject instanceof TestUser) {
            return;
        }

        $subject->forceFill([
            'facebook_provider_id' => null,
            'deleted_from_facebook_at' => now(),
        ])->save();
    }
}
