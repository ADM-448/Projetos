# **💼 Plataforma de Recrutamento Corporativo (Architecture Showcase)**

> **⚠️ Nota de Confidencialidade:** Este repositório é uma demonstração de arquitetura e boas práticas aplicadas na construção de um ecossistema real de vagas e recrutamento. Regras de negócio exclusivas, nomes de tabelas proprietárias e dados reais da empresa foram rigorosamente anonimizados e abstraídos. O sistema em produção pode ser acessado em [intalenti.com.br/vagas/IntalenTIVagasMVC/](https://intalenti.com.br/vagas/IntalenTIVagasMVC/auth/login).

## **🚀 Sobre a Engenharia do Projeto**

Este estudo de caso documenta a estruturação de um sistema robusto de gerenciamento de vagas e filtragem de candidatos, desenvolvido com **PHP 8 e Laravel**. O foco central da arquitetura apresentada aqui é a **manutenibilidade do código**, **segurança contra injeções de dados** e **alta performance em buscas no banco de dados**.

## **🏛️ Arquitetura e Segurança (Padrões MVC)**

O sistema foi desenhado respeitando rigorosamente o padrão **MVC (Model-View-Controller)**, garantindo que o acoplamento seja mínimo:

* **Controllers:** Focados inteiramente no fluxo HTTP (Clean Controllers).  
* **Form Requests:** Utilizados como a primeira barreira de defesa do sistema. Eles encapsulam regras complexas de autorização e validação de dados dinâmicos, garantindo que lixo ou tentativas de injeção nunca cheguem ao Controller.  
* **Models:** Encarregados da variações e abstração de dados via Eloquent ORM, implementando *Eager Loading* para performance e Escopos (Scopes) para consultas padronizadas.

### **🛡️ Exemplo Prático: Segurança e Validação Profunda**

Abaixo, um exemplo abstrato de como protegi as rotas de criação de vagas. A validação não checa apenas strings básicas, mas também condicionais (como salários) e arrays dinâmicos de perguntas.

\<?php

namespace App\\Http\\Requests;

use Illuminate\\Foundation\\Http\\FormRequest;

class StoreVagaRequest extends FormRequest  
{  
    public function authorize()  
    {  
        // Camada de Segurança: Apenas perfis Recrutadores podem prosseguir  
        return $this-\>user()-\>tipo\_usuario \=== 2;  
    }

    public function rules()  
    {  
        return \[  
            'titulo'             \=\> 'required|string|max:255',  
            'empresa\_id'         \=\> 'required|exists:empresas,id', // Proteção de integridade referencial  
            'descricao'          \=\> 'required|string',  
              
            // Validação lógica de valores salariais  
            'faixa\_salarial\_min' \=\> 'nullable|numeric|min:0',  
            'faixa\_salarial\_max' \=\> 'nullable|numeric|gte:faixa\_salarial\_min', // MAX deve ser \>= MIN  
              
            // Validação profunda de arrays dinâmicos enviados pelo front-end  
            'perguntas'                \=\> 'nullable|array',  
            'perguntas.\*.texto'        \=\> 'required\_with:perguntas|string',  
            'perguntas.\*.eliminatoria' \=\> 'boolean',  
        \];  
    }  
}

## **⚡ Performance: Modelagem de Dados e Otimização de Buscas**

Uma plataforma de vagas recebe milhares de consultas textuais (ex: buscar por "Desenvolvedor PHP Laravel"). Utilizar o padrão SQL %LIKE% nesses casos obriga o banco a ler todas as linhas da tabela (*Full Table Scan*), degradando a performance e sobrecarregando a CPU do servidor.

Para resolver esse gargalo arquitetural e escalar o sistema, implementei **Full-Text Search em Boolean Mode** no MySQL. Com índices invertidos, o banco de dados retorna resultados complexos em milissegundos.

### **🔍 Exemplo Prático: Busca Otimizada e Eager Loading**

Abaixo, um excerto do Model demonstrando como a query foi estruturada no Laravel para evitar lentidão e o clássico problema de *"N+1 queries"*.

\<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;  
use Illuminate\\Database\\Eloquent\\Builder;

class Vaga extends Model  
{  
    // Eager Loading automático para relacionamentos frequentemente usados  
    protected $with \= \['empresa'\];

    /\*\*  
     \* Scope de Busca Avançada utilizando Índices Full-Text no banco.  
     \*/  
    public function scopeBuscaOtimizada(Builder $query, string $busca, ?string $localizacao, ?string $ramo)  
    {  
        // Sanitização e formatação da string de busca para o Boolean Mode do MySQL  
        $termo \= str\_replace(' ', '\* ', trim(preg\_replace('/\[+\\-\>\<()\~\\"@\]+/', ' ', $busca))) . '\*';

        return $query-\>where('status', 'ativa')  
            \-\>when($busca, function ($q) use ($termo) {  
                // Executa a busca em milissegundos graças ao índice Full-Text  
                $q-\>whereRaw('MATCH(titulo, descricao) AGAINST(? IN BOOLEAN MODE)', \[$termo\])  
                  \-\>orWhereHas('empresa', function ($qEmpresa) use ($termo) {  
                        
                      // Filtra o nome da empresa, mas garante a segurança da privacidade  
                      $qEmpresa-\>whereRaw('MATCH(nome\_empresa) AGAINST(? IN BOOLEAN MODE)', \[$termo\]);  
                        
                  }, '\>=', 1, 'and', function($qCond) {  
                      $qCond-\>where('vagas.confidencial', 0); // Vagas sigilosas não expõem a empresa  
                  });  
            })  
            \-\>when($localizacao, fn($q) \=\> $q-\>where('localizacao', 'LIKE', "%{$localizacao}%"))  
            \-\>when($ramo, fn($q) \=\> $q-\>where('ramo\_atividade', 'LIKE', "%{$ramo}%"))  
            \-\>latest(); // Ordena pelas mais recentes  
    }  
}  
