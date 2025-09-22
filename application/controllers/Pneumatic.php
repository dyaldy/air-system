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
                'rules' => 'required|trim|max_length[15]|callback_check_type_exists',
                'errors' => [
                    'required'   => '%s harus diisi',
                    'max_length' => '%s maksimal 15 karakter',
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
        $this->load->model('Pneumatic_type_model');
        $this->load->library(['form_validation', 'pagination']);
        $this->load->helper('common');

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
        // Handle Excel uploads using the ASRS-style helper
        $this->handleFileUpload();

        // If a type is provided via GET (from the type selection page), set it as a session filter
        $getType = $this->input->get('type', true);
        $clearType = $this->input->get('clear_type', true);
        if (!empty($clearType)) {
            // clear any existing filter
            $this->session->unset_userdata('filter');
        }
        if (!empty($getType)) {
            // normalize to uppercase since types are stored uppercase in DB
            $this->session->set_userdata('filter', ['type' => [strtoupper($getType)]]);
        }

        $this->handleSessionState();

        $sessionData = [
            'search' => $this->session->userdata('keyword'),
            'filter' => $this->session->userdata('filter'),
            'sort'   => $this->session->userdata('sort'),
        ];

        // Provide distinct values for filter dropdowns (respect current search/filter state)
        $brandOptions = $this->Pneumatic_model->getPneumaticFilter('brand', $sessionData['search'], $sessionData['filter']);
        $typeOptions = $this->Pneumatic_model->getPneumaticFilter('type', $sessionData['search'], $sessionData['filter']);

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
            'title'          => 'Data Pneumatic',
            'pneumatics'     => $pneumatics,
            'pagination'     => $pagination,
            'total_rows'     => $totalRows,
            'searchKeyword'  => $sessionData['search'],
            'sortKeyword'    => ($sessionData['sort'] && strpos($sessionData['sort'], '-') !== false) ? explode('-', $sessionData['sort'], 2) : ['', ''],
            'filterKeyword'  => $sessionData['filter'],
            'hasFilters'     => (!empty($sessionData['search']) || !empty($sessionData['filter']) || !empty($sessionData['sort'])),
            'brand_options'  => $brandOptions,
            'type_options'   => $typeOptions,
        ];

        render_view('pneumatic/index', $data);
    }

    /**
     * Show type selection page before entering pneumatic index.
     * Displays available types as image cards; clicking a type navigates to index filtered by that type.
     *
     * @return void
     */
    public function type(): void
    {
        // Fetch all types from the types table - show all available types, not just those with pneumatic records
        $allTypes = $this->Pneumatic_type_model->getAllTypes();

        // Build list of type rows with image information
        $typeOptions = [];
        $imgPath = FCPATH . 'assets/img/pneumatic_types/';
        $defaultUrl = base_url('assets/img/pneumatic-default.jpg');
        foreach ($allTypes as $row) {
            $t = (string)($row['type'] ?? '');
            if ($t === '') continue;

            $image = $row['image'] ?? null;
            $imageUrl = $defaultUrl;

            // Prefer stored filename if it exists
            if (!empty($image)) {
                $candidate = $imgPath . $image;
                if (is_file($candidate)) {
                    $imageUrl = base_url('assets/img/pneumatic_types/' . $image);
                }
            }

            // Try type-based filenames if still using default
            if ($imageUrl === $defaultUrl) {
                $typeSafe = strtolower($t);
                foreach (['.jpg', '.png', '.jpeg', '.gif'] as $ext) {
                    $candidate = $imgPath . $typeSafe . $ext;
                    if (is_file($candidate)) {
                        $imageUrl = base_url('assets/img/pneumatic_types/' . $typeSafe . $ext);
                        break;
                    }
                }
            }

            $typeOptions[] = [
                'type' => $t,
                'image' => $image,
                'image_url' => $imageUrl,
            ];
        }

        $data = [
            'title' => 'Pilih Type Pneumatic',
            'type_options' => $typeOptions,
        ];

        render_view('pneumatic/type', $data);
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

        // Fetch available pneumatic types for dropdown
        $pneumaticTypes = $this->Pneumatic_type_model->getAllTypes();

        // Check if type is pre-selected from URL parameter
        $preselectedType = $this->input->get('type', true);

        $data = [
            'title' => 'Tambah Pneumatic',
            'pneumatic_types' => $pneumaticTypes,
            'preselected_type' => $preselectedType
        ];
        render_view('pneumatic/add', $data);
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

        // Fetch available pneumatic types for dropdown
        $pneumaticTypes = $this->Pneumatic_type_model->getAllTypes();

        $data['pneumatic'] = $pneumatic;
        $data['title'] = 'Edit Pneumatic';
        $data['pneumatic_types'] = $pneumaticTypes;
        render_view('pneumatic/edit', $data);
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
            // Use letter-style headers like ASRS
            $sheet->setCellValue('A1', 'Brand');
            $sheet->setCellValue('B1', 'Type');
            $sheet->setCellValue('C1', 'Bore');
            $sheet->setCellValue('D1', 'Stroke');

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
    // Upload handling is performed in index() via handleFileUpload() to match ASRS (no separate public upload endpoint)
    /**
     * Public wrapper for upload POSTs — delegates to handleFileUpload().
     * Prevents 404 for forms that POST to /pneumatic/upload while keeping logic centralized.
     *
     * @return void
     */
    public function upload(): void
    {
        $this->handleFileUpload();
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

        // Set headers (letter style like ASRS)
        $sheet->setCellValue('A1', 'Pneumatic ID');
        $sheet->setCellValue('B1', 'Brand');
        $sheet->setCellValue('C1', 'Type');
        $sheet->setCellValue('D1', 'Bore');
        $sheet->setCellValue('E1', 'Stroke');
        $sheet->setCellValue('F1', 'Created At');
        $sheet->setCellValue('G1', 'Updated At');
        $sheet->setCellValue('H1', 'Editor');

        // Style headers similar to ASRS
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E9ECEF']
            ]
        ];
        // Apply style to header row (A1:..)
        $highestColumn = $sheet->getHighestColumn();
        $sheet->getStyle("A1:{$highestColumn}1")->applyFromArray($headerStyle);

        // Add data
        $row = 2;
        foreach ($pneumatics as $pneumatic) {
            $sheet->setCellValue("A{$row}", $pneumatic['pneumatic_id']);
            $sheet->setCellValue("B{$row}", $pneumatic['brand']);
            $sheet->setCellValue("C{$row}", $pneumatic['type']);
            $sheet->setCellValue("D{$row}", $pneumatic['bore']);
            $sheet->setCellValue("E{$row}", $pneumatic['stroke']);
            $sheet->setCellValue("F{$row}", $pneumatic['created_at']);
            $sheet->setCellValue("G{$row}", $pneumatic['updated_at']);
            $sheet->setCellValue("H{$row}", $pneumatic['editor']);
            $row++;
        }

        // Auto-filter and auto-size columns (ASRS style)
        $sheet->setAutoFilter('A1:H1');
        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
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
        exit;
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

        // Batch insert (after validating types) - insert only if there are no row-level DB risks
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
     * Handle file upload posted to index (ASRS-style).
     * This mirrors the upload handling in ASRS User controller but for pneumatic data.
     *
     * @return void
     */
    private function handleFileUpload(): void
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !isset($_FILES['file'])) {
            return;
        }

        if (
            $_FILES['file']['error'] !== UPLOAD_ERR_OK ||
            empty($_FILES['file']['tmp_name']) ||
            !is_uploaded_file($_FILES['file']['tmp_name'])
        ) {
            set_message(['danger', 'File upload tidak valid']);
            return;
        }

        $file = $_FILES['file']['tmp_name'];

        try {
            $spreadsheet = @IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray(null, true, true, true);
            array_shift($data); // remove header row

            $skippedData = [];
            $insertData  = [];

            foreach ($data as $rowIndex => $row) {
                // Validate required fields
                if (!$row['A'] && !$row['B'] && !$row['C'] && !$row['D']) {
                    continue; // skip empty row
                }

                if (!$row['A']) {
                    $skippedData[] = "Brand tidak boleh kosong";
                    continue;
                }

                if (!$row['B']) {
                    $skippedData[] = "Type tidak boleh kosong";
                    continue;
                }

                if (!$row['C']) {
                    $skippedData[] = "Bore tidak boleh kosong";
                    continue;
                }

                if (!$row['D']) {
                    $skippedData[] = "Stroke tidak boleh kosong";
                    continue;
                }

                $brand = $row['A'] ?? '';
                $type = $row['B'] ?? '';
                $bore = $row['C'] ?? '';
                $stroke = $row['D'] ?? '';

                if (strlen($brand) > 15) {
                    $skippedData[] = "Brand maksimal 15 karakter: {$brand}";
                    continue;
                }

                if (strlen($type) > 5) {
                    $skippedData[] = "Type maksimal 5 karakter: {$type}";
                    continue;
                }

                if (!is_numeric($bore) || $bore <= 0) {
                    $skippedData[] = "Bore harus berupa angka positif: {$bore}";
                    continue;
                }

                if (!is_numeric($stroke) || $stroke <= 0) {
                    $skippedData[] = "Stroke harus berupa angka positif: {$stroke}";
                    continue;
                }

                $pneumaticId = 'pnm-' . strtolower(trim($brand)) . '-' . strtolower(trim($type)) . '-' . $bore . '-' . $stroke;

                if ($this->Pneumatic_model->isPneumaticIdExists($pneumaticId)) {
                    $skippedData[] = "Kombinasi pneumatic sudah terdaftar (Brand: {$brand}, Type: {$type}, Bore: {$bore}, Stroke: {$stroke})";
                    continue;
                }

                $insertData[] = [
                    'pneumatic_id' => $pneumaticId,
                    'brand'        => strtoupper(trim($brand)),
                    'type'         => strtoupper(trim($type)),
                    'bore'         => (int)$bore,
                    'stroke'       => (int)$stroke,
                    'created_at'   => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
                    'updated_at'   => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
                    'editor'       => $this->session->userdata('user_data')['nik']
                ];
            }

            $insertCount  = 0;
            $skippedCount = count($skippedData);

            // Pre-validate types for each insert row to provide row-level errors instead of DB errors
            $validInsertData = [];
            foreach ($insertData as $rowIndex => $row) {
                $type = $row['type'] ?? '';
                if (empty($type) || !$this->Pneumatic_type_model->getByType($type)) {
                    $skippedCount++;
                    $skippedData[] = "Type tidak tersedia atau tidak terdaftar: {$type}";
                    continue;
                }
                $validInsertData[] = $row;
            }

            $insertCount = count($validInsertData);

            if ($insertCount > 0) {
                $this->Pneumatic_model->insertBatch($validInsertData);
            }

            // Now set flash messages based on skipped/insert counts
            if ($insertCount > 0 && $skippedCount > 0) {
                set_message([
                    'warning',
                    "{$insertCount} data berhasil ditambahkan.<br>{$skippedCount} data gagal ditambahkan.<br>" . implode('<br>', $skippedData)
                ]);
            } elseif ($skippedCount > 0) {
                set_message([
                    'danger',
                    "{$skippedCount} data gagal ditambahkan.<br>" . implode('<br>', $skippedData)
                ]);
            } elseif ($insertCount > 0) {
                set_message(['success', "Data berhasil ditambahkan! ({$insertCount} data baru)"]);
            } else {
                set_message(['danger', 'Data kosong!']);
            }

            $this->session->unset_userdata(['keyword', 'sort', 'filter']);
            redirect('pneumatic');
        } catch (Exception $e) {
            log_message('error', 'File upload error: ' . $e->getMessage());
            set_message(['danger', 'Terjadi kesalahan dalam membaca file Excel.']);
        }
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

        if ($this->input->post('sort-send')) {
            // Accept sort in format 'field-ORDER' where ORDER is ASC or DESC
            $sortRaw = $this->input->post('sort-send', true);
            if (is_string($sortRaw) && preg_match('/^[a-z0-9_\-]+-(ASC|DESC)$/i', $sortRaw)) {
                // Keep field name as-is, but uppercase the direction
                [$field, $direction] = explode('-', $sortRaw, 2);
                $this->session->set_userdata('sort', $field . '-' . strtoupper($direction));
            } elseif ($sortRaw === '') {
                $this->session->unset_userdata('sort');
            }
        }

        if ($this->input->post('reset')) {
            $this->session->unset_userdata(['keyword', 'sort', 'filter']);
        }

        if ($this->input->post('filter')) {
            $filterRaw = $this->input->post('filter', true);
            // If JSON string submitted by JS, decode it to associative array
            if (is_string($filterRaw) && ($json = json_decode($filterRaw, true)) !== null) {
                $this->session->set_userdata('filter', $json);
            } elseif (is_array($filterRaw)) {
                $this->session->set_userdata('filter', $filterRaw);
            }
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
     * Custom validation callback to check if selected type exists in pneumatic_types table.
     *
     * @param string $type The selected type value
     * @return bool
     */
    public function check_type_exists(string $type): bool
    {
        if (!empty($type)) {
            $typeExists = $this->Pneumatic_type_model->getByType($type);
            if (!$typeExists) {
                $this->form_validation->set_message('check_type_exists', 'Type {field} tidak tersedia. Silakan pilih type yang valid.');
                return false;
            }
        }
        return true;
    }
}
