<?php

namespace Lartisan\FacebookDataDeletion\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Lartisan\FacebookDataDeletion\Contracts\DeletesFacebookDeletionSubjectData;
use Lartisan\FacebookDataDeletion\Models\FacebookDataDeletionRequest;
use Throwable;

class ProcessFacebookDataDeletionRequest implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $facebookDataDeletionRequestId,
    ) {}

    public function handle(DeletesFacebookDeletionSubjectData $deletionHandler): void
    {
        /** @var class-string<FacebookDataDeletionRequest> $modelClass */
        $modelClass = config('facebook-data-deletion.model', FacebookDataDeletionRequest::class);

        $facebookDataDeletionRequest = $modelClass::query()->find($this->facebookDataDeletionRequestId);

        if ($facebookDataDeletionRequest === null) {
            return;
        }

        $facebookDataDeletionRequest->markAsProcessing();

        try {
            $deletionHandler->delete(
                $facebookDataDeletionRequest,
                $facebookDataDeletionRequest->resolveSubjectModel(),
            );

            $facebookDataDeletionRequest->markAsCompleted();
        } catch (Throwable $throwable) {
            report($throwable);

            $facebookDataDeletionRequest->markAsFailed($throwable->getMessage());

            throw $throwable;
        }
    }
}
