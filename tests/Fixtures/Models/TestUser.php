<?php

namespace Lartisan\FacebookDataDeletion\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;

class TestUser extends Model
{
    protected $table = 'test_users';

    protected $guarded = [];

    protected $casts = [
        'deleted_from_facebook_at' => 'datetime',
    ];
}
