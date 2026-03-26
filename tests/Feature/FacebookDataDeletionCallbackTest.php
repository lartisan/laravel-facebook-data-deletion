<?php

use Lartisan\FacebookDataDeletion\Models\FacebookDataDeletionRequest;
use Lartisan\FacebookDataDeletion\Tests\Fixtures\Models\TestUser;

it('accepts a valid callback and processes the resolved subject', function () {
    $subject = TestUser::query()->create([
        'name' => 'Jane Doe',
        'facebook_provider_id' => 'app-scoped-id-123',
    ]);

    $response = $this->postJson('/api/facebook/data-deletion', [
        'signed_request' => facebookSignedRequest([
            'algorithm' => 'HMAC-SHA256',
            'issued_at' => now()->timestamp,
            'user_id' => 'app-scoped-id-123',
        ], 'test-facebook-secret'),
    ]);

    $response
        ->assertSuccessful()
        ->assertJsonStructure([
            'url',
            'confirmation_code',
        ]);

    $facebookDataDeletionRequest = FacebookDataDeletionRequest::query()->firstOrFail();
    $statusPath = parse_url($response->json('url'), PHP_URL_PATH);

    expect($facebookDataDeletionRequest->status)->toBe(FacebookDataDeletionRequest::STATUS_COMPLETED)
        ->and($facebookDataDeletionRequest->confirmation_code)->toMatch('/^[A-Z0-9]{32}$/')
        ->and($facebookDataDeletionRequest->user_found)->toBeTrue()
        ->and($facebookDataDeletionRequest->subject_type)->toBe(TestUser::class)
        ->and($facebookDataDeletionRequest->subject_id)->toBe((string) $subject->getKey());

    $subject->refresh();

    expect($subject->facebook_provider_id)->toBeNull()
        ->and($subject->deleted_from_facebook_at)->not->toBeNull();

    $this->get($statusPath)
        ->assertSuccessful()
        ->assertSeeText('Facebook Data Deletion Request')
        ->assertSeeText($facebookDataDeletionRequest->confirmation_code);

    $this->getJson($statusPath)
        ->assertSuccessful()
        ->assertJson([
            'confirmation_code' => $facebookDataDeletionRequest->confirmation_code,
            'status' => FacebookDataDeletionRequest::STATUS_COMPLETED,
            'user_found' => true,
        ]);
});

it('rejects callbacks with an invalid signature', function () {
    $response = $this->postJson('/api/facebook/data-deletion', [
        'signed_request' => facebookSignedRequest([
            'algorithm' => 'HMAC-SHA256',
            'issued_at' => now()->timestamp,
            'user_id' => 'invalid-app-scoped-id',
        ], 'different-secret'),
    ]);

    $response->assertForbidden();

    $this->assertDatabaseCount('facebook_data_deletion_requests', 0);
});

it('creates a completed tracking record even when no subject is found', function () {
    $response = $this->postJson('/api/facebook/data-deletion', [
        'signed_request' => facebookSignedRequest([
            'algorithm' => 'HMAC-SHA256',
            'issued_at' => now()->timestamp,
            'user_id' => 'missing-app-scoped-id',
        ], 'test-facebook-secret'),
    ]);

    $response->assertSuccessful();

    $facebookDataDeletionRequest = FacebookDataDeletionRequest::query()->firstOrFail();

    expect($facebookDataDeletionRequest->status)->toBe(FacebookDataDeletionRequest::STATUS_COMPLETED)
        ->and($facebookDataDeletionRequest->user_found)->toBeFalse()
        ->and($facebookDataDeletionRequest->facebook_user_id)->toBe('missing-app-scoped-id');
});

it('validates the signed_request payload', function () {
    $this->postJson('/api/facebook/data-deletion', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['signed_request']);
});

function facebookSignedRequest(array $payload, string $appSecret): string
{
    $encodedPayload = rtrim(
        strtr(base64_encode((string) json_encode($payload)), '+/', '-_'),
        '='
    );

    $signature = hash_hmac('sha256', $encodedPayload, $appSecret, true);
    $encodedSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

    return $encodedSignature.'.'.$encodedPayload;
}
