import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
    vus: 100,
    duration: '30s',
    thresholds: {
        http_req_failed: ['rate<0.01'], // 99% of requests must succeed
        http_req_duration: ['p(95)<2000'], // 95% of requests should be below 2s
    },
};

export default function () {
    // URL base do seu projeto PHP no XAMPP
    const BASE_URL = 'http://localhost/Projetos%20PHP/INTALENTI%20VAGAS/IntalenTi%20Vagas%20Antigravity/IntalenTIVagasMVC/public';

    // Endpoint de busca de vagas
    // ?busca=a simula uma pesquisa genérica que provavelmente retorna resultados
    const searchUrl = `${BASE_URL}/vagas?busca=a`;

    let res = http.get(searchUrl);

    // Verificações Inteligentes
    check(res, {
        'status is 200': (r) => r.status === 200,
        // Verifica se carregou o HTML básico
        'is html': (r) => r.headers['Content-Type'] && r.headers['Content-Type'].includes('text/html'),
        // Verifica se achou pelo menos UM cartão de vaga (ajuste 'card' ou 'vaga-item' conforme seu HTML real)
        'found results': (r) => r.body.includes('job-card'),
        // Opcional: Verifica se NÃO apareceu a mensagem de erro (garante que não está vazio)
        'not empty': (r) => !r.body.includes('Nenhuma vaga encontrada'),
    });

    sleep(1);
}