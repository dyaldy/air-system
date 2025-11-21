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
        fputcsv($output, ['Regulator ID', 'Type', 'Min Stock', 'Created At', 'Updated At']);

        foreach ($regulators as $regulator) {
            fputcsv($output, [
                $regulator['regulator_id'],
                $regulator['type'],
                $regulator['min_stock'] ?? 5,
                $regulator['created_at'],
                $regulator['updated_at']
            ]);
        }

        fclose($output);
        exit;
    }

    public function downloadPDF(): void
    {
        $regulators = $this->Regulator_model->getAllRegulator();
        require_once(APPPATH . '../vendor/autoload.php');
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Air System');
        $pdf->SetTitle('Data Regulator');
        $pdf->SetHeaderData('', 0, 'Data Regulator', date('Y-m-d H:i:s'));
        $pdf->AddPage();

        $html = '<h1>Data Regulator</h1><table border="1" cellpadding="4">';
        $html .= '<thead><tr style="background-color:#f0f0f0;"><th>Regulator ID</th><th>Type</th><th>Min Stock</th></tr></thead><tbody>';

        foreach ($regulators as $regulator) {
            $html .= '<tr><td>' . htmlspecialchars($regulator['regulator_id']) . '</td>';
            $html .= '<td>' . htmlspecialchars($regulator['type']) . '</td>';
            $html .= '<td>' . htmlspecialchars($regulator['min_stock'] ?? 5) . '</td></tr>';
        }

        $html .= '</tbody></table>';
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('regulator_data_' . date('YmdHis') . '.pdf', 'D');
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
                    'min_stock'    => 5,
                    'created_at'   => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
                    'updated_at'   => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
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
