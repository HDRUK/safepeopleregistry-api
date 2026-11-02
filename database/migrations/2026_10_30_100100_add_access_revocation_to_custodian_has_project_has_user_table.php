<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * When a User leaves an Organisation, they leave every project they were on through that affiliation.
 * That does not revoke their real data access, so the Custodian must confirm they have done so.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::table('custodian_has_project_has_user', function (Blueprint $table) {
            $table->dateTime('access_revocation_required_at')->nullable();
            $table->dateTime('access_revocation_reminded_at')->nullable();
            $table->dateTime('access_revoked_at')->nullable();
            $table->unsignedBigInteger('access_revoked_by')->nullable();

            $table->foreign('access_revoked_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['custodian_id', 'access_revocation_required_at', 'access_revoked_at'], 'chpu_access_revocation_index');
        });
    }

    public function down(): void
    {
        Schema::table('custodian_has_project_has_user', function (Blueprint $table) {
            $table->dropIndex('chpu_access_revocation_index');
            $table->dropForeign(['access_revoked_by']);
            $table->dropColumn([
                'access_revocation_required_at',
                'access_revocation_reminded_at',
                'access_revoked_at',
                'access_revoked_by',
            ]);
        });
    }
};
