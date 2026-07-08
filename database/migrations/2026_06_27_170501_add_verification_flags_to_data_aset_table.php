<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_aset', function (Blueprint $table) {
            $table->boolean('needs_org_verification')->default(false)->after('id_unit');
            $table->boolean('needs_pic_verification')->default(false)->after('needs_org_verification');
            $table->boolean('needs_pj_verification')->default(false)->after('needs_pic_verification');
            
            $table->foreign('pic_id', 'fk_data_aset_pic_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('data_aset', function (Blueprint $table) {
            $table->dropForeign('fk_data_aset_pic_id');
            $table->dropColumn([
                'needs_org_verification',
                'needs_pic_verification',
                'needs_pj_verification'
            ]);
        });
    }
};
