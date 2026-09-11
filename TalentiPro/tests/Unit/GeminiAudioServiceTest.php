<?php

namespace Tests\Unit;

use App\Services\GeminiAudioService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class GeminiAudioServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Garantir que a key está setada pro teste não falhar
        putenv('GEMINI_API_KEY=fake_api_key_123');
    }

    public function test_gemini_service_transcreve_audio_corretamente()
    {
        $audioContent = 'fake_audio_binary_content';
        $mimeType = 'audio/webm';

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => 'Transcrição do áudio gerada.'
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $service = new GeminiAudioService();
        $resultado = $service->transcreverAudio($audioContent, $mimeType);

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['success']);
        $this->assertArrayHasKey('transcricao', $resultado);
        $this->assertEquals('Transcrição do áudio gerada.', $resultado['transcricao']);
    }

    public function test_gemini_service_analisa_transcricao_corretamente()
    {
        $transcricao = 'A reunião foi muito boa.';
        
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => '```json
{
  "resumo": "A reunião foi muito boa.",
  "sentimento": "Positivo",
  "pdis": []
}
```'
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $service = new GeminiAudioService();
        $resultado = $service->analisarTranscricao($transcricao, []);

        $this->assertIsArray($resultado);
        $this->assertTrue($resultado['success']);
        $this->assertArrayHasKey('resumo', $resultado);
        $this->assertArrayHasKey('sentimento', $resultado);
        $this->assertEquals('A reunião foi muito boa.', $resultado['resumo']);
    }

    public function test_gemini_service_trata_erro_de_api()
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response('Unauthorized', 401),
        ]);

        $service = new GeminiAudioService();
        $resultado = $service->transcreverAudio('audio', 'audio/webm');

        $this->assertIsArray($resultado);
        $this->assertFalse($resultado['success']);
        $this->assertArrayHasKey('error', $resultado);
    }
}
