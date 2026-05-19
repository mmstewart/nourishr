<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    protected $fillable = [
        'user_id',
        'url',
        'method',
        'exception',
        'message',
        'input',
        'stack_trace',
        'ip'
    ];
}
