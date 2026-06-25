<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable, LogsActivity;

    protected $fillable = [
        'name',
        'email',
        'password',
        'business_name',
        'business_type',
        'phone',
        'address'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'business_name', 'business_type'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName) => "User {$eventName}");
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function aiInsightLogs(): HasMany
    {
        return $this->hasMany(AiInsightLog::class);
    }

    public function getTransactionsSummary(Carbon $from, Carbon $to): array
    {
        return $this->transactions()
            ->whereBetween('date', [$from, $to])
            ->selectRaw("
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END)  AS total_income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS total_expense,
                COUNT(*) AS total_transactions
            ")
            ->first()
            ->toArray();
    }
}
