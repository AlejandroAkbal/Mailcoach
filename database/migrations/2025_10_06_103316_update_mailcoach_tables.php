<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mailcoach_email_lists', function (Blueprint $table) {
            $table->json('extra_attributes')->nullable()->after('website_subscription_description');
        });

        Schema::table('mailcoach_subscribers', function (Blueprint $table) {
            $table->unsignedInteger('emails_received')->default(0)->after('extra_attributes');
            $table->unsignedInteger('emails_opened')->default(0)->after('emails_received');
            $table->unsignedInteger('emails_clicked')->default(0)->after('emails_opened');
            $table->dateTime('last_open_at')->nullable()->after('emails_clicked');
            $table->dateTime('last_click_at')->nullable()->after('last_open_at');

            $table->index(['email_list_id', 'emails_received', 'emails_opened', 'emails_clicked'], 'engagement_index');
            $table->index(['email_list_id', 'last_open_at']);
            $table->index(['email_list_id', 'last_click_at']);
        });

        Schema::table('mailcoach_tags', function (Blueprint $table) {
            $table->index('email_list_id');
            $table->unique(['email_list_id', 'name', 'type']);
        });

        Schema::table('mailcoach_sends', function (Blueprint $table) {
            $table->dropIndex('mailcoach_sends_transport_message_id_index');

            $table->index(['content_item_id', 'sent_at']);
            $table->index(['failed_at']);
        });

        Schema::table('mailcoach_clicks', function (Blueprint $table) {
            $table->index(['link_id', 'subscriber_id']);
            $table->index(['link_id', 'created_at']);
        });

        Schema::table('mailcoach_opens', function (Blueprint $table) {
            $table->index(['content_item_id', 'created_at']);
        });

        Schema::table('mailcoach_automation_action_subscriber', function (Blueprint $table) {
            $table->dropIndex('pending_action_subscribers');
            $table->index(['action_id', 'job_dispatched_at', 'completed_at', 'halted_at'], 'pending_action_subscribers');
        });

        Schema::table('mailcoach_subscriber_imports', function (Blueprint $table) {
            $table->dropColumn('errors');
        });

        Schema::table('mailcoach_subscriber_imports', function (Blueprint $table) {
            $table->unsignedInteger('errors')->nullable()->after('imported_subscribers_count');
        });

        Schema::table('mailcoach_subscriber_exports', function (Blueprint $table) {
            $table->dropColumn('errors');
        });

        Schema::table('mailcoach_subscriber_exports', function (Blueprint $table) {
            $table->unsignedInteger('errors')->nullable()->after('exported_subscribers_count');
        });
    }
};
