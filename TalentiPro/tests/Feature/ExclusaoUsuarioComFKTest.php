<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\User;
use App\Models\Avaliacao;
use App\Enums\StatusEmpresa;
use App\Enums\StatusUsuario;
use App\Enums\UserRole;
use App\Services\ColaboradorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Suite: ExclusaoUsuarioComFKTest
 *
 * Cobre os dois bugs corrigidos em 2026-07-13:
 * 1) Exclusao de usuario com avaliacoes (FK created_by) sem erro 500
 * 2) Importacao de colaboradores com campo telefone/whatsapp via CSV
 */
class ExclusaoUsuarioComFKTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;
    private User $master;
    private User $alvo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresa = Empresa::factory()->create([
            'status'       => StatusEmpresa::ATIVA,
            'max_usuarios' => 50,
        ]);

        $this->master = User::factory()->create([
            'empresa_id'    => $this->empresa->id,
            'perfil_acesso' => UserRole::MASTER,
            'status'        => StatusUsuario::ATIVO,
        ]);

        $this->alvo = User::factory()->create([
            'empresa_id'    => $this->empresa->id,
            'perfil_acesso' => UserRole::MASTER,
            'status'        => StatusUsuario::ATIVO,
        ]);
    }

    // =====================================================================
    // GRUPO 1: Exclusao com FK em avaliacoes.created_by
    // =====================================================================

    #[Test]
    public function test_excluir_usuario_que_criou_avaliacoes_nao_lanca_erro_500(): void
    {
        Avaliacao::create([
            'empresa_id'  => $this->empresa->id,
            'created_by'  => $this->alvo->id,
            'titulo'      => 'Avaliacao Performance Q1',
            'data_inicio' => now()->format('Y-m-d'),
            'data_fim'    => now()->addDays(30)->format('Y-m-d'),
            'status'      => 'rascunho',
        ]);

        $response = $this->actingAs($this->master)
            ->delete(route('colaboradores.destroy', $this->alvo->id));

        // Deve redirecionar sem erro — antes estourava 500
        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('users', ['id' => $this->alvo->id]);
    }

    #[Test]
    public function test_avaliacoes_sao_preservadas_apos_excluir_o_criador(): void
    {
        $avaliacao = Avaliacao::create([
            'empresa_id'  => $this->empresa->id,
            'created_by'  => $this->alvo->id,
            'titulo'      => 'Avaliacao 360',
            'data_inicio' => now()->format('Y-m-d'),
            'data_fim'    => now()->addDays(30)->format('Y-m-d'),
            'status'      => 'rascunho',
        ]);

        // Invoca o service diretamente (teste de integracao)
        app(ColaboradorService::class)->excluir($this->alvo, $this->master->id);

        // Avaliacao ainda existe — apenas created_by vira null
        $this->assertDatabaseHas('avaliacoes', ['id' => $avaliacao->id]);
        $this->assertDatabaseHas('avaliacoes', ['id' => $avaliacao->id, 'created_by' => null]);
    }

    #[Test]
    public function test_excluir_usuario_sem_avaliacoes_funciona_normalmente(): void
    {
        $this->assertDatabaseMissing('avaliacoes', ['created_by' => $this->alvo->id]);

        $response = $this->actingAs($this->master)
            ->delete(route('colaboradores.destroy', $this->alvo->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $this->alvo->id]);
    }

    #[Test]
    public function test_usuario_nao_pode_excluir_a_si_mesmo(): void
    {
        $response = $this->actingAs($this->master)
            ->delete(route('colaboradores.destroy', $this->master->id));

        $response->assertRedirect();
        // O controller usa ->with('error', ...) e nao withErrors()
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->master->id]);
    }

    // =====================================================================
    // GRUPO 2: Importacao CSV com telefone/WhatsApp
    // =====================================================================

    #[Test]
    public function test_importar_csv_com_campo_telefone_salva_numero(): void
    {
        $csv  = "nome,email,cpf,perfil,telefone\n";
        $csv .= '"Joao Importado",joao.importado@teste.com,,colaborador,"(11) 98765-4321"' . "\n";

        $file = UploadedFile::fake()->createWithContent('colaboradores.csv', $csv);

        $this->actingAs($this->master)
            ->post(route('colaboradores.importar'), ['arquivo' => $file])
            ->assertRedirect(route('colaboradores.index'));

        $this->assertDatabaseHas('users', [
            'email'    => 'joao.importado@teste.com',
            'telefone' => '11987654321',
        ]);
    }

    #[Test]
    public function test_importar_csv_com_campo_whatsapp_salva_numero(): void
    {
        $csv  = "nome,email,perfil,WhatsApp\n";
        $csv .= '"Maria WPP",maria.wpp@teste.com,colaborador,5511999887766' . "\n";

        $file = UploadedFile::fake()->createWithContent('colaboradores.csv', $csv);

        $this->actingAs($this->master)
            ->post(route('colaboradores.importar'), ['arquivo' => $file])
            ->assertRedirect(route('colaboradores.index'));

        $this->assertDatabaseHas('users', [
            'email'    => 'maria.wpp@teste.com',
            'telefone' => '5511999887766',
        ]);
    }

    #[Test]
    public function test_importar_csv_sem_telefone_nao_quebra_a_importacao(): void
    {
        $csv  = "nome,email,perfil\n";
        $csv .= '"Carlos Sem Fone",carlos.semfone@teste.com,colaborador' . "\n";

        $file = UploadedFile::fake()->createWithContent('colaboradores.csv', $csv);

        $this->actingAs($this->master)
            ->post(route('colaboradores.importar'), ['arquivo' => $file])
            ->assertRedirect(route('colaboradores.index'));

        $user = User::where('email', 'carlos.semfone@teste.com')->first();
        $this->assertNotNull($user, 'Usuario sem telefone nao foi importado');
        $this->assertNull($user->telefone);
    }

    #[Test]
    public function test_importar_csv_sanitiza_formatacao_do_telefone(): void
    {
        $csv  = "nome,email,perfil,telefone\n";
        $csv .= '"Ana Formatada",ana.formatada@teste.com,colaborador,"(21) 3333-4444"' . "\n";

        $file = UploadedFile::fake()->createWithContent('colaboradores_fmt.csv', $csv);

        $this->actingAs($this->master)
            ->post(route('colaboradores.importar'), ['arquivo' => $file]);

        $user = User::where('email', 'ana.formatada@teste.com')->first();
        $this->assertNotNull($user, 'Usuario nao foi criado');
        // Apenas digitos — sem parenteses, tracos ou espacos
        $this->assertMatchesRegularExpression('/^\d+$/', $user->telefone);
        $this->assertEquals('2133334444', $user->telefone);
    }
}
