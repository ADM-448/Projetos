<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Competencia;
use Illuminate\Http\Request;

class CompetenciaWebController extends Controller
{
    public function index()
    {
        $competencias = Competencia::with('skills')->get();
        return view('competencias.index', compact('competencias'));
    }

    public function create()
    {
        return view('competencias.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'skills' => 'required|array|min:1', // Pelo menos 1 skill
            'skills.*.nome' => 'required|string', // Nome da skill obrigatório
        ]);

        $competencia = Competencia::create([
            'nome' => $validated['nome'],
            'descricao' => $request->descricao,
            // 'empresa_id' => auth()->user()->empresa_id ?? 1
        ]);

        // Cria as Skills filhas
        foreach ($request->skills as $skill) {
            if (!empty($skill['nome'])) {
                $competencia->skills()->create($skill);
            }
        }

        return redirect()->route('competencias.index')->with('success', 'Competência criada com sucesso!');
    }

    public function edit($id)
    {
        // Carrega a competência JÁ com as skills para preencher o form
        $competencia = Competencia::with('skills')->findOrFail($id);
        return view('competencias.form', compact('competencia'));
    }

    public function update(Request $request, $id)
    {
        $competencia = Competencia::findOrFail($id);

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'skills' => 'nullable|array',
        ]);

        $competencia->update([
            'nome' => $validated['nome'],
            'descricao' => $request->descricao
        ]);

        // ATUALIZAÇÃO DE SKILLS (Estratégia: Limpar e Recriar)
        // Isso evita lógica complexa de verificar qual ID mudou
        if ($request->has('skills')) {
            $competencia->skills()->delete(); // Remove as antigas

            foreach ($request->skills as $skill) {
                if (!empty($skill['nome'])) {
                    $competencia->skills()->create($skill);
                }
            }
        }

        return redirect()->route('competencias.index')->with('success', 'Competência atualizada!');
    }

    public function destroy($id)
    {
        $competencia = Competencia::findOrFail($id);
        // Ao deletar a competência, as skills somem (se configurado cascade)
        // ou podemos forçar delete aqui:
        $competencia->skills()->delete();
        $competencia->delete();

        return redirect()->route('competencias.index')->with('success', 'Competência excluída.');
    }

    public function importar(Request $request)
    {
        $request->validate([
            'arquivo' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('arquivo');
        $handle = fopen($file->getRealPath(), 'r');
        
        // Detect delimiter
        $line = fgets($handle);
        $delimiter = (strpos($line, ';') !== false) ? ';' : ',';
        rewind($handle);

        $header = fgetcsv($handle, 0, $delimiter);
        
        if (!$header) {
             return back()->withErrors(['arquivo' => 'Arquivo vazio ou inválido.']);
        }

        // Remove BOM if present
        if (isset($header[0]) && strncmp($header[0], "\xEF\xBB\xBF", 3) === 0) {
            $header[0] = substr($header[0], 3);
        }

        // Normalize headers
        $header = array_map(function($h) {
            return strtolower(trim($h)); 
        }, $header);

        // Map indices
        $idxNome = array_search('nome', $header);
        if($idxNome === false) $idxNome = array_search('competencia', $header);
        
        $idxDesc = array_search('descricao', $header);
        $idxSkills = array_search('skills', $header); // Separated by |

        if ($idxNome === false) {
             fclose($handle);
             return back()->withErrors(['arquivo' => "Cabeçalho inválido. Esperado: 'nome' (ou 'competencia')."]);
        }

        $importedCount = 0;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
             // Detecta e converte encoding automaticamente:
             // Se o texto JÁ for UTF-8 válido, não mexe.
             // Se for Windows-1252/Latin-1 (Excel ANSI), converte para UTF-8.
             $row = array_map(function($item) {
                 if (mb_detect_encoding($item, 'UTF-8', true)) {
                     return $item; // Já é UTF-8, não converte
                 }
                 return mb_convert_encoding($item, 'UTF-8', 'Windows-1252');
             }, $row);

             if(empty($row[$idxNome])) continue; // Skip empty rows

             $nome = trim($row[$idxNome]);
             $descricao = ($idxDesc !== false && isset($row[$idxDesc])) ? trim($row[$idxDesc]) : null;
             
             // Create or Update Competencia
             $competencia = Competencia::updateOrCreate(
                ['nome' => $nome],
                ['descricao' => $descricao]
             );

             // Import Skills (Separated by |)
             if ($idxSkills !== false && !empty($row[$idxSkills])) {
                 $skillsStr = $row[$idxSkills];
                 $skillsArr = explode('|', $skillsStr);
                 
                 foreach($skillsArr as $skillName) {
                     $skillName = trim($skillName);
                     if(!empty($skillName)) {
                         // Check if skill exists for this competencia
                         $competencia->skills()->firstOrCreate(['nome' => $skillName]);
                     }
                 }
             }
             
             $importedCount++;
        }
        
        fclose($handle);

        return redirect()->back()->with('success', "Importação concluída! {$importedCount} competências processadas.");
    }
}
