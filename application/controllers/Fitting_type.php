<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Fitting_type extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('user_data')) {
            redirect(base_url());
        }
        $this->load->model('Fitting_type_model');
        $this->load->model('Fitting_subtype_model');
        $this->load->helper(['url', 'file', 'form']);
        $this->load->library('form_validation');
    }

    public function index(): void
    {
        $types = $this->Fitting_type_model->getAllTypes();

        // Add usage information to each type
        foreach ($types as &$type) {
            $type['usage_count'] = $this->Fitting_type_model->getUsageCount($type['type']);
            $type['is_in_use'] = $type['usage_count'] > 0;
        }

        $data['title'] = 'Kelola Type Fitting';
        $data['types'] = $types;
        render_view('fitting/manage_types', $data);
    }

    public function add(): void
    {
        if ($this->input->method() === 'post') {
            $type = $this->input->post('type', true);
            $this->form_validation->set_rules('type', 'Type', 'required|trim|max_length[30]');
            if ($this->form_validation->run() && !$this->Fitting_type_model->isTypeExists($type)) {
                $imageFilename = $this->handleUpload();
                $this->Fitting_type_model->addType($type, $imageFilename);
                set_message(['success', 'Type berhasil ditambahkan']);
                redirect('fitting_type');
            } else {
                set_message(['danger', 'Type sudah ada atau data tidak valid']);
            }
        }
        $data = [
            'title' => 'Tambah Type Fitting',
            'form_action' => site_url('fitting_type/add'),
            'type' => [],
        ];
        render_view('fitting/type_form', $data);
    }

    public function edit(int $id): void
    {
        $typeRow = $this->Fitting_type_model->getById($id);
        if (!$typeRow) {
            set_message(['danger', 'Type tidak ditemukan']);
            redirect('fitting_type');
        }

        if ($this->input->method() === 'post') {
            $type = $this->input->post('type', true);
            $this->form_validation->set_rules('type', 'Type', 'required|trim|max_length[30]');

            if ($this->form_validation->run()) {
                $imageFilename = $this->handleUpload();
                $this->Fitting_type_model->editType($id, $type, $imageFilename);
                set_message(['success', 'Type berhasil diperbarui']);
                redirect('fitting_type');
            }
        }

        // Get subtypes for this type
        $subtypes = $this->Fitting_subtype_model->getSubtypesByParentType($typeRow['type']);

        $data = [
            'title' => 'Edit Type Fitting',
            'form_action' => site_url('fitting_type/edit/' . $id),
            'type' => $typeRow,
            'subtypes' => $subtypes,
        ];
        render_view('fitting/type_form', $data);
    }

    public function delete(int $id): void
    {
        $typeRow = $this->Fitting_type_model->getById($id);
        if (!$typeRow) {
            set_message(['danger', 'Type tidak ditemukan']);
            redirect('fitting_type');
        }

        // Check if type is being used by any fitting records
        if ($this->Fitting_type_model->isTypeInUse($typeRow['type'])) {
            $usageCount = $this->Fitting_type_model->getUsageCount($typeRow['type']);
            set_message(['warning', "Type '{$typeRow['type']}' tidak dapat dihapus karena sedang digunakan oleh {$usageCount} fitting. Hapus terlebih dahulu fitting yang menggunakan type ini."]);
            redirect('fitting_type');
        }

        // delete image file if exists
        if (!empty($typeRow['image'])) {
            $imagePath = FCPATH . 'assets/img/fitting_types/' . $typeRow['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $this->Fitting_type_model->deleteType($id);
        set_message(['success', 'Type berhasil dihapus']);
        redirect('fitting_type');
    }

    /**
     * Get subtypes for a specific type (AJAX endpoint)
     */
    public function getSubtypes(): void
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $type = $this->input->post('type', true);
        if (empty($type)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([]));
            return;
        }

        $subtypes = $this->Fitting_subtype_model->getSubtypesByParentType($type);
        
        // Add usage information for each subtype
        foreach ($subtypes as &$subtype) {
            $subtype['is_in_use'] = $this->Fitting_subtype_model->isSubtypeInUse($type, $subtype['subtype']);
        }
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($subtypes));
    }

    /**
     * Add a new subtype for a specific type
     */
    public function addSubtype(): void
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $parentType = $this->input->post('parent_type', true);
        $subtype = $this->input->post('subtype', true);
        $description = $this->input->post('description', true);

        if (empty($parentType) || empty($subtype)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Parent type dan subtype harus diisi']));
            return;
        }

        // Check if subtype already exists for this parent type
        if ($this->Fitting_subtype_model->isSubtypeExists($parentType, $subtype)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Subtype sudah ada untuk type ini']));
            return;
        }

        try {
            $this->Fitting_subtype_model->addSubtype($parentType, $subtype, $description);
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'success', 'message' => 'Subtype berhasil ditambahkan']));
        } catch (Exception $e) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Gagal menambahkan subtype: ' . $e->getMessage()]));
        }
    }

    /**
     * Update a subtype
     */
    public function updateSubtype(): void
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $id = $this->input->post('id', true);
        $parentType = $this->input->post('parent_type', true);
        $subtype = $this->input->post('subtype', true);
        $description = $this->input->post('description', true);

        if (empty($id) || empty($parentType) || empty($subtype)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'ID, parent type dan subtype harus diisi']));
            return;
        }

        // Check if subtype already exists for this parent type (excluding current subtype)
        if ($this->Fitting_subtype_model->isSubtypeExists($parentType, $subtype, $id)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Subtype sudah ada untuk type ini']));
            return;
        }

        try {
            $this->Fitting_subtype_model->editSubtype($id, $parentType, $subtype, $description);
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'success', 'message' => 'Subtype berhasil diperbarui']));
        } catch (Exception $e) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Gagal memperbarui subtype: ' . $e->getMessage()]));
        }
    }

    /**
     * Delete a subtype
     */
    public function deleteSubtype(): void
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $id = $this->input->post('id', true);

        if (empty($id)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'ID subtype harus diisi']));
            return;
        }

        // Get subtype data to check usage
        $subtypeData = $this->Fitting_subtype_model->getById($id);
        if (!$subtypeData) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Subtype tidak ditemukan']));
            return;
        }

        // Check if subtype is being used by any fitting
        if ($this->Fitting_subtype_model->isSubtypeInUse($subtypeData['parent_type'], $subtypeData['subtype'])) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Subtype tidak dapat dihapus karena sedang digunakan oleh fitting']));
            return;
        }

        try {
            $this->Fitting_subtype_model->deleteSubtype($id);
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'success', 'message' => 'Subtype berhasil dihapus']));
        } catch (Exception $e) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Gagal menghapus subtype: ' . $e->getMessage()]));
        }
    }

    private function handleUpload(): ?string
    {
        if (empty($_FILES['image']['name'])) {
            return null; // No file uploaded, return null (will not update image field)
        }

        $config = [
            'upload_path' => FCPATH . 'assets/img/fitting_types/',
            'allowed_types' => 'jpg|jpeg|png',
            'max_size' => 2048, // 2MB
            'file_name' => time() . '-' . $_FILES['image']['name'],
        ];

        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, true);
        }

        $this->load->library('upload', $config);

        if ($this->upload->do_upload('image')) {
            return $this->upload->data('file_name');
        } else {
            set_message(['danger', 'Upload gambar gagal: ' . $this->upload->display_errors()]);
            return null;
        }
    }
}
