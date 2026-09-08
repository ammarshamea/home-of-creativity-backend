<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table): void {
            if (! Schema::hasColumn('requests', 'uuid')) {
                $table->uuid('uuid')->nullable()->after('id');
            }
            if (! Schema::hasColumn('requests', 'work_type')) {
                $table->string('work_type')->nullable()->after('source');
            }
            if (! Schema::hasColumn('requests', 'execution_status')) {
                $table->string('execution_status')->nullable()->after('status');
            }
            if (! Schema::hasColumn('requests', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('paid_at');
            }
            if (! Schema::hasColumn('requests', 'gemini_status')) {
                $table->string('gemini_status')->nullable()->after('ai_analysis');
            }
            if (! Schema::hasColumn('requests', 'gemini_attempts')) {
                $table->unsignedSmallInteger('gemini_attempts')->default(0)->after('gemini_status');
            }
            if (! Schema::hasColumn('requests', 'gemini_error')) {
                $table->text('gemini_error')->nullable()->after('gemini_attempts');
            }
            if (! Schema::hasColumn('requests', 'gemini_processed_at')) {
                $table->timestamp('gemini_processed_at')->nullable()->after('gemini_error');
            }
            if (! Schema::hasColumn('requests', 'aggregate_version')) {
                $table->unsignedInteger('aggregate_version')->default(0)->after('gemini_processed_at');
            }
            if (! Schema::hasColumn('requests', 'quotation_amount')) {
                $table->decimal('quotation_amount', 12, 2)->nullable()->after('aggregate_version');
            }
            if (! Schema::hasColumn('requests', 'quotation_notes')) {
                $table->text('quotation_notes')->nullable()->after('quotation_amount');
            }
        });

        if (Schema::hasColumn('requests', 'uuid')) {
            DB::table('requests')->whereNull('uuid')->orderBy('id')->each(function ($row): void {
                DB::table('requests')->where('id', $row->id)->update(['uuid' => (string) Str::uuid()]);
            });

            try {
                Schema::table('requests', function (Blueprint $table): void {
                    $table->unique('uuid');
                });
            } catch (Throwable) {
                // Unique index already exists.
            }
        }

        if (! Schema::hasColumn('department_briefs', 'type')) {
            Schema::table('department_briefs', function (Blueprint $table): void {
                $table->string('type')->nullable()->after('department');
            });
        }

        if (Schema::hasTable('request_status_histories') && ! Schema::hasTable('request_status_history')) {
            Schema::rename('request_status_histories', 'request_status_history');
        }

        if (Schema::hasTable('request_status_history') && ! Schema::hasColumn('request_status_history', 'request_id')) {
            Schema::drop('request_status_history');
        }

        if (! Schema::hasTable('request_status_history')) {
            Schema::create('request_status_history', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('request_id')->constrained()->cascadeOnDelete();
                $table->string('from_status')->nullable();
                $table->string('to_status');
                $table->string('actor')->nullable();
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('integration_events') && ! Schema::hasColumn('integration_events', 'event_uuid')) {
            Schema::drop('integration_events');
        }

        if (! Schema::hasTable('integration_events')) {
            Schema::create('integration_events', function (Blueprint $table): void {
                $table->id();
                $table->uuid('event_uuid')->unique();
                $table->string('event_type');
                $table->uuid('request_uuid');
                $table->string('request_number');
                $table->uuid('correlation_id');
                $table->unsignedInteger('aggregate_version')->default(0);
                $table->json('payload')->nullable();
                $table->string('status')->default('pending');
                $table->unsignedSmallInteger('attempts')->default(0);
                $table->text('last_error')->nullable();
                $table->timestamp('next_retry_at')->nullable();
                $table->timestamp('dispatched_at')->nullable();
                $table->timestamps();
                $table->index(['status', 'next_retry_at']);
                $table->index('request_uuid');
            });
        }

        if (! Schema::hasTable('clickup_tasks')) {
            Schema::create('clickup_tasks', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('request_id')->constrained()->cascadeOnDelete();
                $table->foreignId('brief_id')->nullable()->constrained('department_briefs')->nullOnDelete();
                $table->string('task_type');
                $table->string('clickup_task_id')->nullable();
                $table->string('clickup_list_id')->nullable();
                $table->string('clickup_user_id')->nullable();
                $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
                $table->string('clickup_url')->nullable();
                $table->string('status')->nullable();
                $table->string('integration_key')->unique();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('quotations')) {
            Schema::create('quotations', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('request_id')->constrained()->cascadeOnDelete();
                $table->unsignedSmallInteger('version');
                $table->decimal('amount', 12, 2);
                $table->text('notes')->nullable();
                $table->string('pdf_path')->nullable();
                $table->string('telegram_file_id')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
                $table->unique(['request_id', 'version']);
            });
        }

        if (! Schema::hasTable('quotation_decisions')) {
            Schema::create('quotation_decisions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('request_id')->constrained()->cascadeOnDelete();
                $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
                $table->string('decision');
                $table->text('reason')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('request_id')->constrained()->cascadeOnDelete();
                $table->string('invoice_number')->unique();
                $table->string('pdf_path')->nullable();
                $table->string('telegram_file_id')->nullable();
                $table->timestamp('issued_at')->nullable();
                $table->string('payment_method')->nullable();
                $table->string('odoo_invoice_id')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('support_messages') && ! Schema::hasColumn('support_messages', 'message')) {
            Schema::drop('support_messages');
        }

        if (! Schema::hasTable('support_messages')) {
            Schema::create('support_messages', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('request_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('client_id')->constrained()->cascadeOnDelete();
                $table->text('message');
                $table->timestamps();
            });
        }

        if (Schema::hasTable('request_deliveries') && ! Schema::hasTable('deliveries')) {
            Schema::rename('request_deliveries', 'deliveries');
        }

        if (Schema::hasTable('deliveries') && ! Schema::hasColumn('deliveries', 'request_id')) {
            Schema::drop('deliveries');
        }

        if (! Schema::hasTable('deliveries')) {
            Schema::create('deliveries', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('request_id')->constrained()->cascadeOnDelete();
                $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
                $table->text('notes')->nullable();
                $table->string('file_path')->nullable();
                $table->string('telegram_file_id')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('support_messages');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('quotation_decisions');
        Schema::dropIfExists('quotations');
        Schema::dropIfExists('clickup_tasks');
        Schema::dropIfExists('integration_events');
        Schema::dropIfExists('request_status_history');
    }
};
