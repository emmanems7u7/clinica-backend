<?php

namespace Database\Seeders\Support;

/** Datos ficticios con sabor boliviano para poblar la clínica. */
class BoliviaData
{
    public const MALE = ['Juan', 'Carlos', 'Luis', 'José', 'Miguel', 'Pedro', 'Diego', 'Marco', 'Andrés', 'Fernando', 'Ricardo', 'Álvaro', 'Daniel', 'Rodrigo', 'Gustavo', 'Sergio', 'Mauricio', 'Iván', 'Wilson', 'Edgar'];
    public const FEMALE = ['María', 'Ana', 'Carla', 'Lucía', 'Patricia', 'Rosa', 'Verónica', 'Daniela', 'Silvia', 'Paola', 'Gabriela', 'Fabiola', 'Claudia', 'Nadia', 'Alejandra', 'Karen', 'Mónica', 'Teresa', 'Marisol', 'Jimena'];
    public const SURNAMES = ['Mamani', 'Quispe', 'Flores', 'Condori', 'Choque', 'Rojas', 'Vargas', 'Gutiérrez', 'Fernández', 'Copa', 'Limachi', 'Huanca', 'Villca', 'Apaza', 'Mendoza', 'Torrez', 'Salazar', 'Calle', 'Ticona', 'Aliaga', 'Rivera', 'Callisaya', 'Poma', 'Cussi', 'Zeballos', 'Paredes', 'Arce', 'Montes', 'Nina', 'Yujra'];
    public const ZONES = ['Sopocachi', 'Miraflores', 'Calacoto', 'San Miguel', 'Obrajes', 'Achumani', 'Villa Fátima', 'Cota Cota', 'San Pedro', 'Irpavi', 'Los Pinos', 'Alto Obrajes', 'Següencoma', 'Villa Copacabana'];
    public const STREETS = ['Av. Arce', 'Calle 21', 'Av. Ballivián', 'Calle Rosendo Gutiérrez', 'Av. 6 de Agosto', 'Calle Jaimes Freyre', 'Av. Busch', 'Calle Capitán Ravelo', 'Av. Hernando Siles', 'Calle 15'];
    public const CI_EXT = ['LP', 'LP', 'LP', 'LP', 'CB', 'SC', 'OR', 'PT'];

    public static function pick(array $a)
    {
        return $a[array_rand($a)];
    }

    /** Devuelve [nombres, apellidos, género]. */
    public static function person(): array
    {
        $gender = random_int(0, 1) ? 'male' : 'female';
        $pool = $gender === 'male' ? self::MALE : self::FEMALE;
        $first = self::pick($pool) . (random_int(1, 100) <= 35 ? ' ' . self::pick($pool) : '');
        $last = self::pick(self::SURNAMES) . ' ' . self::pick(self::SURNAMES);

        return [$first, $last, $gender];
    }

    public static function phone(): string
    {
        return (string) random_int(60000000, 79999999);
    }

    public static function address(): string
    {
        return self::pick(self::STREETS) . ' #' . random_int(100, 2900) . ', ' . self::pick(self::ZONES) . ', La Paz';
    }

    public static function slug(string $s): string
    {
        $s = strtr(mb_strtolower($s), ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n', 'ü' => 'u']);

        return trim(preg_replace('/[^a-z0-9]+/', '.', $s), '.');
    }
}
