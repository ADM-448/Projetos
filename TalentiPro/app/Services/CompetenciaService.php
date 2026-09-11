<?php

namespace App\Services;

use App\Contracts\CompetenciaServiceContract;
use App\Models\Competencia;
use App\Models\Skill;
use Illuminate\Support\Facades\DB;
use Exception;

class CompetenciaService implements CompetenciaServiceContract
{
    public function criar(array $dados): Competencia
    {
        return DB::transaction(function () use ($dados) {
            $competencia = Competencia::create($dados);
            
            if (!empty($dados['skills']) && is_array($dados['skills'])) {
                foreach ($dados['skills'] as $skillName) {
                    if (!empty(trim($skillName))) {
                        $competencia->skills()->create(['nome' => trim($skillName)]);
                    }
                }
            }
            
            return $competencia;
        });
    }

    public function atualizar(Competencia $competencia, array $dados): Competencia
    {
        return DB::transaction(function () use ($competencia, $dados) {
            $competencia->update($dados);
            
            if (isset($dados['skills']) && is_array($dados['skills'])) {
                $competencia->skills()->delete();
                foreach ($dados['skills'] as $skillName) {
                    if (!empty(trim($skillName))) {
                        $competencia->skills()->create(['nome' => trim($skillName)]);
                    }
                }
            }
            
            return $competencia->fresh();
        });
    }

    public function excluir(Competencia $competencia): void
    {
        DB::transaction(function () use ($competencia) {
            // As skills vinculadas serão removidas em cascade no DB.
            $competencia->delete();
        });
    }

    public function adicionarSkill(Competencia $competencia, string $nomeSkill): void
    {
        DB::transaction(function () use ($competencia, $nomeSkill) {
            $competencia->skills()->create([
                'nome' => $nomeSkill,
            ]);
        });
    }

    public function removerSkill(Competencia $competencia, int $skillId): void
    {
        DB::transaction(function () use ($competencia, $skillId) {
            $skill = $competencia->skills()->find($skillId);
            if ($skill) {
                $skill->delete();
            } else {
                throw new Exception('A skill não existe ou não pertence a esta competência.');
            }
        });
    }
}
