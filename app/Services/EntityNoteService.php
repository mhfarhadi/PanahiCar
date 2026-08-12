<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class EntityNoteService
{
    public static function add(
        string $entityType,
        int $entityId,
        ?string $body,
        ?int $createdBy,
        CarbonInterface|string|null $createdAt = null
    ): void {
        $body = trim((string) $body);

        if ($body === '') {
            return;
        }

        $timestamp = $createdAt ?: now();

        DB::table('entity_notes')->insert([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'body' => $body,
            'created_by' => $createdBy,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }
}
