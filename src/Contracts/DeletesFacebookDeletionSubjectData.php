<?php

namespace Lartisan\FacebookDataDeletion\Contracts;

use Illuminate\Database\Eloquent\Model;
use Lartisan\FacebookDataDeletion\Models\FacebookDataDeletionRequest;

interface DeletesFacebookDeletionSubjectData
{
    public function delete(FacebookDataDeletionRequest $request, ?Model $subject): void;
}
