<?php

namespace App\Models;

use CodeIgniter\Model;

class ReportSectionModel extends Model
{
    protected $table            = 'report_sections';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'report_id',
        'section_level',
        'questionnaire_type',
        'domain_code',
        'dimension_code',
        'form_type',
        'score_value',
        'risk_level',
        'distribution_data',
        'ai_generated_text',
        'consultant_comment',
        'consultant_prompt',
        'is_approved',
        'approved_at',
        'approved_by',
        'order_position',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'score_value'       => '?float',
        'is_approved'       => 'boolean',
        'distribution_data' => '?json-array',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'report_id'     => 'required|integer',
        'section_level' => 'required|in_list[executive,total,questionnaire,domain,dimension]',
        'form_type'     => 'required|in_list[A,B,conjunto]',
    ];

    protected $validationMessages = [];
    protected $skipValidation     = false;

    /**
     * Obtener todas las secciones de un informe ordenadas
     */
    public function getSectionsByReport(int $reportId): array
    {
        return $this->where('report_id', $reportId)
                    ->orderBy('order_position', 'ASC')
                    ->findAll();
    }

    /**
     * Obtener secciones pendientes de aprobación
     */
    public function getPendingSections(int $reportId): array
    {
        return $this->where('report_id', $reportId)
                    ->where('is_approved', 0)
                    ->orderBy('order_position', 'ASC')
                    ->findAll();
    }

    /**
     * Obtener resumen ejecutivo
     */
    public function getExecutiveSummary(int $reportId): ?array
    {
        return $this->where('report_id', $reportId)
                    ->where('section_level', 'executive')
                    ->first();
    }

    /**
     * Obtener secciones por nivel
     */
    public function getSectionsByLevel(int $reportId, string $level): array
    {
        return $this->where('report_id', $reportId)
                    ->where('section_level', $level)
                    ->orderBy('order_position', 'ASC')
                    ->findAll();
    }

    /**
     * Obtener secciones por tipo de cuestionario y forma
     */
    public function getSectionsByQuestionnaireAndForm(int $reportId, string $questionnaireType, string $formType): array
    {
        return $this->where('report_id', $reportId)
                    ->where('questionnaire_type', $questionnaireType)
                    ->where('form_type', $formType)
                    ->orderBy('order_position', 'ASC')
                    ->findAll();
    }

    /**
     * Aprobar una sección
     */
    public function approveSection(int $sectionId, ?int $userId = null): bool
    {
        return $this->update($sectionId, [
            'is_approved' => 1,
            'approved_at' => date('Y-m-d H:i:s'),
            'approved_by' => $userId,
        ]);
    }

    /**
     * Aprobar todas las secciones de un informe
     */
    public function approveAllSections(int $reportId, ?int $userId = null): bool
    {
        return $this->where('report_id', $reportId)
                    ->set([
                        'is_approved' => 1,
                        'approved_at' => date('Y-m-d H:i:s'),
                        'approved_by' => $userId,
                    ])
                    ->update();
    }

    /**
     * Verificar si todas las secciones están aprobadas
     */
    public function allSectionsApproved(int $reportId): bool
    {
        $pending = $this->where('report_id', $reportId)
                        ->where('is_approved', 0)
                        ->countAllResults();
        return $pending === 0;
    }

    /**
     * Agregar comentario del consultor
     */
    public function addConsultantComment(int $sectionId, string $comment): bool
    {
        return $this->update($sectionId, [
            'consultant_comment' => $comment,
        ]);
    }

    /**
     * Guardar prompt complementario del consultor
     * Este prompt se concatena al prompt del sistema para dar contexto adicional a la IA
     */
    public function saveConsultantPrompt(int $sectionId, ?string $prompt): bool
    {
        return $this->update($sectionId, [
            'consultant_prompt' => $prompt,
        ]);
    }

    /**
     * Resetear una sección para poder regenerarla con IA
     * Limpia el texto generado y la aprobación, pero conserva el prompt del consultor
     */
    public function resetSection(int $sectionId): bool
    {
        return $this->update($sectionId, [
            'ai_generated_text' => null,
            'is_approved' => 0,
            'approved_at' => null,
            'approved_by' => null,
        ]);
    }

    /**
     * Desaprobar una sección (sin borrar el texto)
     */
    public function unapproveSection(int $sectionId): bool
    {
        return $this->update($sectionId, [
            'is_approved' => 0,
            'approved_at' => null,
            'approved_by' => null,
        ]);
    }

    /**
     * Obtener estadísticas de aprobación del informe
     */
    public function getApprovalStats(int $reportId): array
    {
        $total = $this->where('report_id', $reportId)->countAllResults(false);
        $approved = $this->where('report_id', $reportId)
                         ->where('is_approved', 1)
                         ->countAllResults();

        return [
            'total'      => $total,
            'approved'   => $approved,
            'pending'    => $total - $approved,
            'percentage' => $total > 0 ? round(($approved / $total) * 100, 1) : 0,
        ];
    }
}
