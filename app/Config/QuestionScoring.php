<?php

namespace Config;

/**
 * Configuración de calificación de preguntas
 * Basado en las tablas oficiales del Ministerio de la Protección Social de Colombia
 *
 * NORMAL: Siempre=0, Casi siempre=1, Algunas veces=2, Casi nunca=3, Nunca=4
 * INVERTIDA: Siempre=4, Casi siempre=3, Algunas veces=2, Casi nunca=1, Nunca=0
 */
class QuestionScoring
{
    /**
     * Intralaboral Forma A - Preguntas INVERTIDAS (Siempre=4)
     * Según Tabla 21 de la batería oficial
     */
    public static $intralaboralA_invertidas = [
        1, 2, 3, 7, 8, 10, 11, 13, 15, 16, 17,
        18, 19, 20, 21, 23, 24, 25, 26, 27, 28,
        29, 31, 35, 36, 37, 38, 52, 80, 106, 107,
        108, 109, 110, 111, 112, 113, 114, 115, 116, 117,
        118, 119, 120, 121, 122, 123
    ];

    /**
     * Intralaboral Forma B - Preguntas INVERTIDAS (Siempre=4)
     * Según Tabla 22 de la batería oficial
     */
    public static $intralaboralB_invertidas = [
        1, 2, 3, 7, 8, 10, 11, 13, 15, 16, 17,
        18, 19, 20, 21, 23, 25, 26, 27, 28, 66,
        89, 90, 91, 92, 93, 94, 95, 96
    ];

    /**
     * Extralaboral - Preguntas INVERTIDAS (Siempre=4)
     * Según Tabla 11 de la batería oficial
     */
    public static $extralaboral_invertidas = [
        2, 3, 6, 24, 26, 28, 30, 31
    ];

    /**
     * Verifica si una pregunta de Intralaboral A es invertida
     */
    public static function isIntralaboralAInverted($questionNumber)
    {
        return in_array($questionNumber, self::$intralaboralA_invertidas);
    }

    /**
     * Verifica si una pregunta de Intralaboral B es invertida
     */
    public static function isIntralaboralBInverted($questionNumber)
    {
        return in_array($questionNumber, self::$intralaboralB_invertidas);
    }

    /**
     * Verifica si una pregunta de Extralaboral es invertida
     */
    public static function isExtralaboralInverted($questionNumber)
    {
        return in_array($questionNumber, self::$extralaboral_invertidas);
    }

    /**
     * Califica una respuesta de Intralaboral/Extralaboral según si es invertida o no
     *
     * @param int $likertValue Valor del Likert (0=Siempre, 1=Casi siempre, 2=Algunas veces, 3=Casi nunca, 4=Nunca)
     * @param bool $isInverted Si la pregunta es invertida
     * @return int Puntaje calculado (0-4)
     */
    public static function scoreQuestion($likertValue, $isInverted)
    {
        if ($isInverted) {
            // Invertir: Siempre(0)=4, Casi siempre(1)=3, Algunas veces(2)=2, Casi nunca(3)=1, Nunca(4)=0
            return 4 - $likertValue;
        } else {
            // Normal: Siempre(0)=0, Casi siempre(1)=1, Algunas veces(2)=2, Casi nunca(3)=3, Nunca(4)=4
            return $likertValue;
        }
    }

    /**
     * Califica una respuesta de Estrés
     * Escala: Siempre=9, Casi siempre=6, A veces=3, Nunca=0
     *
     * @param int $likertValue Valor del Likert (0=Siempre, 1=Casi siempre, 2=A veces, 3=Nunca)
     * @return int Puntaje calculado (0, 3, 6, 9)
     */
    public static function scoreEstres($likertValue)
    {
        $map = [
            0 => 9,  // Siempre
            1 => 6,  // Casi siempre
            2 => 3,  // A veces
            3 => 0   // Nunca
        ];
        return $map[$likertValue] ?? 0;
    }
}
