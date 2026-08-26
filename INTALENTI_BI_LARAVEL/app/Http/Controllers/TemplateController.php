<?php

namespace App\Http\Controllers;

class TemplateController extends Controller
{
    public function download()
    {
        $filePath = public_path('Modelo_Importacao_BI.xlsx');

        if (!file_exists($filePath)) {
            abort(404, 'Arquivo modelo não encontrado.');
        }

        return response()->download(
            $filePath,
            'Modelo_Importacao_BI.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }
}
