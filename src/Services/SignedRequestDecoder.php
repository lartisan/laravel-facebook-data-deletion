<?php

namespace Lartisan\FacebookDataDeletion\Services;

use Illuminate\Contracts\Config\Repository;
use JsonException;
use Lartisan\FacebookDataDeletion\Exceptions\FacebookSignedRequestException;

class SignedRequestDecoder
{
    public function __construct(
        protected Repository $config,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function decode(string $signedRequest): array
    {
        $segments = explode('.', $signedRequest, 2);

        if (count($segments) !== 2) {
            throw new FacebookSignedRequestException('Facebook signed_request is malformed.');
        }

        [$encodedSignature, $encodedPayload] = $segments;
        $decodedSignature = $this->base64UrlDecode($encodedSignature);
        $decodedPayload = $this->base64UrlDecode($encodedPayload);

        try {
            $payload = json_decode($decodedPayload, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new FacebookSignedRequestException('Facebook signed_request payload is not valid JSON.');
        }

        if (! is_array($payload)) {
            throw new FacebookSignedRequestException('Facebook signed_request payload is invalid.');
        }

        if (strtoupper((string) data_get($payload, 'algorithm')) !== 'HMAC-SHA256') {
            throw new FacebookSignedRequestException('Facebook signed_request algorithm is invalid.');
        }

        $appSecret = $this->config->get('facebook-data-deletion.app_secret');

        if (! is_string($appSecret) || $appSecret === '') {
            throw new FacebookSignedRequestException('Facebook app secret is not configured.', 500);
        }

        $expectedSignature = hash_hmac('sha256', $encodedPayload, $appSecret, true);

        if (! hash_equals($expectedSignature, $decodedSignature)) {
            throw new FacebookSignedRequestException('Facebook signed_request signature is invalid.', 403);
        }

        return $payload;
    }

    private function base64UrlDecode(string $value): string
    {
        $normalizedValue = strtr($value, '-_', '+/');
        $paddingLength = strlen($normalizedValue) % 4;

        if ($paddingLength !== 0) {
            $normalizedValue .= str_repeat('=', 4 - $paddingLength);
        }

        $decodedValue = base64_decode($normalizedValue, true);

        if ($decodedValue === false) {
            throw new FacebookSignedRequestException('Facebook signed_request contains invalid base64 data.');
        }

        return $decodedValue;
    }
}
