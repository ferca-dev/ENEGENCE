<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use UnexpectedValueException;

class InegiService
{
    /**
     * @return list<array{
     *     code: string,
     *     name: string,
     *     abbreviation: string,
     *     total_population: int,
     *     female_population: int,
     *     male_population: int,
     *     inhabited_dwellings: int
     * }>
     */
    public function states(): array
    {
        $response = $this->get('mgee/');

        $items = $response->json('datos');

        if (! is_array($items) || $items === []) {
            throw new UnexpectedValueException('INEGI no devolvió entidades válidas.');
        }

        $numReg = $response->json('numReg');

        if ((int) $numReg !== count($items)) {
            throw new UnexpectedValueException('El total reportado por INEGI no coincide con los datos.');
        }

        $states = array_map($this->mapState(...), $items);
        usort($states, fn (array $first, array $second): int => $first['code'] <=> $second['code']);

        $codes = array_column($states, 'code');

        if (count($codes) !== count(array_unique($codes))) {
            throw new UnexpectedValueException('INEGI devolvió claves estatales duplicadas.');
        }

        return $states;
    }

    /**
     * @return list<array{code: string, name: string, total_population: int|null}>
     */
    public function municipalities(string $stateCode): array
    {
        if (preg_match('/^\d{2}$/D', $stateCode) !== 1) {
            throw new InvalidArgumentException('La clave estatal debe contener dos dígitos.');
        }

        $response = $this->get("mgem/{$stateCode}");
        $items = $response->json('datos');

        if (! is_array($items) || $items === []) {
            throw new UnexpectedValueException('INEGI no devolvió municipios válidos.');
        }

        if ((int) $response->json('numReg') !== count($items)) {
            throw new UnexpectedValueException('El total de municipios reportado por INEGI no coincide con los datos.');
        }

        $municipalities = array_map(
            fn (mixed $item): array => $this->mapMunicipality($item, $stateCode),
            $items,
        );
        usort($municipalities, fn (array $first, array $second): int => $first['code'] <=> $second['code']);

        $codes = array_column($municipalities, 'code');

        if (count($codes) !== count(array_unique($codes))) {
            throw new UnexpectedValueException('INEGI devolvió claves municipales duplicadas.');
        }

        return $municipalities;
    }

    /**
     * @return array{
     *     code: string,
     *     name: string,
     *     abbreviation: string,
     *     total_population: int,
     *     female_population: int,
     *     male_population: int,
     *     inhabited_dwellings: int
     * }
     */
    private function mapState(mixed $item): array
    {
        if (! is_array($item)) {
            throw new UnexpectedValueException('INEGI devolvió una entidad con formato inválido.');
        }

        $code = $item['cve_ent'] ?? null;
        $name = $item['nomgeo'] ?? null;
        $abbreviation = $item['nom_abrev'] ?? null;

        if (! is_string($code) || preg_match('/^\d{2}$/D', $code) !== 1) {
            throw new UnexpectedValueException('INEGI devolvió una clave estatal inválida.');
        }

        if (! is_string($name) || trim($name) === '') {
            throw new UnexpectedValueException('INEGI devolvió un nombre estatal inválido.');
        }

        if (! is_string($abbreviation) || trim($abbreviation) === '') {
            throw new UnexpectedValueException('INEGI devolvió una abreviatura estatal inválida.');
        }

        return [
            'code' => $code,
            'name' => trim($name),
            'abbreviation' => trim($abbreviation),
            'total_population' => $this->mapNonNegativeInteger($item['pob_total'] ?? null, 'población total estatal'),
            'female_population' => $this->mapNonNegativeInteger($item['pob_femenina'] ?? null, 'población femenina estatal'),
            'male_population' => $this->mapNonNegativeInteger($item['pob_masculina'] ?? null, 'población masculina estatal'),
            'inhabited_dwellings' => $this->mapNonNegativeInteger($item['total_viviendas_habitadas'] ?? null, 'total de viviendas habitadas'),
        ];
    }

    /**
     * @return array{code: string, name: string, total_population: int|null}
     */
    private function mapMunicipality(mixed $item, string $stateCode): array
    {
        if (! is_array($item)) {
            throw new UnexpectedValueException('INEGI devolvió un municipio con formato inválido.');
        }

        $itemStateCode = $item['cve_ent'] ?? null;
        $code = $item['cve_mun'] ?? null;
        $name = $item['nomgeo'] ?? null;
        $population = $item['pob_total'] ?? null;

        if ($itemStateCode !== null && $itemStateCode !== $stateCode) {
            throw new UnexpectedValueException('INEGI devolvió un municipio de otra entidad.');
        }

        if (! is_string($code) || preg_match('/^\d{3}$/D', $code) !== 1) {
            throw new UnexpectedValueException('INEGI devolvió una clave municipal inválida.');
        }

        if (! is_string($name) || trim($name) === '') {
            throw new UnexpectedValueException('INEGI devolvió un nombre municipal inválido.');
        }

        if ($population !== null && ((! is_string($population) && ! is_int($population)) || ! ctype_digit((string) $population))) {
            throw new UnexpectedValueException('INEGI devolvió una población municipal inválida.');
        }

        return [
            'code' => $code,
            'name' => trim($name),
            'total_population' => $population === null ? null : (int) $population,
        ];
    }

    private function get(string $path): Response
    {
        $baseUrl = rtrim((string) config('services.inegi.base_url'), '/').'/';

        return Http::baseUrl($baseUrl)
            ->acceptJson()
            ->connectTimeout((int) config('services.inegi.connect_timeout'))
            ->timeout((int) config('services.inegi.timeout'))
            ->retry(
                (int) config('services.inegi.retries'),
                (int) config('services.inegi.retry_sleep_ms'),
            )
            ->get($path)
            ->throw();
    }

    private function mapNonNegativeInteger(mixed $value, string $field): int
    {
        if ((! is_string($value) && ! is_int($value)) || ! ctype_digit((string) $value)) {
            throw new UnexpectedValueException("INEGI devolvió un valor inválido para {$field}.");
        }

        return (int) $value;
    }
}
