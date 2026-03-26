<?php

namespace Lartisan\FacebookDataDeletion\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Lartisan\FacebookDataDeletion\Contracts\ResolvesFacebookDeletionSubject;
use Lartisan\FacebookDataDeletion\Exceptions\FacebookSignedRequestException;
use Lartisan\FacebookDataDeletion\Http\Requests\FacebookDataDeletionRequest as FacebookDataDeletionFormRequest;
use Lartisan\FacebookDataDeletion\Jobs\ProcessFacebookDataDeletionRequest;
use Lartisan\FacebookDataDeletion\Models\FacebookDataDeletionRequest;
use Lartisan\FacebookDataDeletion\Services\ConfirmationCodeGenerator;
use Lartisan\FacebookDataDeletion\Services\SignedRequestDecoder;

class FacebookDataDeletionController extends Controller
{
    public function __construct(
        protected SignedRequestDecoder $signedRequestDecoder,
        protected ConfirmationCodeGenerator $confirmationCodeGenerator,
        protected ResolvesFacebookDeletionSubject $deletionSubjectResolver,
    ) {}

    public function handle(FacebookDataDeletionFormRequest $request): JsonResponse
    {
        try {
            $payload = $this->signedRequestDecoder->decode($request->string('signed_request')->toString());
        } catch (FacebookSignedRequestException $exception) {
            $this->abortJson($exception->statusCode(), $exception->getMessage());
        }

        $facebookUserId = (string) data_get($payload, 'user_id');

        if ($facebookUserId === '') {
            $this->abortJson(422, 'Facebook user_id is missing from the signed_request payload.');
        }

        $subject = $this->deletionSubjectResolver->resolve($facebookUserId);

        /** @var class-string<FacebookDataDeletionRequest> $modelClass */
        $modelClass = config('facebook-data-deletion.model', FacebookDataDeletionRequest::class);

        $facebookDataDeletionRequest = $modelClass::query()->create([
            'confirmation_code' => $this->confirmationCodeGenerator->generate($modelClass),
            'facebook_user_id' => $facebookUserId,
            'subject_type' => $subject instanceof Model ? $subject::class : null,
            'subject_id' => $this->resolveSubjectId($subject),
            'status' => FacebookDataDeletionRequest::STATUS_PENDING,
            'user_found' => $subject !== null,
            'signed_request_payload' => $payload,
            'requested_at' => now(),
        ]);

        $job = new ProcessFacebookDataDeletionRequest($facebookDataDeletionRequest->getKey());

        if (is_string(config('facebook-data-deletion.queue.connection')) && config('facebook-data-deletion.queue.connection') !== '') {
            $job->onConnection((string) config('facebook-data-deletion.queue.connection'));
        }

        if (is_string(config('facebook-data-deletion.queue.name')) && config('facebook-data-deletion.queue.name') !== '') {
            $job->onQueue((string) config('facebook-data-deletion.queue.name'));
        }

        dispatch($job);

        return response()->json([
            'url' => route($this->routeName('status'), [
                'confirmationCode' => $facebookDataDeletionRequest->confirmation_code,
            ]),
            'confirmation_code' => $facebookDataDeletionRequest->confirmation_code,
        ]);
    }

    public function status(Request $request, string $confirmationCode): JsonResponse|View
    {
        /** @var class-string<FacebookDataDeletionRequest> $modelClass */
        $modelClass = config('facebook-data-deletion.model', FacebookDataDeletionRequest::class);

        $facebookDataDeletionRequest = $modelClass::query()
            ->where('confirmation_code', $confirmationCode)
            ->firstOrFail();

        if (! $request->wantsJson()) {
            return view(config('facebook-data-deletion.view', 'facebook-data-deletion::status'), [
                'facebookDataDeletionRequest' => $facebookDataDeletionRequest,
            ]);
        }

        return response()->json([
            'confirmation_code' => $facebookDataDeletionRequest->confirmation_code,
            'status' => $facebookDataDeletionRequest->status,
            'user_found' => $facebookDataDeletionRequest->user_found,
            'requested_at' => $facebookDataDeletionRequest->requested_at?->toIso8601String(),
            'completed_at' => $facebookDataDeletionRequest->completed_at?->toIso8601String(),
        ]);
    }

    private function abortJson(int $status, string $message): never
    {
        throw new HttpResponseException(
            response()->json([
                'message' => $message,
            ], $status),
        );
    }

    private function routeName(string $suffix): string
    {
        $prefix = trim((string) config('facebook-data-deletion.route.name_prefix', 'facebook-data-deletion'), '.');

        return $prefix !== '' ? $prefix.'.'.$suffix : $suffix;
    }

    private function resolveSubjectId(?Model $subject): ?string
    {
        if ($subject === null) {
            return null;
        }

        return (string) $subject->getKey();
    }
}
