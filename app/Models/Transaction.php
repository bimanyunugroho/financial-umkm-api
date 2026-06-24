<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Carbon\Carbon;

class Transaction extends Model
{
    use HasFactory, HasUuids, SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id',
        'category_id',
        'type',
        'amount',
        'description',
        'date',
        'payment_method',
        'notes',
        'reference_number',
    ];

    protected function casts(): array
    {
        return [
            'type'           => TransactionType::class,
            'payment_method' => PaymentMethod::class,
            'amount'         => 'float',
            'date'           => 'date',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['type', 'amount', 'description', 'date', 'category_id', 'payment_method'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $e) => "Transaction {$e}");
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeIncome($query)
    {
        return $query->where('type', TransactionType::Income);
    }

    public function scopeExpense($query)
    {
        return $query->where('type', TransactionType::Expense);
    }

    public function scopeInPeriod($query, Carbon $from, Carbon $to)
    {
        return $query->whereBetween('date', [$from->startOfDay(), $to->endOfDay()]);
    }

    public function scopeInMonth($query, int $year, int $month)
    {
        return $query->whereYear('date', $year)->whereMonth('date', $month);
    }
}
