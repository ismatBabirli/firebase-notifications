<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_tokens', function (Blueprint $table) {
            $table->uuid("id")->unique()->primary()->index();
            $table->foreignId("user_id")->constrained();
            $table->foreignId("application_id")->constrained();
            $table->string("application_name")->nullable();
            $table->string("fcm_token", 300);
            $table->ipAddress("user_ip")->nullable();
            $table->string("user_agent", 350)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_tokens');
    }
}
