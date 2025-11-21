<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Solenoid_type extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['file', 'form']);

        // Check user authentication using common helper
        check_user_authentication();

        $this->load->model('Solenoid_type_model');
        $this->load->library('form_validation');
    }

    public function index(): void
    {
        $types = $this->Solenoid_type_model->getAllTypes();

        // Add usage information to each type
        foreach ($types as &$type) {
            $type['usage_count'] = $this->Solenoid_type_model->getUsageCount($type['type']);
            $type['is_in_use'] = $type['usage_count'] > 0;
        }

        $data['title'] = 'Kelola Type Solenoid';
        $data['types'] = $types;
        render_view('solenoid/manage_types', $data);
    }

    public function add(): void
    {
        if ($this->input->method() === 'post') {
            $type = $this->input->post('type', true);
            $this->form_validation->set_rules('type', 'Type', 'required|trim|max_length[15]|callback_validate_type_format');
            if ($this->form_validation->run() && !$this->Solenoid_type_model->isTypeExists($type)) {
                $imageFilename = $this->handleUpload();
                $this->Solenoid_type_model->addType($type, $imageFilename);
                set_message(['success', 'Type berhasil ditambahkan']);
                redirect('solenoid_type');
            } else {
                set_message(['danger', 'Type sudah ada atau data tidak valid']);
            }
        }
        $data = [
            'title' => 'Tambah Type Solenoid',
            'form_action' => site_url('solenoid_type/add'),
            'type' => [],
        ];
        render_view('solenoid/type_form', $data);
    }

    public function edit(int $id): void
    {
        $typeRow = $this->Solenoid_type_model->getById($id);
        if (!$typeRow) show_404();

        if ($this->input->method() === 'post') {
            $type = $this->input->post('type', true);
            $this->form_validation->set_rules('type', 'Type', 'required|trim|max_length[15]|callback_validate_type_format');
            if ($this->form_validation->run() && !$this->Solenoid_type_model->isTypeExists($type, $id)) {
                $imageFilename = $this->handleUpload($typeRow['image']);
                $this->Solenoid_type_model->editType($id, $type, $imageFilename);
                set_message(['success', 'Type berhasil diperbarui']);
                redirect('solenoid_type');
            } else {
                set_message(['danger', 'Type sudah ada atau data tidak valid']);
            }
        }

        $data['title'] = 'Edit Type Solenoid';
        $data['form_action'] = site_url('solenoid_type/edit/' . $id);
        $data['type'] = $typeRow;
        render_view('solenoid/type_form', $data);
    }

    public function delete(int $id): void
    {
        $typeRow = $this->Solenoid_type_model->getById($id);
        if (!$typeRow) {
            set_message(['danger', 'Type tidak ditemukan']);
            redirect('solenoid_type');
        }

        // Check if type is being used by any solenoid records
        if ($this->Solenoid_type_model->isTypeInUse($typeRow['type'])) {
            $usageCount = $this->Solenoid_type_model->getUsageCount($typeRow['type']);
            set_message(['warning', "Type '{$typeRow['type']}' tidak dapat dihapus karena sedang digunakan oleh {$usageCount} solenoid. Hapus terlebih dahulu solenoid yang menggunakan type ini."]);
            redirect('solenoid_type');
        }

        // delete image file if exists
        if (!empty($typeRow['image'])) {
            $filePath = FCPATH . 'assets/img/solenoid_types/' . $typeRow['image'];
            if (is_file($filePath)) @unlink($filePath);
        }

        try {
            if ($this->Solenoid_type_model->deleteType($id)) {
                set_message(['success', 'Type berhasil dihapus']);
            } else {
                set_message(['danger', 'Gagal menghapus type']);
            }
        } catch (Exception $e) {
            set_message(['danger', 'Terjadi kesalahan saat menghapus type']);
        }

        redirect('solenoid_type');
    }

    /**
     * Handle file upload for type image.
     *
     * @param string|null $oldImage Previous image filename to delete
     * @return string|null The uploaded filename or the old filename if no new upload
     */
    private function handleUpload(?string $oldImage = null): ?string
    {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
            return $oldImage;
        }

        $uploadPath = FCPATH . 'assets/img/solenoid_types/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $config = [
            'upload_path' => $uploadPath,
            'allowed_types' => 'jpg|jpeg|png|gif',
            'max_size' => 2048, // 2MB
            'encrypt_name' => true,
        ];

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('image')) {
            set_message(['danger', $this->upload->display_errors('', '')]);
            return $oldImage;
        }

        // Delete old image if exists
        if ($oldImage && is_file($uploadPath . $oldImage)) {
            @unlink($uploadPath . $oldImage);
        }

        $uploadData = $this->upload->data();
        return $uploadData['file_name'];
    }

    /**
     * Custom validation callback for type format.
     *
     * @param string $str The type value to validate
     * @return bool True if valid, false otherwise
     */
    public function validate_type_format(string $str): bool
    {
        // Allow alphanumeric, spaces, and some special characters
        if (!preg_match('/^[a-zA-Z0-9\s\-\/]+$/', $str)) {
            $this->form_validation->set_message('validate_type_format', 'Type hanya boleh mengandung huruf, angka, spasi, dan karakter - /');
            return false;
        }
        return true;
    }

    /**
     * Get type image via AJAX.
     */
    public function getTypeImage(): void
    {
        $this->output->set_content_type('application/json');

        $type = $this->input->get('type', true);
        if (!$type) {
            $this->output->set_output(json_encode(['success' => false, 'message' => 'Type tidak valid']));
            return;
        }

        $typeData = $this->Solenoid_type_model->getByType($type);
        if ($typeData && !empty($typeData['image'])) {
            $imagePath = base_url('assets/img/solenoid_types/' . $typeData['image']);
            $this->output->set_output(json_encode(['success' => true, 'image' => $imagePath]));
        } else {
            $this->output->set_output(json_encode(['success' => false, 'message' => 'Gambar tidak ditemukan']));
        }
    }

    /**
     * Display type image page.
     */
    public function type(?string $typeParam = null): void
    {
        if (!$typeParam) {
            show_404();
        }

        $typeData = $this->Solenoid_type_model->getByType($typeParam);
        if (!$typeData) {
            show_404();
        }

        $data['title'] = 'Type Solenoid: ' . $typeData['type'];
        $data['type'] = $typeData;
        render_view('solenoid/type', $data);
    }

    /**
     * Get all types as JSON (for AJAX requests).
     */
    public function getTypes(): void
    {
        $this->output->set_content_type('application/json');
        $types = $this->Solenoid_type_model->getAllTypes();
        $this->output->set_output(json_encode(['success' => true, 'types' => $types]));
    }

    /**
     * Check if a type exists (for validation).
     */
    public function checkTypeExists(): void
    {
        $this->output->set_content_type('application/json');

        $type = $this->input->get('type', true);
        $excludeId = $this->input->get('exclude_id');

        if (!$type) {
            $this->output->set_output(json_encode(['exists' => false]));
            return;
        }

        $exists = $this->Solenoid_type_model->isTypeExists($type, $excludeId);
        $this->output->set_output(json_encode(['exists' => $exists]));
    }

    /**
     * Bulk delete types (for future enhancement).
     */
    public function bulkDelete(): void
    {
        $this->output->set_content_type('application/json');

        if ($this->input->method() !== 'post') {
            $this->output->set_output(json_encode(['success' => false, 'message' => 'Invalid request method']));
            return;
        }

        $ids = $this->input->post('ids');
        if (empty($ids) || !is_array($ids)) {
            $this->output->set_output(json_encode(['success' => false, 'message' => 'No types selected']));
            return;
        }

        $deleted = 0;
        $errors = [];

        foreach ($ids as $id) {
            $typeRow = $this->Solenoid_type_model->getById((int)$id);
            if (!$typeRow) {
                continue;
            }

            if ($this->Solenoid_type_model->isTypeInUse($typeRow['type'])) {
                $errors[] = "Type '{$typeRow['type']}' sedang digunakan";
                continue;
            }

            if (!empty($typeRow['image'])) {
                $filePath = FCPATH . 'assets/img/solenoid_types/' . $typeRow['image'];
                if (is_file($filePath)) @unlink($filePath);
            }

            if ($this->Solenoid_type_model->deleteType((int)$id)) {
                $deleted++;
            }
        }

        $message = "$deleted type berhasil dihapus";
        if (!empty($errors)) {
            $message .= '. ' . implode(', ', $errors);
        }

        $this->output->set_output(json_encode([
            'success' => true,
            'message' => $message,
            'deleted' => $deleted,
            'errors' => $errors
        ]));
    }

    /**
     * Export types to Excel (for future enhancement).
     */
    public function export(): void
    {
        // Future implementation
        set_message(['info', 'Export feature coming soon']);
        redirect('solenoid_type');
    }

    /**
     * Import types from Excel (for future enhancement).
     */
    public function import(): void
    {
        // Future implementation
        set_message(['info', 'Import feature coming soon']);
        redirect('solenoid_type');
    }
}
