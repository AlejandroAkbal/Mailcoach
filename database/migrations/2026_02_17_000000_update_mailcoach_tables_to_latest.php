<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function indexExists(string $table, string $indexName): bool
    {
        $fullTable = Schema::getConnection()->getTablePrefix() . $table;
        $indexes = DB::select('SHOW INDEX FROM `' . str_replace('`', '``', $fullTable) . '` WHERE Key_name = ?', [$indexName]);

        return count($indexes) > 0;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mailcoach_email_lists', function (Blueprint $table) {
            if (!Schema::hasColumn('mailcoach_email_lists', 'extra_attributes')) {
                $table->json('extra_attributes')->nullable()->after('website_subscription_description');
            }
        });

        Schema::table('mailcoach_subscribers', function (Blueprint $table) {
            if (!Schema::hasColumn('mailcoach_subscribers', 'emails_received')) {
                $table->unsignedInteger('emails_received')->default(0)->after('extra_attributes');
            }
            if (!Schema::hasColumn('mailcoach_subscribers', 'emails_opened')) {
                $table->unsignedInteger('emails_opened')->default(0)->after('emails_received');
            }
            if (!Schema::hasColumn('mailcoach_subscribers', 'emails_clicked')) {
                $table->unsignedInteger('emails_clicked')->default(0)->after('emails_opened');
            }
            if (!Schema::hasColumn('mailcoach_subscribers', 'last_open_at')) {
                $table->dateTime('last_open_at')->nullable()->after('emails_clicked');
            }
            if (!Schema::hasColumn('mailcoach_subscribers', 'last_click_at')) {
                $table->dateTime('last_click_at')->nullable()->after('last_open_at');
            }

            if (!$this->indexExists('mailcoach_subscribers', 'engagement_index')) {
                $table->index(['email_list_id', 'emails_received', 'emails_opened', 'emails_clicked'], 'engagement_index');
            }
            if (!$this->indexExists('mailcoach_subscribers', 'mailcoach_subscribers_email_list_id_last_open_at_index')) {
                $table->index(['email_list_id', 'last_open_at']);
            }
            if (!$this->indexExists('mailcoach_subscribers', 'mailcoach_subscribers_email_list_id_last_click_at_index')) {
                $table->index(['email_list_id', 'last_click_at']);
            }
        });

        Schema::table('mailcoach_tags', function (Blueprint $table) {
            if (!$this->indexExists('mailcoach_tags', 'mailcoach_tags_email_list_id_index')) {
                $table->index('email_list_id');
            }
            if (!$this->indexExists('mailcoach_tags', 'mailcoach_tags_email_list_id_name_type_unique')) {
                $table->unique(['email_list_id', 'name', 'type']);
            }
        });

        Schema::table('mailcoach_sends', function (Blueprint $table) {
            if ($this->indexExists('mailcoach_sends', 'mailcoach_sends_transport_message_id_index')) {
                $table->dropIndex('mailcoach_sends_transport_message_id_index');
            }
            if (!$this->indexExists('mailcoach_sends', 'mailcoach_sends_content_item_id_sent_at_index')) {
                $table->index(['content_item_id', 'sent_at']);
            }
            if (!$this->indexExists('mailcoach_sends', 'mailcoach_sends_failed_at_index')) {
                $table->index(['failed_at']);
            }
        });

        Schema::table('mailcoach_clicks', function (Blueprint $table) {
            if (!$this->indexExists('mailcoach_clicks', 'mailcoach_clicks_link_id_subscriber_id_index')) {
                $table->index(['link_id', 'subscriber_id']);
            }
            if (!$this->indexExists('mailcoach_clicks', 'mailcoach_clicks_link_id_created_at_index')) {
                $table->index(['link_id', 'created_at']);
            }
        });

        Schema::table('mailcoach_opens', function (Blueprint $table) {
            if (!$this->indexExists('mailcoach_opens', 'mailcoach_opens_content_item_id_created_at_index')) {
                $table->index(['content_item_id', 'created_at']);
            }
        });

        Schema::table('mailcoach_automation_action_subscriber', function (Blueprint $table) {
            if ($this->indexExists('mailcoach_automation_action_subscriber', 'pending_action_subscribers')) {
                $table->dropIndex('pending_action_subscribers');
            }
            if (!$this->indexExists('mailcoach_automation_action_subscriber', 'pending_action_subscribers')) {
                $table->index(['action_id', 'job_dispatched_at', 'completed_at', 'halted_at'], 'pending_action_subscribers');
            }
            if (!$this->indexExists('mailcoach_automation_action_subscriber', 'maas_action_subscriber_idx')) {
                $table->index(['action_id', 'subscriber_id'], 'maas_action_subscriber_idx');
            }
            if (!$this->indexExists('mailcoach_automation_action_subscriber', 'maas_pending_run_idx')) {
                $table->index(['action_id', 'job_dispatched_at', 'completed_at', 'halted_at', 'run_at'], 'maas_pending_run_idx');
            }
        });

        Schema::table('mailcoach_subscriber_imports', function (Blueprint $table) {
            if (Schema::hasColumn('mailcoach_subscriber_imports', 'errors')) {
                $table->dropColumn('errors');
            }
            $table->unsignedInteger('errors')->nullable()->after('imported_subscribers_count');
        });

        Schema::table('mailcoach_subscriber_exports', function (Blueprint $table) {
            if (Schema::hasColumn('mailcoach_subscriber_exports', 'errors')) {
                $table->dropColumn('errors');
            }
            $table->unsignedInteger('errors')->nullable()->after('exported_subscribers_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
