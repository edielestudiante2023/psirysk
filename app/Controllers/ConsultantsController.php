<?php

namespace App\Controllers;

use App\Models\ConsultantModel;
use CodeIgniter\Controller;

class ConsultantsController extends Controller
{
    protected $consultantModel;

    public function __construct()
    {
        $this->consultantModel = new ConsultantModel();
    }

    /**
     * Lista de consultores
     */
    public function index()
    {
        $data = [
            'title' => 'Consultores',
            'consultants' => $this->consultantModel->orderBy('nombre_completo', 'ASC')->findAll(),
        ];

        return view('consultants/index', $data);
    }

    /**
     * Formulario para crear consultor
     */
    public function create()
    {
        $data = [
            'title' => 'Nuevo Consultor',
        ];

        return view('consultants/create', $data);
    }

    /**
     * Guardar nuevo consultor
     */
    public function store()
    {
        $rules = [
            'nombre_completo' => 'required|min_length[3]|max_length[150]',
            'tipo_documento' => 'required|in_list[CC,CE,PAS,OTRO]',
            'numero_documento' => 'required|max_length[30]',
            'email' => 'permit_empty|valid_email|max_length[150]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nombre_completo' => $this->request->getPost('nombre_completo'),
            'tipo_documento' => $this->request->getPost('tipo_documento'),
            'numero_documento' => $this->request->getPost('numero_documento'),
            'licencia_sst' => $this->request->getPost('licencia_sst'),
            'cargo' => $this->request->getPost('cargo'),
            'email' => $this->request->getPost('email'),
            'telefono' => $this->request->getPost('telefono'),
            'website' => $this->request->getPost('website'),
            'linkedin' => $this->request->getPost('linkedin'),
            'activo' => $this->request->getPost('activo') ? 1 : 0,
        ];

        // Manejar subida de firma
        $firma = $this->request->getFile('firma');
        if ($firma && $firma->isValid() && !$firma->hasMoved()) {
            $newName = $firma->getRandomName();
            $firma->move(FCPATH . 'uploads/firmas', $newName);
            $data['firma_path'] = 'uploads/firmas/' . $newName;
        }

        $this->consultantModel->insert($data);

        return redirect()->to('/consultants')->with('success', 'Consultor creado exitosamente');
    }

    /**
     * Ver detalle de consultor
     */
    public function show($id)
    {
        $consultant = $this->consultantModel->find($id);

        if (!$consultant) {
            return redirect()->to('/consultants')->with('error', 'Consultor no encontrado');
        }

        $data = [
            'title' => 'Detalle del Consultor',
            'consultant' => $consultant,
        ];

        return view('consultants/show', $data);
    }

    /**
     * Formulario para editar consultor
     */
    public function edit($id)
    {
        $consultant = $this->consultantModel->find($id);

        if (!$consultant) {
            return redirect()->to('/consultants')->with('error', 'Consultor no encontrado');
        }

        $data = [
            'title' => 'Editar Consultor',
            'consultant' => $consultant,
        ];

        return view('consultants/edit', $data);
    }

    /**
     * Actualizar consultor
     */
    public function update($id)
    {
        $consultant = $this->consultantModel->find($id);

        if (!$consultant) {
            return redirect()->to('/consultants')->with('error', 'Consultor no encontrado');
        }

        $rules = [
            'nombre_completo' => 'required|min_length[3]|max_length[150]',
            'tipo_documento' => 'required|in_list[CC,CE,PAS,OTRO]',
            'numero_documento' => 'required|max_length[30]',
            'email' => 'permit_empty|valid_email|max_length[150]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nombre_completo' => $this->request->getPost('nombre_completo'),
            'tipo_documento' => $this->request->getPost('tipo_documento'),
            'numero_documento' => $this->request->getPost('numero_documento'),
            'licencia_sst' => $this->request->getPost('licencia_sst'),
            'cargo' => $this->request->getPost('cargo'),
            'email' => $this->request->getPost('email'),
            'telefono' => $this->request->getPost('telefono'),
            'website' => $this->request->getPost('website'),
            'linkedin' => $this->request->getPost('linkedin'),
            'activo' => $this->request->getPost('activo') ? 1 : 0,
        ];

        // Manejar subida de firma
        $firma = $this->request->getFile('firma');
        if ($firma && $firma->isValid() && !$firma->hasMoved()) {
            // Eliminar firma anterior si existe
            if (!empty($consultant['firma_path']) && file_exists(FCPATH . $consultant['firma_path'])) {
                unlink(FCPATH . $consultant['firma_path']);
            }
            $newName = $firma->getRandomName();
            $firma->move(FCPATH . 'uploads/firmas', $newName);
            $data['firma_path'] = 'uploads/firmas/' . $newName;
        }

        $this->consultantModel->update($id, $data);

        return redirect()->to('/consultants')->with('success', 'Consultor actualizado exitosamente');
    }

    /**
     * Eliminar consultor
     */
    public function delete($id)
    {
        $consultant = $this->consultantModel->find($id);

        if (!$consultant) {
            return redirect()->to('/consultants')->with('error', 'Consultor no encontrado');
        }

        // Eliminar firma si existe
        if (!empty($consultant['firma_path']) && file_exists(FCPATH . $consultant['firma_path'])) {
            unlink(FCPATH . $consultant['firma_path']);
        }

        $this->consultantModel->delete($id);

        return redirect()->to('/consultants')->with('success', 'Consultor eliminado exitosamente');
    }
}
