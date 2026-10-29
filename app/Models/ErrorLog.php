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

    protected function casts(): array
    {
        return [
            'input' => 'array',
            'stack_trace' => 'array',
        ];
    }

    /**
     * Save an exception without ever throwing. A failure here must not turn
     * into another reported exception, or reporting loops until the request
     * crashes (which is what broke Google sign-in on Vercel).
     */
    public static function record(\Throwable $e): void
    {
        static $recording = false;

        if ($recording) {
            return;
        }

        $recording = true;

        try {
            static::create([
                'user_id' => auth()->id(),
                'url' => mb_substr(request()->fullUrl(), 0, 255),
                'method' => request()->method(),
                'exception' => get_class($e),
                'message' => mb_substr($e->getMessage(), 0, 5000),
                'input' => request()->except(['password', 'password_confirmation', 'current_password', 'code']),
                // File, line, and function only: arguments can hold secrets or objects that can't be stored.
                'stack_trace' => collect($e->getTrace())->take(10)->map(fn ($frame) => [
                    'file' => $frame['file'] ?? null,
                    'line' => $frame['line'] ?? null,
                    'function' => isset($frame['class']) ? $frame['class'] . '::' . $frame['function'] : ($frame['function'] ?? null),
                ])->all(),
                'ip' => request()->ip(),
            ]);
        } catch (\Throwable $loggingFailure) {
            error_log('ErrorLog::record failed: ' . $loggingFailure->getMessage() . ' | original: ' . $e->getMessage());
        } finally {
            $recording = false;
        }
    }
}
