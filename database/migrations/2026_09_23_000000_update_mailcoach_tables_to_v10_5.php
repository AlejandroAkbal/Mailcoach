<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mailcoach_campaigns') && ! Schema::hasColumn('mailcoach_campaigns', 'split_test_statistics')) {
            Schema::table('mailcoach_campaigns', function (Blueprint $table) {
                $table->json('split_test_statistics')->nullable();
            });
        }

        if (! Schema::hasTable('mailcoach_subscriber_import_rows')) {
            Schema::create('mailcoach_subscriber_import_rows', function (Blueprint $table) {
                $table->foreignId('subscriber_import_id')->constrained('mailcoach_subscriber_imports')->cascadeOnDelete();
                $table->string('row_identifier', 64);
                $table->primary(['subscriber_import_id', 'row_identifier'], 'mailcoach_import_rows_primary');
            });
        }

        if (Schema::hasTable('mailcoach_subscriber_imports')) {
            Schema::table('mailcoach_subscriber_imports', function (Blueprint $table) {
                if (! Schema::hasColumn('mailcoach_subscriber_imports', 'unsubscribe_last_processed_id')) {
                    $table->unsignedBigInteger('unsubscribe_last_processed_id')->default(0);
                }

                if (! Schema::hasColumn('mailcoach_subscriber_imports', 'unsubscribe_completed_at')) {
                    $table->timestamp('unsubscribe_completed_at')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mailcoach_campaigns') && Schema::hasColumn('mailcoach_campaigns', 'split_test_statistics')) {
            Schema::table('mailcoach_campaigns', function (Blueprint $table) {
                $table->dropColumn('split_test_statistics');
            });
        }

        Schema::dropIfExists('mailcoach_subscriber_import_rows');

        if (Schema::hasTable('mailcoach_subscriber_imports')) {
            Schema::table('mailcoach_subscriber_imports', function (Blueprint $table) {
                if (Schema::hasColumn('mailcoach_subscriber_imports', 'unsubscribe_last_processed_id')) {
                    $table->dropColumn('unsubscribe_last_processed_id');
                }

                if (Schema::hasColumn('mailcoach_subscriber_imports', 'unsubscribe_completed_at')) {
                    $table->dropColumn('unsubscribe_completed_at');
                }
            });
        }
    }
};
