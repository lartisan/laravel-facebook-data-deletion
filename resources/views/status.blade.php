<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Facebook Data Deletion Status</title>
    <style>
        body {
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            margin: 0;
            padding: 32px 16px;
            background: #f5f7fb;
            color: #111827;
        }

        .card {
            max-width: 720px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge--completed {
            background: #dcfce7;
            color: #166534;
        }

        .badge--failed {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge--processing {
            background: #fef3c7;
            color: #92400e;
        }

        h1 {
            margin: 16px 0 12px;
            font-size: 30px;
            line-height: 1.2;
        }

        p {
            margin: 0 0 16px;
            line-height: 1.6;
            color: #4b5563;
        }

        dl {
            margin: 24px 0 0;
            display: grid;
            grid-template-columns: minmax(180px, 220px) 1fr;
            gap: 12px 16px;
        }

        dt {
            font-weight: 600;
            color: #111827;
        }

        dd {
            margin: 0;
            color: #374151;
            word-break: break-word;
        }

        @media (max-width: 640px) {
            .card {
                padding: 24px;
            }

            dl {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
@php
    $status = $facebookDataDeletionRequest->status;
    $badgeClass = match ($status) {
        'completed' => 'badge badge--completed',
        'failed' => 'badge badge--failed',
        'processing' => 'badge badge--processing',
        default => 'badge',
    };

    $statusMessage = match ($status) {
        'completed' => 'Your data deletion request has been completed.',
        'failed' => 'We could not complete your data deletion request yet. Please contact support if the problem persists.',
        'processing' => 'Your data deletion request is currently being processed.',
        default => 'Your data deletion request has been received and is waiting to be processed.',
    };
@endphp
<div class="card">
    <span class="{{ $badgeClass }}">{{ $status }}</span>

    <h1>Facebook Data Deletion Request</h1>

    <p>{{ $statusMessage }}</p>

    <dl>
        <dt>Confirmation code</dt>
        <dd>{{ $facebookDataDeletionRequest->confirmation_code }}</dd>

        <dt>Status</dt>
        <dd>{{ ucfirst($facebookDataDeletionRequest->status) }}</dd>

        <dt>Request received at</dt>
        <dd>{{ optional($facebookDataDeletionRequest->requested_at)->toDayDateTimeString() ?? 'Not available' }}</dd>

        <dt>Completed at</dt>
        <dd>{{ optional($facebookDataDeletionRequest->completed_at)->toDayDateTimeString() ?? 'Pending' }}</dd>
    </dl>
</div>
</body>
</html>

