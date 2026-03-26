<?php

namespace Lartisan\FacebookDataDeletion\Models;

use Illuminate\Database\Eloquent\Model;

class FacebookDataDeletionRequest extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $table = 'facebook_data_deletion_requests';

    protected $fillable = [
        'confirmation_code',
        'facebook_user_id',
        'subject_type',
        'subject_id',
        'status',
        'user_found',
        'signed_request_payload',
        'requested_at',
        'completed_at',
        'failure_reason',
    ];

    protected $casts = [
        'user_found' => 'boolean',
        'signed_request_payload' => 'array',
        'requested_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function markAsProcessing(): void
    {
        $this->forceFill([
            'status' => self::STATUS_PROCESSING,
            'failure_reason' => null,
        ])->save();
    }

    public function markAsCompleted(): void
    {
        $this->forceFill([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'failure_reason' => null,
        ])->save();
    }

    public function markAsFailed(string $failureReason): void
    {
        $this->forceFill([
            'status' => self::STATUS_FAILED,
            'failure_reason' => $failureReason,
        ])->save();
    }

    public function resolveSubjectModel(): ?Model
    {
        if (! is_string($this->subject_type) || $this->subject_type === '') {
            return null;
        }

        if (! class_exists($this->subject_type) || ! is_subclass_of($this->subject_type, Model::class)) {
            return null;
        }

        /** @var Model $subject */
        $subject = app($this->subject_type);

        return $subject->newQuery()
            ->where($subject->getKeyName(), $this->subject_id)
            ->first();
    }
}
