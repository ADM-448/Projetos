<?php

namespace src\Utils;

class GeoUtils
{

    public static function validateCEP($cep)
    {
        $cepLimpo = preg_replace('/[^0-9]/', '', $cep);

        if (strlen($cepLimpo) != 8) return false;

        $resultado = [
            'street' => null,
            'neighborhood' => null,
            'city' => null,
            'state' => null,
            'latitude' => null,
            'longitude' => null
        ];

        // 1. BrasilAPI
        $ch = curl_init("https://brasilapi.com.br/api/cep/v2/{$cepLimpo}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200) {
            $data = json_decode($response, true);
            $resultado['street'] = $data['street'] ?? null;
            $resultado['neighborhood'] = $data['neighborhood'] ?? null;
            $resultado['city'] = $data['city'] ?? null;
            $resultado['state'] = $data['state'] ?? null;
            if (isset($data['location']['coordinates']['latitude'])) {
                $resultado['latitude'] = $data['location']['coordinates']['latitude'];
                $resultado['longitude'] = $data['location']['coordinates']['longitude'];
            }
        }

        // 2. ViaCEP (Fallback)
        if (empty($resultado['city'])) {
            $ch2 = curl_init("https://viacep.com.br/ws/{$cepLimpo}/json/");
            curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch2, CURLOPT_TIMEOUT, 4);
            $response2 = curl_exec($ch2);
            $data2 = json_decode($response2, true);
            curl_close($ch2);

            if (!isset($data2['erro']) && isset($data2['localidade'])) {
                $resultado['street'] = $data2['logradouro'] ?? null;
                $resultado['neighborhood'] = $data2['bairro'] ?? null;
                $resultado['city'] = $data2['localidade'];
                $resultado['state'] = $data2['uf'];
            }
        }

        // 3. Nominatim (GPS Fallback)
        if (!empty($resultado['city']) && empty($resultado['latitude'])) {
            $busca = $cepLimpo;
            if (!empty($resultado['street'])) {
                $busca = $resultado['street'] . ", " . $resultado['city'] . " - " . $resultado['state'] . ", Brasil";
            } else {
                $busca = $resultado['city'] . " - " . $resultado['state'] . ", Brasil";
            }

            $query_string = http_build_query(['q' => $busca, 'format' => 'json', 'limit' => 1, 'addressdetails' => 1]);
            $ch3 = curl_init("https://nominatim.openstreetmap.org/search?" . $query_string);
            curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch3, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch3, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch3, CURLOPT_USERAGENT, "IntalentiVagas/1.0 (contato@intalenti.com.br)");

            $response3 = curl_exec($ch3);
            $data3 = json_decode($response3, true);
            curl_close($ch3);

            if (!empty($data3[0])) {
                $resultado['latitude'] = $data3[0]['lat'];
                $resultado['longitude'] = $data3[0]['lon'];
            }
        }

        if (empty($resultado['city'])) return false;

        return $resultado;
    }
    /**
     * Calcula um quadrado (Bounding Box) ao redor do ponto para pré-filtrar no banco.
     * Isso permite usar INDEX no banco (lat/lng) e evita Full Table Scan com cosseno.
     */
    public static function getBoundingBox($lat, $lng, $distanceKm)
    {
        $lat = (float) $lat;
        $lng = (float) $lng;

        // Raio da Terra
        $R = 6371;

        // 1 grau de latitude ~ 111km
        // Variação de latitude é simples:
        $dLat = $distanceKm / $R;
        $minLat = $lat - rad2deg($dLat);
        $maxLat = $lat + rad2deg($dLat);

        // Variação de longitude depende da latitude (fica menor perto dos polos)
        // dLon = distancia / (R * cos(lat))
        $dLon = asin(sin($distanceKm / $R) / cos(deg2rad($lat)));

        $minLng = $lng - rad2deg($dLon);
        $maxLng = $lng + rad2deg($dLon);

        return [
            'min_lat' => $minLat,
            'max_lat' => $maxLat,
            'min_lng' => $minLng,
            'max_lng' => $maxLng
        ];
    }
}
