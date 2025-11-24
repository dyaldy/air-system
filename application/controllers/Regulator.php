<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Regulator extends CI_Controller
{
    private const CONFIG = [
        'pagination' => ['items_per_page' => 7],
        'validation' => [
            'type' => [
                'field' => 'type',
                'label' => 'Type',
                'rules' => 'required|trim|max_length[20]|callback_check_regulator_combination',
                'errors' => [
                    'required'   => '%s harus diisi',
                    'max_length' => '%s maksimal 20 karakter',
                ],
            ],
            'min_stock' => [
                'field' => 'min_stock',
                'label' => 'Minimum Stock',
                'rules' => 'numeric|greater_than_equal_to[0]',
                'errors' => [
                    'numeric'               => '%s harus berupa angka',
                    'greater_than_equal_to' => '%s harus lebih besar atau sama dengan 0',
                ],
            ],
        ],
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Regulator_model');
        $this->load->helper(['common', 'url', 'date']);
        $this->load->library(['form_validation', 'pagination']);
        check_user_authentication();
        reset_controller_session('regulator');
    }

    public function index(): void
    {
        $this->handleFileUpload();
        $this->handleSessionState();

        $sessionData = [
            'search' => $this->session->userdata('keyword'),
            'filter' => $this->session->userdata('filter'),
            'sort'   => $this->session->userdata('sort'),
        ];

        $typeOptions = $this->Regulator_model->getRegulatorFilter('type', $sessionData['search'], $sessionData['filter']);
        $totalRows = $this->Regulator_model->countRegulator($sessionData['search'], $sessionData['filter']);
        $config = setup_pagination(site_url('regulator/index'), $totalRows, self::CONFIG['pagination']['items_per_page']);
        $this->pagination->initialize($config);

        $startData = (int) ($this->uri->segment(3) ?: 0);
        $regulators = $this->Regulator_model->getRegulator(
            self::CONFIG['pagination']['items_per_page'],
            $startData,
            $sessionData['search'],
            $sessionData['filter'],
            $sessionData['sort']
        );

        $data = [
            'title'          => 'Data Regulator',
            'regulators'     => $regulators,
            'pagination'     => ['links' => $this->pagination->create_links()],
            'total_rows'     => $totalRows,
            'searchKeyword'  => $sessionData['search'],
            'sortKeyword'    => ($sessionData['sort'] && strpos($sessionData['sort'], '-') !== false) ? explode('-', $sessionData['sort'], 2) : ['', ''],
            'filterKeyword'  => $sessionData['filter'],
            'hasFilters'     => (!empty($sessionData['search']) || !empty($sessionData['filter']) || !empty($sessionData['sort'])),
            'type_options'   => $typeOptions,
        ];

        render_view('regulator/index', $data);
    }

    public function add(): void
    {
        if ($this->input->method() === 'post') {
            $this->setValidationRules();
            if ($this->form_validation->run()) {
                $this->Regulator_model->addRegulator();
                set_message(['success', 'Data regulator berhasil ditambahkan!']);
                redirect('regulator');
            }
        }
        render_view('regulator/add', ['title' => 'Tambah Regulator']);
    }

    public function edit($id = null): void
    {
        if (!$id) {
            set_message(['danger', 'ID Regulator tidak ditemukan.']);
            redirect('regulator');
        }

        $regulator = $this->Regulator_model->getById($id);
        if (!$regulator) {
            set_message(['danger', 'Data regulator tidak ditemukan.']);
            redirect('regulator');
        }

        if ($this->input->method() === 'post') {
            $this->setValidationRules(true);
            if ($this->form_validation->run()) {
                $this->Regulator_model->editRegulator($id);
                set_message(['success', 'Data regulator berhasil diperbarui!']);
                redirect('regulator');
            }
        }

        render_view('regulator/edit', ['title' => 'Edit Regulator', 'regulator' => $regulator]);
    }

    public function delete($id = null): void
    {
        if (!$id) {
            set_message(['danger', 'ID Regulator tidak ditemukan.']);
            redirect('regulator');
        }

        if ($this->Regulator_model->deleteRegulator($id)) {
            set_message(['success', 'Data regulator berhasil dihapus!']);
        } else {
            set_message(['danger', 'Gagal menghapus regulator. Mungkin masih ada di storage.']);
        }
        redirect('regulator');
    }

    public function download(): void
    {
        $regulators = $this->Regulator_model->getAllRegulator();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=regulator_data_' . date('YmdHis') . '.csv');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, ['Regulator ID', 'Type', 'Min Stock', 'Created At', 'Updated At', 'Editor']);

        foreach ($regulators as $regulator) {
            fputcsv($output, [
                $regulator['regulator_id'],
                $regulator['type'],
                $regulator['min_stock'] ?? '',
                $regulator['created_at'],
                $regulator['updated_at'],
                $regulator['editor'] ?? ''
            ]);
        }

        fclose($output);
        exit;
    }

    public function downloadPDF(): void
    {
        $regulators = $this->Regulator_model->getAllRegulator();
        require_once(APPPATH . '../vendor/autoload.php');
        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('Air System');
        $pdf->SetAuthor('Air System');
        $pdf->SetTitle('Data Regulator');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);

        // Add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'Data Regulator', 0, 1, 'C');
        $pdf->Ln(5);

        // Table header
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(66, 139, 202);
        $pdf->SetTextColor(255, 255, 255);

        $pdf->Cell(70, 7, 'Regulator ID', 1, 0, 'C', 1);
        $pdf->Cell(60, 7, 'Type', 1, 0, 'C', 1);
        $pdf->Cell(50, 7, 'Created At', 1, 0, 'C', 1);
        $pdf->Cell(50, 7, 'Updated At', 1, 0, 'C', 1);
        $pdf->Cell(40, 7, 'Editor', 1, 1, 'C', 1);

        // Table data
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        $fill = false;

        foreach ($regulators as $regulator) {
            $pdf->SetFillColor(245, 245, 245);
            $pdf->Cell(70, 6, $regulator['regulator_id'], 1, 0, 'L', $fill);
            $pdf->Cell(60, 6, $regulator['type'], 1, 0, 'L', $fill);
            $pdf->Cell(50, 6, $regulator['created_at'], 1, 0, 'C', $fill);
            $pdf->Cell(50, 6, $regulator['updated_at'], 1, 0, 'C', $fill);
            $pdf->Cell(40, 6, $regulator['editor'], 1, 1, 'C', $fill);
            $fill = !$fill;
        }

        $filename = 'Data Regulator ' . date('Y-m-d H-i-s') . '.pdf';
        $pdf->Output($filename, 'D');
        exit;
    }

    public function upload(): void
    {
        $this->handleFileUpload();
        redirect('regulator');
    }

    public function download_template(): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=regulator_template.csv');
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, ['Type']);
        fputcsv($output, ['AR2000']);
        fputcsv($output, ['AR3000']);
        fclose($output);
        exit;
    }

    private function handleFileUpload(): void
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !isset($_FILES['file'])) {
            return;
        }

        if ($_FILES['file']['error'] !== UPLOAD_ERR_OK || empty($_FILES['file']['tmp_name']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
            set_message(['danger', 'File upload tidak valid']);
            return;
        }

        $file = $_FILES['file']['tmp_name'];

        try {
            $handle = fopen($file, 'r');
            if ($handle === false) throw new Exception('Unable to open CSV file');

            $bom = fread($handle, 3);
            if ($bom !== chr(0xEF) . chr(0xBB) . chr(0xBF)) rewind($handle);

            $data = [];
            while (($row = fgetcsv($handle)) !== false) $data[] = $row;
            fclose($handle);

            if (empty($data)) throw new Exception('CSV file is empty');
            array_shift($data);

            $skippedData = [];
            $insertData  = [];

            foreach ($data as $row) {
                if (count($row) < 1 || !$row[0]) continue;

                $type = trim($row[0]);
                if (empty($type)) {
                    $skippedData[] = "Type tidak boleh kosong";
                    continue;
                }
                if (strlen($type) > 20) {
                    $skippedData[] = "Type terlalu panjang (max 20 karakter): {$type}";
                    continue;
                }

                $regulatorId = 'reg-' . strtolower(str_replace(' ', '', $type));
                if ($this->Regulator_model->isRegulatorIdExists($regulatorId)) {
                    $skippedData[] = "Regulator dengan Type: {$type} sudah terdaftar";
                    continue;
                }

                $insertData[] = [
                    'regulator_id' => $regulatorId,
                    'type'         => $type,
                    'min_stock'    => null,
                    'created_at'   => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
                    'updated_at'   => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
                    'editor'       => $this->session->userdata('user_data')['nik'],
                ];
            }

            $insertCount  = count($insertData);
            $skippedCount = count($skippedData);

            if ($insertCount > 0) $this->Regulator_model->insertBatch($insertData);

            if ($insertCount > 0 && $skippedCount > 0) {
                set_message(['warning', "{$insertCount} data berhasil ditambahkan.<br>{$skippedCount} data gagal ditambahkan.<br>" . implode('<br>', $skippedData)]);
            } elseif ($skippedCount > 0) {
                set_message(['danger', "{$skippedCount} data gagal ditambahkan.<br>" . implode('<br>', $skippedData)]);
            } elseif ($insertCount > 0) {
                set_message(['success', "Data berhasil ditambahkan! ({$insertCount} data baru)"]);
            } else {
                set_message(['danger', 'Data kosong!']);
            }

            $this->session->unset_userdata(['keyword', 'sort', 'filter']);
            redirect('regulator');
        } catch (Exception $e) {
            log_message('error', 'File upload error: ' . $e->getMessage());
            set_message(['danger', 'Terjadi kesalahan dalam membaca file CSV.']);
        }
    }

    private function handleSessionState(): void
    {
        handle_session_state('regulator', []);
    }

    private function setValidationRules(bool $isEdit = false): void
    {
        foreach (self::CONFIG['validation'] as $field => $config) {
            $this->form_validation->set_rules($config['field'], $config['label'], $config['rules'], $config['errors']);
        }
    }

    public function check_regulator_combination(string $type): bool
    {
        if (!empty($type)) {
            $regulatorId = 'reg-' . strtolower(str_replace(' ', '', $type));
            if ($this->Regulator_model->isRegulatorIdExists($regulatorId)) {
                $this->form_validation->set_message('check_regulator_combination', 'Regulator dengan Type: {field} sudah terdaftar');
                return false;
            }
        }
        return true;
    }
}
