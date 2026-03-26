<?php

namespace Lartisan\FacebookDataDeletion\Support;

use Illuminate\Database\Eloquent\Model;
use Lartisan\FacebookDataDeletion\Contracts\DeletesFacebookDeletionSubjectData;
use Lartisan\FacebookDataDeletion\Models\FacebookDataDeletionRequest;

class NullDeletionHandler implements DeletesFacebookDeletionSubjectData
{
    public function delete(FacebookDataDeletionRequest $request, ?Model $subject): void {}
}
