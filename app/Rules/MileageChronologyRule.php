<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class MileageChronologyRule implements ValidationRule, DataAwareRule
{
    protected array $data = [];

    public function __construct(
        private readonly Builder|Relation $query,
        private readonly ?string $excludeId = null,
    ) {}

    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $performedAt = $this->data['performed_at'] ?? null;
        if (!$performedAt) {
            return;
        }

        $mileage = (int) $value;

        $base = $this->excludeId
            ? (clone $this->query)->where('id', '!=', $this->excludeId)
            : clone $this->query;

        $conflictBefore = (clone $base)
            ->where('performed_at', '<', $performedAt)
            ->where('mileage', '>', $mileage)
            ->orderBy('performed_at', 'desc')
            ->first();

        if ($conflictBefore) {
            $date = Carbon::parse($conflictBefore->performed_at)->format('d/m/Y');
            $km   = number_format($conflictBefore->mileage, 0, ',', ' ');
            $fail("Kilométrage incohérent : l'intervention du {$date} a déjà {$km} km.");
            return;
        }

        $conflictAfter = (clone $base)
            ->where('performed_at', '>', $performedAt)
            ->where('mileage', '<', $mileage)
            ->orderBy('performed_at', 'asc')
            ->first();

        if ($conflictAfter) {
            $date = Carbon::parse($conflictAfter->performed_at)->format('d/m/Y');
            $km   = number_format($conflictAfter->mileage, 0, ',', ' ');
            $fail("Kilométrage incohérent : l'intervention du {$date} n'a que {$km} km.");
        }
    }
}
