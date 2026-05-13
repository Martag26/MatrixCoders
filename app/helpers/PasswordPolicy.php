<?php
/**
 * Política de contraseñas compartida.
 *
 * Regla actual: mínimo 10 caracteres, al menos una mayúscula, una minúscula
 * y un dígito. No exige símbolos para no frustrar a usuarios normales,
 * pero la longitud + complejidad ya resiste muy bien fuerza bruta.
 */
class PasswordPolicy
{
    public const MIN_LEN = 10;

    /**
     * Devuelve null si la contraseña es válida; si no, un mensaje de error
     * legible para enseñar al usuario.
     */
    public static function validar(string $pass): ?string
    {
        if (mb_strlen($pass) < self::MIN_LEN) {
            return 'La contraseña debe tener al menos ' . self::MIN_LEN . ' caracteres.';
        }
        if (!preg_match('/[A-Z]/', $pass)) {
            return 'La contraseña debe contener al menos una letra mayúscula.';
        }
        if (!preg_match('/[a-z]/', $pass)) {
            return 'La contraseña debe contener al menos una letra minúscula.';
        }
        if (!preg_match('/\d/', $pass)) {
            return 'La contraseña debe contener al menos un número.';
        }
        return null;
    }
}
