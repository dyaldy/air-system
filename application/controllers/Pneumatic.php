<?php
defined('BASEPATH') or exit('No direct script access allowed');

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Pneumatic controller for air-system.
 *
 * Manage pneumatics: listing, search/filter/sort, CRUD operations, and Excel import/export following ASRS conventions.
 *
 * @package AirSystem
 * @subpackage Controllers
 * @category Pneumatic
 * @author Apparel One Indonesia
 * @version 1.0.0
 * @property CI_Session $session
 * @property CI_Input $input
 * @property CI_URI $uri
 * @property CI_Form_validation $form_validation
 * @property CI_Pagination $pagination
 * @property Pneumatic_model $Pneumatic_model
 */
class Pneumatic extends CI_Controller
{
    /**
     * Configuration array for all settings.
     *
     * @var array
     */
    private const CONFIG = [
        'pagination' => [
            'items_per_page' => 7
        ],
        'validation' => [
            'brand' => [
                'field' => 'brand',
                'label' => 'Brand',
                'rules' => 'required|trim|max_length[15]',
                'errors' => [
                    'required'   => '%s harus diisi',
                    'max_length' => '%s maksimal 15 karakter',
                ],
            ],
            'type' => [
                'field' => 'type',
                'label' => 'Type',
                'rules' => 'required|trim|max_length[5]',
                'errors' => [
                    'required'   => '%s harus diisi',
                    'max_length' => '%s maksimal 5 karakter',
                ],
            ],
            'bore' => [
                'field' => 'bore',
                'label' => 'Bore',
                'rules' => 'required|numeric|greater_than[0]',
                'errors' => [
                    'required'     => '%s harus diisi',
                    'numeric'      => '%s harus berupa angka',
                    'greater_than' => '%s harus lebih besar dari 0',
                ],
            ],
            'stroke' => [
                'field' => 'stroke',
                'label' => 'Stroke',
                'rules' => 'required|numeric|greater_than[0]',
                'errors' => [
                    'required'     => '%s harus diisi',
                    'numeric'      => '%s harus berupa angka',
                    'greater_than' => '%s harus lebih besar dari 0',
                ],
            ],
        ],
    ];

    /**
     * Class constructor.
     *
     * Loads models, libraries and helpers. Verifies authentication.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Check authentication
        if (!$this->session->userdata('user_data')) {
            redirect(base_url());
        }

        $this->load->model('Pneumatic_model');
        $this->load->library(['form_validation', 'pagination']);

        // Reset session if controller changed
        if ($this->session->userdata('controller') !== 'pneumatic') {
            $this->session->set_userdata('controller', 'pneumatic');
            $this->session->unset_userdata(['keyword', 'sort', 'filter']);
        }
    }

    /**
     * List pneumatics with pagination and optional search. Renders via render_view().
     *
     * @return void
     */
    public function index(): void
    {
        $this->handleSessionState();

        $sessionData = [
            'search' => $this->session->userdata('keyword'),
            'filter' => $this->session->userdata('filter'),
            'sort'   => $this->session->userdata('sort'),
        ];

        $totalRows = $this->Pneumatic_model->countPneumatic($sessionData['search'], $sessionData['filter']);
        $pagination = $this->setupPagination($totalRows);

        $pneumatics = $this->Pneumatic_model->getPneumatic(
            self::CONFIG['pagination']['items_per_page'],
            $pagination['offset'],
            $sessionData['search'],
            $sessionData['filter'],
            $sessionData['sort']
        );

        $data = [
            'pneumatics'     => $pneumatics,
            'pagination'     => $pagination,
            'total_rows'     => $totalRows,
            'search_keyword' => $sessionData['search'],
            'sort_keyword'   => $sessionData['sort'],
            'filter_keyword' => $sessionData['filter'],
        ];

        $this->render_view('pneumatic/index', $data);
    }

    /**
     * Display the add pneumatic form and handle form submission.
     *
     * @return void
     */
    public function add(): void
    {
        if ($this->input->method() === 'post') {
            $this->setValidationRules();

            if ($this->form_validation->run()) {
                $this->Pneumatic_model->addPneumatic();
                set_message(['success', 'Data pneumatic berhasil ditambahkan!']);
                redirect('pneumatic');
            }
        }

        $this->render_view('pneumatic/add');
    }

    /**
     * Display the edit pneumatic form and handle form submission.
     *
     * @param string $pneumaticId The pneumatic ID to edit.
     *
     * @return void
     */
    public function edit(string $pneumaticId): void
    {
        $pneumatic = $this->Pneumatic_model->getById($pneumaticId);

        if (!$pneumatic) {
            show_404();
        }

        if ($this->input->method() === 'post') {
            $this->setValidationRules(true);

            if ($this->form_validation->run()) {
                $this->Pneumatic_model->editPneumatic($pneumaticId);
                set_message(['success', 'Data pneumatic berhasil diperbarui!']);
                redirect('pneumatic');
            }
        }

        $data['pneumatic'] = $pneumatic;
        $this->render_view('pneumatic/edit', $data);
    }

    /**
     * Handle pneumatic deletion.
     *
     * @param string $pneumaticId The pneumatic ID to delete.
     *
     * @return void
     */
    public function delete(string $pneumaticId): void
    {
        $pneumatic = $this->Pneumatic_model->getById($pneumaticId);

        if (!$pneumatic) {
            set_message(['danger', 'Data pneumatic tidak ditemukan!']);
        } else {
            $this->Pneumatic_model->deletePneumatic($pneumaticId);
            set_message(['success', 'Data pneumatic berhasil dihapus!']);
        }

        redirect('pneumatic');
    }

    /**
     * Downloads pneumatic data as Excel file.
     *
     * @return void
     */
    public function download(): void
    {
        try {
            $pneumatics = $this->Pneumatic_model->getAllPneumatics();
            $this->generateExcelFile($pneumatics);
        } catch (Exception $e) {
            log_message('error', 'Excel download error: ' . $e->getMessage());
            set_message(['danger', 'Error downloading file: ' . $e->getMessage()]);
            redirect('pneumatic');
        }
    }

    /**
     * Downloads Excel template for pneumatic upload.
     *
     * @return void
     */
    public function template(): void
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $headers = ['Brand', 'Type', 'Bore', 'Stroke'];

            foreach ($headers as $index => $header) {
                $sheet->setCellValueByColumnAndRow($index + 1, 1, $header);
            }

            $filename = 'Template Data Pneumatic.xlsx';
            $this->outputExcelFile($spreadsheet, $filename);
        } catch (Exception $e) {
            log_message('error', 'Template download error: ' . $e->getMessage());
            show_error('Error generating template file: ' . $e->getMessage());
        }
    }

    /**
     * Handles Excel file upload and pneumatic import.
     *
     * @return void
     */
    public function upload(): void
    {
        // Accept file-only multipart POST submissions; don't rely on $this->input->post()
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            redirect('pneumatic');
            return;
        }

        // Basic server-side guard if no file provided
        if (empty($_FILES['file']) || (int)($_FILES['file']['error'] ?? UPLOAD_ERR_OK) === UPLOAD_ERR_NO_FILE) {
            set_message(['danger', 'Tidak ada file yang diunggah. Pilih file Excel terlebih dahulu.']);
            redirect('pneumatic');
            return;
        }

        $config['upload_path'] = './uploads/';
        $config['allowed_types'] = 'xlsx|xls';
        $config['max_size'] = 2048; // 2MB
        $config['file_name'] = 'pneumatic_upload_' . time();

        // Create upload directory if it doesn't exist
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, true);
        }

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('file')) {
            set_message(['danger', 'Upload error: ' . $this->upload->display_errors()]);
            redirect('pneumatic');
            return;
        }

        $uploadData = $this->upload->data();
        $filePath = $uploadData['full_path'];

        try {
            $result = $this->processExcelFile($filePath);
            unlink($filePath); // Remove uploaded file

            if ($result['success']) {
                set_message(['success', "Data berhasil diimport. {$result['inserted']} pneumatic ditambahkan."]);
            } elseif ($result['inserted'] > 0) {
                $errorDetails = implode('<br>', $result['errorMessages']);
                set_message([
                    'warning',
                    "Import selesai dengan peringatan. {$result['inserted']} pneumatic ditambahkan, {$result['errors']} error.<br><br>Detail error:<br>{$errorDetails}"
                ]);
            } else {
                $errorDetails = implode('<br>', $result['errorMessages']);
                set_message([
                    'danger',
                    "Import gagal. {$result['errors']} error ditemukan.<br><br>Detail error:<br>{$errorDetails}"
                ]);
            }
        } catch (Exception $e) {
            unlink($filePath);
            log_message('error', 'Excel processing error: ' . $e->getMessage());
            set_message(['danger', 'Error processing file: ' . $e->getMessage()]);
        }

        redirect('pneumatic');
    }

    ## Private Helper Methods

    /**
     * Generates Excel file for download.
     *
     * @param array $pneumatics Array of pneumatic data
     * @return void
     */
    private function generateExcelFile(array $pneumatics): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = ['Pneumatic ID', 'Brand', 'Type', 'Bore', 'Stroke', 'Created At', 'Updated At', 'Editor'];
        foreach ($headers as $index => $header) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $header);
        }

        // Add data
        $row = 2;
        foreach ($pneumatics as $pneumatic) {
            $sheet->setCellValueByColumnAndRow(1, $row, $pneumatic['pneumatic_id']);
            $sheet->setCellValueByColumnAndRow(2, $row, $pneumatic['brand']);
            $sheet->setCellValueByColumnAndRow(3, $row, $pneumatic['type']);
            $sheet->setCellValueByColumnAndRow(4, $row, $pneumatic['bore']);
            $sheet->setCellValueByColumnAndRow(5, $row, $pneumatic['stroke']);
            $sheet->setCellValueByColumnAndRow(6, $row, $pneumatic['created_at']);
            $sheet->setCellValueByColumnAndRow(7, $row, $pneumatic['updated_at']);
            $sheet->setCellValueByColumnAndRow(8, $row, $pneumatic['editor']);
            $row++;
        }

        $filename = 'Data Pneumatic.xlsx';
        $this->outputExcelFile($spreadsheet, $filename);
    }

    /**
     * Outputs Excel file to browser for download.
     *
     * @param Spreadsheet $spreadsheet The spreadsheet object
     * @param string $filename The filename for download
     * @return void
     */
    private function outputExcelFile(Spreadsheet $spreadsheet, string $filename): void
    {
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }

    /**
     * Processes uploaded Excel file and returns results.
     *
     * @param string $filePath Path to the uploaded file
     * @return array Results containing success status, counts, and error messages
     */
    private function processExcelFile(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $inserted = 0;
        $errorMessages = [];
        $pneumaticData = [];

        // Skip header row, start from row 2
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $rowNumber = $i + 1; // Add 1 to account for header row

            // Skip empty rows
            if (empty($row[0]) && empty($row[1]) && empty($row[2]) && empty($row[3])) {
                continue;
            }

            $brand = $row[0] ?? '';
            $type = $row[1] ?? '';
            $bore = $row[2] ?? '';
            $stroke = $row[3] ?? '';

            // Validate required fields
            if (empty($brand)) {
                $errorMessages[] = "Baris {$rowNumber}: Brand tidak boleh kosong";
                continue;
            }

            if (empty($type)) {
                $errorMessages[] = "Baris {$rowNumber}: Type tidak boleh kosong";
                continue;
            }

            if (empty($bore)) {
                $errorMessages[] = "Baris {$rowNumber}: Bore tidak boleh kosong";
                continue;
            }

            if (empty($stroke)) {
                $errorMessages[] = "Baris {$rowNumber}: Stroke tidak boleh kosong";
                continue;
            }

            // Validate data formats
            if (strlen($brand) > 15) {
                $errorMessages[] = "Baris {$rowNumber}: Brand maksimal 15 karakter: {$brand}";
                continue;
            }

            if (strlen($type) > 5) {
                $errorMessages[] = "Baris {$rowNumber}: Type maksimal 5 karakter: {$type}";
                continue;
            }

            if (!is_numeric($bore) || $bore <= 0) {
                $errorMessages[] = "Baris {$rowNumber}: Bore harus berupa angka positif: {$bore}";
                continue;
            }

            if (!is_numeric($stroke) || $stroke <= 0) {
                $errorMessages[] = "Baris {$rowNumber}: Stroke harus berupa angka positif: {$stroke}";
                continue;
            }

            // Generate pneumatic ID
            $pneumaticId = 'pnm-' . strtolower(trim($brand)) . '-' . strtolower(trim($type)) . '-' . $bore . '-' . $stroke;

            // Check if pneumatic combination already exists
            if ($this->Pneumatic_model->isPneumaticIdExists($pneumaticId)) {
                $errorMessages[] = "Baris {$rowNumber}: Kombinasi pneumatic sudah terdaftar (Brand: {$brand}, Type: {$type}, Bore: {$bore}, Stroke: {$stroke})";
                continue;
            }

            $pneumaticData[] = [
                'pneumatic_id' => $pneumaticId,
                'brand'        => strtoupper(trim($brand)),
                'type'         => strtoupper(trim($type)),
                'bore'         => (int)$bore,
                'stroke'       => (int)$stroke,
                'created_at'   => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
                'updated_at'   => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
                'editor'       => $this->session->userdata('user_data')['nik']
            ];
            $inserted++;
        }

        // Batch insert
        if (!empty($pneumaticData)) {
            $this->Pneumatic_model->insertBatch($pneumaticData);
        }

        return [
            'success' => count($errorMessages) === 0,
            'inserted' => $inserted,
            'errors' => count($errorMessages),
            'errorMessages' => $errorMessages
        ];
    }

    /**
     * Sets up pagination configuration.
     *
     * @param int $totalRows Total number of records
     * @return array Pagination configuration
     */
    private function setupPagination(int $totalRows): array
    {
        $config = [
            'base_url'        => site_url('pneumatic/index'),
            'total_rows'      => $totalRows,
            'per_page'        => self::CONFIG['pagination']['items_per_page'],
            'use_page_numbers' => true,
            'attributes'      => ['class' => 'page-link'],
            'full_tag_open'   => '<ul class="pagination justify-content-center">',
            'full_tag_close'  => '</ul>',
            'first_link'      => 'First',
            'last_link'       => 'Last',
            'first_tag_open'  => '<li class="page-item">',
            'first_tag_close' => '</li>',
            'prev_link'       => '&laquo;',
            'prev_tag_open'   => '<li class="page-item">',
            'prev_tag_close'  => '</li>',
            'next_link'       => '&raquo;',
            'next_tag_open'   => '<li class="page-item">',
            'next_tag_close'  => '</li>',
            'last_tag_open'   => '<li class="page-item">',
            'last_tag_close'  => '</li>',
            'cur_tag_open'    => '<li class="page-item active"><span class="page-link">',
            'cur_tag_close'   => '</span></li>',
            'num_tag_open'    => '<li class="page-item">',
            'num_tag_close'   => '</li>',
        ];

        $this->pagination->initialize($config);

        return [
            'links'  => $this->pagination->create_links(),
            'offset' => max(0, ($this->uri->segment(3, 1) - 1) * self::CONFIG['pagination']['items_per_page']),
        ];
    }

    /**
     * Handles session state management for search, filter, and sort.
     *
     * @return void
     */
    private function handleSessionState(): void
    {
        if ($this->input->post('find')) {
            $this->session->set_userdata('keyword', $this->input->post('keyword', true));
        }

        if ($this->input->post('clear')) {
            $this->session->unset_userdata(['keyword', 'sort', 'filter']);
        }

        if ($this->input->post('sort')) {
            $this->session->set_userdata('sort', $this->input->post('sort', true));
        }

        if ($this->input->post('filter')) {
            $this->session->set_userdata('filter', $this->input->post('filter', true));
        }
    }

    /**
     * Sets validation rules for pneumatic forms.
     *
     * @param bool $isEdit Whether this is for edit form
     * @return void
     */
    private function setValidationRules(bool $isEdit = false): void
    {
        foreach (self::CONFIG['validation'] as $field => $config) {
            $this->form_validation->set_rules($config['field'], $config['label'], $config['rules'], $config['errors']);
        }

        // Add custom validation for unique pneumatic combination
        if (!$isEdit) {
            $this->form_validation->set_rules('brand', 'Brand', 'callback_check_pneumatic_combination');
        }
    }

    /**
     * Custom validation callback to check if pneumatic combination already exists.
     *
     * @param string $brand The brand value
     * @return bool
     */
    public function check_pneumatic_combination(string $brand): bool
    {
        $type = $this->input->post('type', true);
        $bore = $this->input->post('bore', true);
        $stroke = $this->input->post('stroke', true);

        if (!empty($brand) && !empty($type) && !empty($bore) && !empty($stroke)) {
            $pneumaticId = 'pnm-' . strtolower($brand) . '-' . strtolower($type) . '-' . $bore . '-' . $stroke;

            if ($this->Pneumatic_model->isPneumaticIdExists($pneumaticId)) {
                $this->form_validation->set_message('check_pneumatic_combination', 'Kombinasi pneumatic (Brand: {field}, Type: ' . $type . ', Bore: ' . $bore . ', Stroke: ' . $stroke . ') sudah terdaftar');
                return false;
            }
        }

        return true;
    }
    /**
     * Renders view with common data.
     *
     * @param string $view The view file to render
     * @param array $data Additional data to pass to view
     * @return void
     */
    private function render_view(string $view, array $data = []): void
    {
        $data['title'] = 'Data Pneumatic';
        $this->load->view('templates/header', $data);
        $this->load->view($view, $data);
        $this->load->view('templates/footer');
    }
}
