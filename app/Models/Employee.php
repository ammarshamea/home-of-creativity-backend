<?php

namespace App\Models;

use App\Enums\EmployeeProfession;
use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    /** @use HasFactory<EmployeeFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'phone',
        'email',
        'telegram_user_id',
        'clickup_user_id',
        'profession',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'profession' => EmployeeProfession::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<Employee>  $query
     * @return Builder<Employee>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
