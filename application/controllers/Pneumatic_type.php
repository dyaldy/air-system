<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pneumatic_type extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('user_data')) {
            redirect(base_url());
        }
        $this->load->model('Pneumatic_type_model');
        $this->load->helper(['url', 'file', 'form']);
        $this->load->library('form_validation');
    }

    public function index(): void
    {
        $data['title'] = 'Kelola Type Pneumatic';
        $data['types'] = $this->Pneumatic_type_model->getAllTypes();
        render_view('pneumatic/manage_types', $data);
    }

    public function add(): void
    {
        if ($this->input->method() === 'post') {
            $type = $this->input->post('type', true);
            $this->form_validation->set_rules('type', 'Type', 'required|trim|max_length[10]');
            if ($this->form_validation->run() && !$this->Pneumatic_type_model->isTypeExists($type)) {
                $imageFilename = $this->handleUpload();
                $this->Pneumatic_type_model->addType($type, $imageFilename);
                set_message(['success', 'Type berhasil ditambahkan']);
                redirect('pneumatic_type');
            } else {
                set_message(['danger', 'Type sudah ada atau data tidak valid']);
            }
        }
        $data = [
            'title' => 'Tambah Type Pneumatic',
            'form_action' => site_url('pneumatic_type/add'),
            'type' => [],
        ];
        render_view('pneumatic/type_form', $data);
    }

    public function edit(int $id): void
    {
        $typeRow = $this->Pneumatic_type_model->getById($id);
        if (!$typeRow) show_404();

        if ($this->input->method() === 'post') {
            $type = $this->input->post('type', true);
            $this->form_validation->set_rules('type', 'Type', 'required|trim|max_length[10]');
            if ($this->form_validation->run() && !$this->Pneumatic_type_model->isTypeExists($type, $id)) {
                $imageFilename = $this->handleUpload($typeRow['image']);
                $this->Pneumatic_type_model->editType($id, $type, $imageFilename);
                set_message(['success', 'Type berhasil diperbarui']);
                redirect('pneumatic_type');
            } else {
                set_message(['danger', 'Type sudah ada atau data tidak valid']);
            }
        }

        $data['title'] = 'Edit Type Pneumatic';
        $data['form_action'] = site_url('pneumatic_type/edit/' . $id);
        $data['type'] = $typeRow;
        render_view('pneumatic/type_form', $data);
    }

    public function delete(int $id): void
    {
        $typeRow = $this->Pneumatic_type_model->getById($id);
        if (!$typeRow) {
            set_message(['danger', 'Type tidak ditemukan']);
            redirect('pneumatic_type');
        }

        // delete image file if exists
        if (!empty($typeRow['image'])) {
            $filePath = FCPATH . 'assets/img/pneumatic_types/' . $typeRow['image'];
            if (is_file($filePath)) @unlink($filePath);
        }

        $this->Pneumatic_type_model->deleteType($id);
        set_message(['success', 'Type berhasil dihapus']);
        redirect('pneumatic_type');
    }

    /**
     * Handle image upload.
     * If $existing provided and upload fails, keep existing filename.
     * Returns filename or null.
     */
    private function handleUpload(?string $existing = null): ?string
    {
        if (empty($_FILES) || empty($_FILES['image']['name'])) {
            return $existing ?? null;
        }

        $config['upload_path'] = FCPATH . 'assets/img/pneumatic_types/';
        if (!is_dir($config['upload_path'])) mkdir($config['upload_path'], 0755, true);
        $config['allowed_types'] = 'jpg|jpeg|png|gif';
        $config['max_size'] = 2048; // 2MB
        $fileName = time() . '-' . preg_replace('/[^a-z0-9\-\.]/i', '-', $_FILES['image']['name']);
        $config['file_name'] = $fileName;

        $this->load->library('upload', $config);

        if ($this->upload->do_upload('image')) {
            $data = $this->upload->data();
            // remove previous file
            if ($existing) {
                $prev = $config['upload_path'] . $existing;
                if (is_file($prev)) @unlink($prev);
            }
            return $data['file_name'];
        }

        // on failure keep existing if provided
        return $existing ?? null;
    }
}
