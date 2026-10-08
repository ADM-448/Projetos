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
        // 1. Configurações de VoIP / FreePBX no Tenant
        Schema::table('tenants', function (Blueprint $table) {
            $table->boolean('voip_enabled')->default(false)->after('ai_model');
            $table->string('voip_server_host')->nullable()->after('voip_enabled');
            $table->string('voip_webrtc_ws_url')->nullable()->after('voip_server_host');
            $table->string('voip_ami_host')->nullable()->after('voip_webrtc_ws_url');
            $table->integer('voip_ami_port')->default(5038)->after('voip_ami_host');
            $table->string('voip_ami_user')->nullable()->after('voip_ami_port');
            $table->string('voip_ami_secret')->nullable()->after('voip_ami_user');
            $table->string('voip_recordings_url')->nullable()->after('voip_ami_secret');
        });

        // 2. Ramal SIP / WebRTC do Usuário (SDR / Gestor)
        Schema::table('users', function (Blueprint $table) {
            $table->string('sip_extension')->nullable()->after('role');
            $table->string('sip_secret')->nullable()->after('sip_extension');
            $table->boolean('sip_webrtc_enabled')->default(true)->after('sip_secret');
        });

        // 3. Tabela de Registros de Chamadas (Call Logs)
        Schema::create('call_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('uniqueid')->nullable()->index(); // ID da chamada no Asterisk / FreePBX
            $table->string('phone_dialed');
            $table->string('direction')->default('outbound'); // outbound, inbound
            $table->string('status')->default('completed')->index(); // ringing, answered, busy, no_answer, failed, completed
            $table->integer('duration')->default(0); // duração total em segundos
            $table->integer('billable_seconds')->default(0); // duração de conversa
            $table->string('outcome')->nullable()->index(); // connected, meeting_scheduled, gatekeeper, busy, no_answer, voicemail
            $table->text('notes')->nullable();
            $table->string('recording_url')->nullable();
            $table->text('transcription')->nullable();
            $table->text('ai_summary')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_logs');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['sip_extension', 'sip_secret', 'sip_webrtc_enabled']);
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'voip_enabled',
                'voip_server_host',
                'voip_webrtc_ws_url',
                'voip_ami_host',
                'voip_ami_port',
                'voip_ami_user',
                'voip_ami_secret',
                'voip_recordings_url',
            ]);
        });
    }
};
