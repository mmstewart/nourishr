<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'url', 'method', 'exception', 'message', 'input', 'stack_trace', 'ip'])]
class ErrorLog extends Model {}
