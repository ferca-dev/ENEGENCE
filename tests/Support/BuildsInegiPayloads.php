<?php

namespace Tests\Support;

trait BuildsInegiPayloads
{
    /**
     * @param  array<string, array<string, mixed>>  $overrides
     * @return array{datos: list<array<string, mixed>>, numReg: int}
     */
    protected function statesPayload(array $overrides = [], int $count = 32): array
    {
        $items = [];

        foreach (range(1, $count) as $number) {
            $code = str_pad((string) $number, 2, '0', STR_PAD_LEFT);
            $totalPopulation = $code === '01' ? 1425607 : 100000 + $number;
            $femalePopulation = $code === '01' ? 728924 : intdiv($totalPopulation, 2);
            $malePopulation = $code === '01' ? 696683 : $totalPopulation - $femalePopulation;
            $items[] = array_merge([
                'cve_ent' => $code,
                'nomgeo' => $code === '01' ? 'Aguascalientes' : "Estado {$code}",
                'nom_abrev' => $code === '01' ? 'Ags.' : "E{$code}",
                'pob_total' => (string) $totalPopulation,
                'pob_femenina' => (string) $femalePopulation,
                'pob_masculina' => (string) $malePopulation,
                'total_viviendas_habitadas' => $code === '01' ? '386671' : (string) (30000 + $number),
            ], $overrides[$code] ?? []);
        }

        return [
            'datos' => $items,
            'numReg' => count($items),
        ];
    }

    /**
     * @return array{datos: list<array<string, string>>, numReg: int}
     */
    protected function municipalitiesPayload(string $stateCode = '01'): array
    {
        $items = [
            [
                'cve_ent' => $stateCode,
                'cve_mun' => '003',
                'nomgeo' => 'Calvillo',
                'pob_total' => '58250',
            ],
            [
                'cve_ent' => $stateCode,
                'cve_mun' => '001',
                'nomgeo' => 'Aguascalientes',
                'pob_total' => '948990',
            ],
            [
                'cve_ent' => $stateCode,
                'cve_mun' => '002',
                'nomgeo' => 'Asientos',
                'pob_total' => '51536',
            ],
        ];

        return [
            'datos' => $items,
            'numReg' => count($items),
        ];
    }
}
