<?php

namespace Tests\Unit;

use App\Enums\StatusEmpresa;
use App\Enums\StatusUsuario;
use App\Enums\UserRole;
use App\Http\Requests\StoreCargoRequest;
use App\Http\Requests\StoreDepartamentoRequest;
use App\Http\Requests\UpdateCargoRequest;
use App\Http\Requests\UpdateDepartamentoRequest;
use App\Models\Cargo;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Tests\TestCase;

/**
 * Suite: CrossTenantRequestRulesTest (Unitário)
 *
 * Testa diretamente as regras de validação dos Form Requests
 * para garantir que o isolamento de tenant está embutido nas regras.
 *
 * Não depende de rotas HTTP — valida apenas a lógica de regras.
 */
class CrossTenantRequestRulesTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresaA;
    private Empresa $empresaB;
    private User $masterA;
    private User $gestorB;
    private Departamento $deptB;
    private Cargo $cargoB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresaA = Empresa::factory()->create(['status' => StatusEmpresa::ATIVA]);
        $this->empresaB = Empresa::factory()->create(['status' => StatusEmpresa::ATIVA]);

        $this->masterA = User::factory()->create([
            'empresa_id'    => $this->empresaA->id,
            'perfil_acesso' => UserRole::MASTER,
            'status'        => StatusUsuario::ATIVO,
        ]);

        $this->gestorB = User::factory()->create([
            'empresa_id'    => $this->empresaB->id,
            'perfil_acesso' => UserRole::GESTOR,
            'status'        => StatusUsuario::ATIVO,
        ]);

        Auth::login(User::factory()->create([
            'empresa_id'    => $this->empresaB->id,
            'perfil_acesso' => UserRole::MASTER,
            'status'        => StatusUsuario::ATIVO,
        ]));

        $this->deptB  = Departamento::create(['nome' => 'Dept B']);
        $this->cargoB = Cargo::create(['nome' => 'Cargo B']);

        // Agora loga como masterA (Empresa A)
        Auth::login($this->masterA);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // StoreDepartamentoRequest
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_store_departamento_request_possui_regra_exists_com_empresa_id(): void
    {
        $request = new StoreDepartamentoRequest();
        $rules   = $request->rules();

        // Confirma que gestor_id é um array de regras (não string simples)
        $this->assertIsArray($rules['gestor_id']);

        // Verifica que existe pelo menos um objeto Rule::exists (não é string crua)
        $hasRuleExists = collect($rules['gestor_id'])->contains(
            fn($rule) => $rule instanceof \Illuminate\Validation\Rules\Exists
        );

        $this->assertTrue($hasRuleExists, 'gestor_id deve usar Rule::exists() com filtro de empresa_id');
    }

    /** @test */
    public function test_store_departamento_request_rejeita_gestor_de_outra_empresa(): void
    {
        $request = new StoreDepartamentoRequest();
        $rules   = $request->rules();

        $validator = Validator::make(
            ['nome' => 'Dept A', 'gestor_id' => $this->gestorB->id],
            $rules
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('gestor_id', $validator->errors()->toArray());
    }

    /** @test */
    public function test_store_departamento_request_aceita_gestor_da_propria_empresa(): void
    {
        $gestorA = User::factory()->create([
            'empresa_id'    => $this->empresaA->id,
            'perfil_acesso' => UserRole::GESTOR,
            'status'        => StatusUsuario::ATIVO,
        ]);

        $request = new StoreDepartamentoRequest();
        $rules   = $request->rules();

        $validator = Validator::make(
            ['nome' => 'Dept A', 'gestor_id' => $gestorA->id],
            $rules
        );

        $this->assertFalse($validator->fails(), implode(', ', $validator->errors()->all()));
    }

    /** @test */
    public function test_store_departamento_request_aceita_gestor_nulo(): void
    {
        $request = new StoreDepartamentoRequest();
        $rules   = $request->rules();

        $validator = Validator::make(
            ['nome' => 'Dept A', 'gestor_id' => null],
            $rules
        );

        $this->assertFalse($validator->fails());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UpdateDepartamentoRequest
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_update_departamento_request_possui_regra_exists_com_empresa_id(): void
    {
        $request = new UpdateDepartamentoRequest();
        $rules   = $request->rules();

        $this->assertIsArray($rules['gestor_id']);

        $hasRuleExists = collect($rules['gestor_id'])->contains(
            fn($rule) => $rule instanceof \Illuminate\Validation\Rules\Exists
        );

        $this->assertTrue($hasRuleExists, 'gestor_id deve usar Rule::exists() com filtro de empresa_id');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // StoreCargoRequest
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_store_cargo_request_possui_regra_exists_para_departamento_id(): void
    {
        $request = new StoreCargoRequest();
        $rules   = $request->rules();

        $this->assertIsArray($rules['departamento_id']);

        $hasRuleExists = collect($rules['departamento_id'])->contains(
            fn($rule) => $rule instanceof \Illuminate\Validation\Rules\Exists
        );

        $this->assertTrue($hasRuleExists, 'departamento_id deve usar Rule::exists() com filtro de empresa_id');
    }

    /** @test */
    public function test_store_cargo_request_possui_regra_exists_para_cargo_superior_id(): void
    {
        $request = new StoreCargoRequest();
        $rules   = $request->rules();

        $this->assertIsArray($rules['cargo_superior_id']);

        $hasRuleExists = collect($rules['cargo_superior_id'])->contains(
            fn($rule) => $rule instanceof \Illuminate\Validation\Rules\Exists
        );

        $this->assertTrue($hasRuleExists, 'cargo_superior_id deve usar Rule::exists() com filtro de empresa_id');
    }

    /** @test */
    public function test_store_cargo_request_rejeita_departamento_de_outra_empresa(): void
    {
        $request = new StoreCargoRequest();
        $rules   = $request->rules();

        $validator = Validator::make(
            ['nome' => 'Cargo A', 'departamento_id' => $this->deptB->id],
            $rules
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('departamento_id', $validator->errors()->toArray());
    }

    /** @test */
    public function test_store_cargo_request_rejeita_cargo_superior_de_outra_empresa(): void
    {
        $request = new StoreCargoRequest();
        $rules   = $request->rules();

        $validator = Validator::make(
            ['nome' => 'Cargo A', 'cargo_superior_id' => $this->cargoB->id],
            $rules
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('cargo_superior_id', $validator->errors()->toArray());
    }

    /** @test */
    public function test_store_cargo_request_aceita_departamento_da_propria_empresa(): void
    {
        $deptA = Departamento::create(['nome' => 'Dept A Legítimo']);

        $request = new StoreCargoRequest();
        $rules   = $request->rules();

        $validator = Validator::make(
            ['nome' => 'Cargo A', 'departamento_id' => $deptA->id],
            $rules
        );

        $this->assertFalse($validator->fails(), implode(', ', $validator->errors()->all()));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UpdateCargoRequest
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function test_update_cargo_request_possui_regras_exists_para_ambos_campos(): void
    {
        $request = new UpdateCargoRequest();
        $rules   = $request->rules();

        foreach (['departamento_id', 'cargo_superior_id'] as $campo) {
            $this->assertIsArray($rules[$campo], "{$campo} deve ser array de regras");

            $hasRuleExists = collect($rules[$campo])->contains(
                fn($rule) => $rule instanceof \Illuminate\Validation\Rules\Exists
            );

            $this->assertTrue($hasRuleExists, "{$campo} deve usar Rule::exists() com filtro de empresa_id");
        }
    }
}
