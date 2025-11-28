<?php

namespace Config;

/**
 * Cuestionario de Factores de Riesgo Psicosocial Extralaboral
 * 31 preguntas
 * Aplica para todas las formas (A y B)
 */
class Extralaboral
{
    /**
     * Escala de respuesta Likert
     */
    public static $likertScale = [
        0 => 'Siempre',
        1 => 'Casi siempre',
        2 => 'Algunas veces',
        3 => 'Casi nunca',
        4 => 'Nunca'
    ];

    /**
     * Encabezados de sección
     */
    public static $sectionHeaders = [
        1 => 'Las siguientes preguntas están relacionadas con varias condiciones de la zona donde usted vive:',
        14 => 'Las siguientes preguntas están relacionadas con su vida fuera del trabajo:'
    ];

    /**
     * Las 31 preguntas del cuestionario Extralaboral
     * Textos exactos del Ministerio de la Protección Social de Colombia
     */
    public static $questions = [
        1 => 'Es fácil trasportarme entre mi casa y el trabajo',
        2 => 'Tengo que tomar varios medios de transporte para llegar a mi lugar de trabajo',
        3 => 'Paso mucho tiempo viajando de ida y regreso al trabajo',
        4 => 'Me trasporto cómodamente entre mi casa y el trabajo',
        5 => 'La zona donde vivo es segura',
        6 => 'En la zona donde vivo se presentan hurtos y mucha delincuencia',
        7 => 'Desde donde vivo me es fácil llegar al centro médico donde me atienden',
        8 => 'Cerca a mi vivienda las vías están en buenas condiciones',
        9 => 'Cerca a mi vivienda encuentro fácilmente transporte',
        10 => 'Las condiciones de mi vivienda son buenas',
        11 => 'En mi vivienda hay servicios de agua y luz',
        12 => 'Las condiciones de mi vivienda me permiten descansar cuando lo requiero',
        13 => 'Las condiciones de mi vivienda me permiten sentirme cómodo',
        14 => 'Me queda tiempo para actividades de recreación',
        15 => 'Fuera del trabajo tengo tiempo suficiente para descansar',
        16 => 'Tengo tiempo para atender mis asuntos personales y del hogar',
        17 => 'Tengo tiempo para compartir con mi familia o amigos',
        18 => 'Tengo buena comunicación con las personas cercanas',
        19 => 'Las relaciones con mis amigos son buenas',
        20 => 'Converso con personas cercanas sobre diferentes temas',
        21 => 'Mis amigos están dispuestos a escucharme cuando tengo problemas',
        22 => 'Cuento con el apoyo de mi familia cuando tengo problemas',
        23 => 'Puedo hablar con personas cercanas sobre las cosas que me pasan',
        24 => 'Mis problemas personales o familiares afectan mi trabajo',
        25 => 'La relación con mi familia cercana es cordial',
        26 => 'Mis problemas personales o familiares me afectan físicamente',
        27 => 'La relación con mis amigos es cordial',
        28 => 'Mis relaciones familiares son cordiales',
        29 => 'Paso tiempo agradable con mis familiares',
        30 => 'Me gusta compartir con mis familiares o amigos en mi tiempo libre',
        31 => 'Tengo buena relación con las personas cercanas'
    ];

    /**
     * Get all questions
     */
    public static function getQuestions()
    {
        return self::$questions;
    }

    /**
     * Get likert scale
     */
    public static function getLikertScale()
    {
        return self::$likertScale;
    }

    /**
     * Get section headers
     */
    public static function getSectionHeaders()
    {
        return self::$sectionHeaders;
    }

    /**
     * Get total number of questions
     */
    public static function getTotalQuestions()
    {
        return count(self::$questions);
    }
}
