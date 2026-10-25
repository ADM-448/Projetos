<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GeoService
{
    /**
     * Consulta CEP via BrasilAPI com fallback para ViaCEP e geocodificação via Nominatim.
     * Resultado é mantido em cache para performance e economia de requisições.
     */
    public function consultarCep(string $cep): ?array
    {
        $cepLimpo = preg_replace('/[^0-9]/', '', $cep);

        if (strlen($cepLimpo) !== 8) {
            return null;
        }

        return Cache::remember("cep_geo_{$cepLimpo}", now()->addDays(30), function () use ($cepLimpo) {
            $resultado = [
                'cep' => $cepLimpo,
                'logradouro' => null,
                'bairro' => null,
                'cidade' => null,
                'estado' => null,
                'latitude' => null,
                'longitude' => null,
            ];

            // 1. BrasilAPI (com suporte a coordenadas v2)
            try {
                $response = Http::timeout(4)->withoutVerifying()->get("https://brasilapi.com.br/api/cep/v2/{$cepLimpo}");
                if ($response->successful()) {
                    $data = $response->json();
                    $resultado['logradouro'] = $data['street'] ?? null;
                    $resultado['bairro'] = $data['neighborhood'] ?? null;
                    $resultado['cidade'] = $data['city'] ?? null;
                    $resultado['estado'] = $data['state'] ?? null;

                    if (!empty($data['location']['coordinates']['latitude'])) {
                        $resultado['latitude'] = (float) $data['location']['coordinates']['latitude'];
                        $resultado['longitude'] = (float) $data['location']['coordinates']['longitude'];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("BrasilAPI falhou para o CEP {$cepLimpo}: " . $e->getMessage());
            }

            // 2. ViaCEP (Fallback de endereço)
            if (empty($resultado['cidade'])) {
                try {
                    $responseViaCep = Http::timeout(4)->withoutVerifying()->get("https://viacep.com.br/ws/{$cepLimpo}/json/");
                    if ($responseViaCep->successful()) {
                        $dataVia = $responseViaCep->json();
                        if (!isset($dataVia['erro']) && !empty($dataVia['localidade'])) {
                            $resultado['logradouro'] = $dataVia['logradouro'] ?? null;
                            $resultado['bairro'] = $dataVia['bairro'] ?? null;
                            $resultado['cidade'] = $dataVia['localidade'];
                            $resultado['estado'] = $dataVia['uf'];
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning("ViaCEP falhou para o CEP {$cepLimpo}: " . $e->getMessage());
                }
            }

            // 3. Fallback inteligente para CEP truncado (ex: últimos dígitos 000)
            if (empty($resultado['cidade'])) {
                $cepBase = substr($cepLimpo, 0, 5) . '000';
                if ($cepBase !== $cepLimpo) {
                    try {
                        $resBase = Http::timeout(4)->withoutVerifying()->get("https://viacep.com.br/ws/{$cepBase}/json/");
                        if ($resBase->successful()) {
                            $dataBase = $resBase->json();
                            if (!isset($dataBase['erro']) && !empty($dataBase['localidade'])) {
                                $resultado['cidade'] = $dataBase['localidade'];
                                $resultado['estado'] = $dataBase['uf'];
                                $resultado['bairro'] = $dataBase['bairro'] ?? null;
                            }
                        }
                    } catch (\Throwable $e) {
                        // continua para prefixo regional
                    }
                }
            }

            // 4. Fallback Regional (Maringá e Região Metropolitana)
            if (empty($resultado['cidade'])) {
                $prefixosRegionais = [
                    '870' => ['cidade' => 'Maringá', 'estado' => 'PR', 'lat' => -23.4209, 'lon' => -51.9331],
                    '8711' => ['cidade' => 'Sarandi', 'estado' => 'PR', 'lat' => -23.4436, 'lon' => -51.8742],
                    '8712' => ['cidade' => 'Marialva', 'estado' => 'PR', 'lat' => -23.4853, 'lon' => -51.7928],
                    '8714' => ['cidade' => 'Paiçandu', 'estado' => 'PR', 'lat' => -23.4575, 'lon' => -52.0486],
                    '8716' => ['cidade' => 'Mandaguaçu', 'estado' => 'PR', 'lat' => -23.3469, 'lon' => -52.0950],
                    '8717' => ['cidade' => 'Mandaguari', 'estado' => 'PR', 'lat' => -23.5303, 'lon' => -51.7744],
                    '8718' => ['cidade' => 'Jandaia do Sul', 'estado' => 'PR', 'lat' => -23.6033, 'lon' => -51.6444],
                    '8720' => ['cidade' => 'Cianorte', 'estado' => 'PR', 'lat' => -23.6617, 'lon' => -52.6078],
                    '8730' => ['cidade' => 'Campo Mourão', 'estado' => 'PR', 'lat' => -24.0458, 'lon' => -52.3786],
                    '8770' => ['cidade' => 'Paranavaí', 'estado' => 'PR', 'lat' => -23.0833, 'lon' => -52.4647],
                    '860' => ['cidade' => 'Londrina', 'estado' => 'PR', 'lat' => -23.3045, 'lon' => -51.1696],
                ];

                foreach ($prefixosRegionais as $pref => $info) {
                    if (str_starts_with($cepLimpo, $pref)) {
                        $resultado['cidade'] = $info['cidade'];
                        $resultado['estado'] = $info['estado'];
                        $resultado['latitude'] = $info['lat'];
                        $resultado['longitude'] = $info['lon'];
                        break;
                    }
                }
            }

            // Se não encontrou nem cidade após todos os fallbacks, retorna null
            if (empty($resultado['cidade'])) {
                return null;
            }

            // 3. Fallback de GPS (Nominatim OpenStreetMap) se ainda não tem coordenadas
            if (empty($resultado['latitude'])) {
                try {
                    $busca = !empty($resultado['logradouro'])
                        ? "{$resultado['logradouro']}, {$resultado['cidade']} - {$resultado['estado']}, Brasil"
                        : "{$resultado['cidade']} - {$resultado['estado']}, Brasil";

                    $resOsm = Http::timeout(4)
                        ->withoutVerifying()
                        ->withUserAgent('IntalentiVagasLaravel/2.0 (contato@intalenti.com.br)')
                        ->get('https://nominatim.openstreetmap.org/search', [
                            'q' => $busca,
                            'format' => 'json',
                            'limit' => 1,
                            'addressdetails' => 1
                        ]);

                    if ($resOsm->successful() && !empty($resOsm->json()[0])) {
                        $first = $resOsm->json()[0];
                        $resultado['latitude'] = (float) $first['lat'];
                        $resultado['longitude'] = (float) $first['lon'];
                    }
                } catch (\Throwable $e) {
                    Log::warning("Nominatim falhou para coordenadas do CEP {$cepLimpo}: " . $e->getMessage());
                }
            }

            return $resultado;
        });
    }

    /**
     * Calcula distância em km entre dois pontos geográficos (Fórmula de Haversine).
     * Retorna null se alguma coordenada for nula, com formatação amigável.
     */
    public function calcularDistancia(?float $lat1, ?float $lon1, ?float $lat2, ?float $lon2): ?float
    {
        if ($lat1 === null || $lon1 === null || $lat2 === null || $lon2 === null) {
            return null;
        }

        $raioTerraKm = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($raioTerraKm * $c, 1);
    }

    /**
     * Calcula Bounding Box para filtro indexado de latitude/longitude no banco.
     */
    public function getBoundingBox(float $lat, float $lng, float $distanceKm): array
    {
        $R = 6371;

        $dLat = $distanceKm / $R;
        $minLat = $lat - rad2deg($dLat);
        $maxLat = $lat + rad2deg($dLat);

        $cosLat = cos(deg2rad($lat));
        if ($cosLat < 0.00001) {
            $cosLat = 0.00001;
        }

        $dLon = asin(min(1, sin($distanceKm / $R) / $cosLat));
        $minLng = $lng - rad2deg($dLon);
        $maxLng = $lng + rad2deg($dLon);

        return [
            'min_lat' => $minLat,
            'max_lat' => $maxLat,
            'min_lng' => $minLng,
            'max_lng' => $maxLng
        ];
    }

    /**
     * Retorna coordenadas aproximadas a partir de CEP, cidade ou endereço com fallback confiável.
     */
    public function getCoordenadasAproximadas(?string $cep = null, ?string $cidade = null, ?string $estado = null): ?array
    {
        if (!empty($cep)) {
            $geo = $this->consultarCep($cep);
            if ($geo && !empty($geo['latitude']) && !empty($geo['longitude'])) {
                return ['lat' => (float) $geo['latitude'], 'lng' => (float) $geo['longitude']];
            }
        }

        $cidadesPadrao = [
            'maringá' => ['lat' => -23.4209, 'lng' => -51.9331],
            'maringa' => ['lat' => -23.4209, 'lng' => -51.9331],
            'sarandi' => ['lat' => -23.4436, 'lng' => -51.8742],
            'marialva' => ['lat' => -23.4853, 'lng' => -51.7928],
            'paiçandu' => ['lat' => -23.4575, 'lng' => -52.0486],
            'paicandu' => ['lat' => -23.4575, 'lng' => -52.0486],
            'mandaguaçu' => ['lat' => -23.3469, 'lng' => -52.0950],
            'mandaguacu' => ['lat' => -23.3469, 'lng' => -52.0950],
            'mandaguari' => ['lat' => -23.5303, 'lng' => -51.7744],
            'londrina' => ['lat' => -23.3045, 'lng' => -51.1696],
            'curitiba' => ['lat' => -25.4284, 'lng' => -49.2733],
            'cascavel' => ['lat' => -24.9578, 'lng' => -53.4595],
            'florianópolis' => ['lat' => -27.5954, 'lng' => -48.5480],
            'florianopolis' => ['lat' => -27.5954, 'lng' => -48.5480],
        ];

        if (!empty($cidade)) {
            $key = strtolower(trim(preg_replace('/[^\p{L}\p{N}\s]/u', '', $cidade)));
            foreach ($cidadesPadrao as $nome => $coords) {
                if (str_contains($key, $nome)) {
                    return $coords;
                }
            }
        }

        return ['lat' => -23.4209, 'lng' => -51.9331];
    }
}
