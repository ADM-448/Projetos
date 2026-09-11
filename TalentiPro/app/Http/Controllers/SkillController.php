<?php

namespace App\Http\Controllers;

use App\Contracts\CompetenciaServiceContract;
use App\Http\Requests\StoreSkillRequest;
use App\Models\Competencia;
use App\Models\Skill;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class SkillController extends Controller
{
    public function __construct(
        private readonly CompetenciaServiceContract $competenciaService
    ) {}

    public function index(Competencia $competencia): JsonResponse
    {
        Gate::authorize('view', $competencia);

        return response()->json($competencia->skills);
    }

    public function store(StoreSkillRequest $request, Competencia $competencia): JsonResponse
    {
        Gate::authorize('update', $competencia);

        $this->competenciaService->adicionarSkill($competencia, $request->validated('nome'));

        // Retornar a lista atualizada para facilitar no frontend
        return response()->json([
            'message' => 'Skill adicionada com sucesso',
            'skills' => $competencia->skills()->get()
        ], 201);
    }

    public function destroy(Competencia $competencia, Skill $skill): JsonResponse
    {
        Gate::authorize('update', $competencia);

        // O Service já valida se a skill pertence a competencia
        $this->competenciaService->removerSkill($competencia, $skill->id);

        return response()->json([
            'message' => 'Skill removida com sucesso',
            'skills' => $competencia->skills()->get()
        ]);
    }
}
