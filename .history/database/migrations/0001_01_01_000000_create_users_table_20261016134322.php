<?php
public function up(): void
{
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->enum('role', ['superadmin', 'sales', 'purchase', 'manager']);
        $table->timestamps();
    });

    Schema::create('sessions', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->foreignId('user_id')->nullable()->index();
        $table->string('ip_address', 45)->nullable();
        $table->text('user_agent')->nullable();
        $table->longText('payload');
        $table->integer('last_activity')->index();
    });
}

public function down(): void
{
    Schema::dropIfExists('sessions');
    Schema::dropIfExists('users');
}