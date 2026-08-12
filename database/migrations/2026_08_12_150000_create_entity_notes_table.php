<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entity_notes', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 30);
            $table->unsignedBigInteger('entity_id');
            $table->text('body');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(
                ['entity_type', 'entity_id', 'created_at'],
                'entity_notes_entity_history_index'
            );
        });

        $this->migrateExistingNotes();
    }

    public function down(): void
    {
        Schema::dropIfExists('entity_notes');
    }

    private function migrateExistingNotes(): void
    {
        $now = now();

        DB::table('contacts')
            ->whereNotNull('description')
            ->where('description', '<>', '')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($now) {
                foreach ($rows as $row) {
                    DB::table('entity_notes')->insert([
                        'entity_type' => 'contact',
                        'entity_id' => $row->id,
                        'body' => $row->description,
                        'created_by' => $row->created_by,
                        'created_at' => $row->created_at ?? $now,
                        'updated_at' => $row->created_at ?? $now,
                    ]);
                }
            });

        DB::table('devices')
            ->whereNotNull('description')
            ->where('description', '<>', '')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($now) {
                foreach ($rows as $row) {
                    DB::table('entity_notes')->insert([
                        'entity_type' => 'device',
                        'entity_id' => $row->id,
                        'body' => $row->description,
                        'created_by' => $row->created_by,
                        'created_at' => $row->created_at ?? $now,
                        'updated_at' => $row->created_at ?? $now,
                    ]);
                }
            });

        DB::table('purchases')
            ->whereNotNull('notes')
            ->where('notes', '<>', '')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($now) {
                foreach ($rows as $row) {
                    DB::table('entity_notes')->insert([
                        'entity_type' => 'purchase',
                        'entity_id' => $row->id,
                        'body' => $row->notes,
                        'created_by' => $row->created_by,
                        'created_at' => $row->created_at ?? $now,
                        'updated_at' => $row->created_at ?? $now,
                    ]);
                }
            });

        DB::table('sales')
            ->whereNotNull('notes')
            ->where('notes', '<>', '')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($now) {
                foreach ($rows as $row) {
                    DB::table('entity_notes')->insert([
                        'entity_type' => 'sale',
                        'entity_id' => $row->id,
                        'body' => $row->notes,
                        'created_by' => $row->created_by,
                        'created_at' => $row->created_at ?? $now,
                        'updated_at' => $row->created_at ?? $now,
                    ]);
                }
            });

        DB::table('installments')
            ->whereNotNull('notes')
            ->where('notes', '<>', '')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($now) {
                foreach ($rows as $row) {
                    DB::table('entity_notes')->insert([
                        'entity_type' => 'installment',
                        'entity_id' => $row->id,
                        'body' => $row->notes,
                        'created_by' => null,
                        'created_at' => $row->created_at ?? $now,
                        'updated_at' => $row->created_at ?? $now,
                    ]);
                }
            });
    }
};
