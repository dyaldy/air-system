<?php
defined('BASEPATH') or exit('No direct script access allowed');

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Solenoid controller for air-system.
 *
 * Manage solenoids: listing, search/filter/sort, CRUD operations, and Excel import/export following ASRS conventions.
 *
 * @package AirSystem
 * @subpackage Controllers
 * @category Solenoid
 * @author Apparel One Indonesia
 * @version 1.0.0
 * @property CI_Session $session
 * @property CI_Input $input
 * @property CI_URI $uri
 * @property CI_Form_validation $form_validation
 * @property CI_Pagination $pagination
 * @property Solenoid_model $Solenoid_model
 */
class Solenoid extends CI_Controller
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
            'type' => [
                'field' => 'type',
                'label' => 'Type',
                'rules' => 'required|trim|max_length[15]|callback_check_type_exists',
                'errors' => [
                    'required'   => '%s harus diisi',
                    'max_length' => '%s maksimal 15 karakter',
                ],
            ],
            'subtype' => [
                'field' => 'subtype',
                'label' => 'Subtype',
                'rules' => 'required|trim|max_length[50]',
                'errors' => [
                    'required'   => '%s harus diisi',
                    'max_length' => '%s maksimal 50 karakter',
                ],
            ],
            'min_stock' => [
                'field' => 'min_stock',
                'label' => 'Minimum Stock',
                'rules' => 'numeric|greater_than_equal_to[0]',
                'errors' => [
                    'numeric'            => '%s harus berupa angka',
                    'greater_than_equal_to' => '%s harus lebih besar atau sama dengan 0',
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
    /**
     * Constructor for Solenoid controller.
     *
     * Initializes the controller by checking user authentication, loading required
     * models and libraries, and resetting session data when switching controllers.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Check user authentication using common helper
        check_user_authentication();

        $this->load->model(['Solenoid_model', 'Solenoid_type_model']);
        $this->load->library(['form_validation', 'pagination']);

        // Reset session data when switching controllers
        reset_controller_session('solenoid');
    }

    /**
     * List solenoids with pagination and optional search. Renders via render_view().
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
        $typeOptions = $this->Solenoid_model->getSolenoidFilter('type', $sessionData['search'], $sessionData['filter']);

        $totalRows = $this->Solenoid_model->countSolenoid($sessionData['search'], $sessionData['filter']);

        // Setup pagination using common helper
        $config = setup_pagination(site_url('solenoid/index'), $totalRows, self::CONFIG['pagination']['items_per_page']);
        $this->pagination->initialize($config);

        $startData = (int) ($this->uri->segment(3) ?: 0);

        $solenoids = $this->Solenoid_model->getSolenoid(
            self::CONFIG['pagination']['items_per_page'],
            $startData,
            $sessionData['search'],
            $sessionData['filter'],
            $sessionData['sort']
        );

        $data = [
            'title'          => 'Data Solenoid',
            'solenoids'      => $solenoids,
            'pagination'     => ['links' => $this->pagination->create_links()],
            'total_rows'     => $totalRows,
            'searchKeyword'  => $sessionData['search'],
            'sortKeyword'    => ($sessionData['sort'] && strpos($sessionData['sort'], '-') !== false) ? explode('-', $sessionData['sort'], 2) : ['', ''],
            'filterKeyword'  => $sessionData['filter'],
            'hasFilters'     => (!empty($sessionData['search']) || !empty($sessionData['filter']) || !empty($sessionData['sort'])),
            'type_options'   => $typeOptions,
        ];

        render_view('solenoid/index', $data);
    }

    /**
     * Show type selection page before entering solenoid index.
     * Displays available types as image cards; clicking a type navigates to index filtered by that type.
     *
     * @return void
     */
    public function type(): void
    {
        // Fetch all types from the types table - show all available types, not just those with solenoid records
        $allTypes = $this->Solenoid_type_model->getAllTypes();

        // Build list of type rows with image information
        $typeOptions = [];
        $imgPath = FCPATH . 'assets/img/solenoid_types/';
        $defaultUrl = base_url('assets/img/placeholder-image.svg');
        foreach ($allTypes as $row) {
            $t = (string)($row['type'] ?? '');
            if ($t === '') continue;

            $image = $row['image'] ?? null;
            $imageUrl = $defaultUrl;

            // Prefer stored filename if it exists
            if (!empty($image)) {
                $candidate = $imgPath . $image;
                if (is_file($candidate)) {
                    $imageUrl = base_url('assets/img/solenoid_types/' . $image);
                }
            }

            // Try type-based filenames if still using default
            if ($imageUrl === $defaultUrl) {
                $typeSafe = strtolower($t);
                foreach (['.jpg', '.png', '.jpeg', '.gif'] as $ext) {
                    $candidate = $imgPath . $typeSafe . $ext;
                    if (is_file($candidate)) {
                        $imageUrl = base_url('assets/img/solenoid_types/' . $typeSafe . $ext);
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
            'title' => 'Pilih Type Solenoid',
            'type_options' => $typeOptions,
        ];

        render_view('solenoid/type', $data);
    }

    /**
     * Display the add solenoid form and handle form submission.
     *
     * @return void
     */
    public function add(): void
    {
        if ($this->input->method() === 'post') {
            $this->setValidationRules();

            if ($this->form_validation->run()) {
                $this->Solenoid_model->addSolenoid();
                set_message(['success', 'Data solenoid berhasil ditambahkan!']);
                redirect('solenoid');
            }
        }

        // Fetch available solenoid types for dropdown
        $solenoidTypes = $this->Solenoid_type_model->getAllTypes();

        // Check if type is pre-selected from URL parameter
        $preselectedType = $this->input->get('type', true);

        $data = [
            'title' => 'Tambah Solenoid',
            'solenoid_types' => $solenoidTypes,
            'preselected_type' => $preselectedType
        ];
        render_view('solenoid/add', $data);
    }

    /**
     * Display the edit solenoid form and handle form submission.
     *
     * @param string $solenoidId The solenoid ID to edit.
     *
     * @return void
     */
    public function edit(string $solenoidId): void
    {
        $solenoid = $this->Solenoid_model->getById(urldecode($solenoidId));

        if (!$solenoid) {
            show_404();
        }

        if ($this->input->method() === 'post') {
            $this->setValidationRules(true, urldecode($solenoidId));

            if ($this->form_validation->run()) {
                $this->Solenoid_model->editSolenoid(urldecode($solenoidId));
                set_message(['success', 'Data solenoid berhasil diperbarui!']);
                redirect('solenoid');
            }
        }

        // Fetch available solenoid types for dropdown
        $solenoidTypes = $this->Solenoid_type_model->getAllTypes();

        $data['solenoid'] = $solenoid;
        $data['title'] = 'Edit Solenoid';
        $data['solenoid_types'] = $solenoidTypes;
        render_view('solenoid/edit', $data);
    }

    /**
     * Handle solenoid deletion.
     *
     * @param string $solenoidId The solenoid ID to delete.
     *
     * @return void
     */
    public function delete(string $solenoidId): void
    {
        $solenoid = $this->Solenoid_model->getById(urldecode($solenoidId));

        if (!$solenoid) {
            set_message(['danger', 'Data solenoid tidak ditemukan!']);
        } else {
            $this->Solenoid_model->deleteSolenoid($solenoidId);
            set_message(['success', 'Data solenoid berhasil dihapus!']);
        }

        redirect('solenoid');
    }

    /**
     * Downloads solenoid data as Excel file.
     *
     * @return void
     */
    public function download(): void
    {
        try {
            $solenoids = $this->Solenoid_model->getAllSolenoids();
            $this->generateExcelFile($solenoids);
        } catch (Exception $e) {
            log_message('error', 'Excel download error: ' . $e->getMessage());
            set_message(['danger', 'Error downloading file: ' . $e->getMessage()]);
            redirect('solenoid');
        }
    }

    /**
     * Downloads Excel template for solenoid upload.
     *
     * @return void
     */
    public function template(): void
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            // Use letter-style headers like ASRS
            $sheet->setCellValue('A1', 'Type');
            $sheet->setCellValue('B1', 'Subtype');

            $filename = 'Template Data Solenoid.xlsx';
            output_excel_file($spreadsheet, $filename);
        } catch (Exception $e) {
            log_message('error', 'Template download error: ' . $e->getMessage());
            show_error('Error generating template file: ' . $e->getMessage());
        }
    }

    /**
     * Handles Excel file upload and solenoid import.
     *
     * @return void
     */
    // Upload handling is performed in index() via handleFileUpload() to match ASRS (no separate public upload endpoint)
    /**
     * Public wrapper for upload POSTs — delegates to handleFileUpload().
     * Prevents 404 for forms that POST to /solenoid/upload while keeping logic centralized.
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
     * @param array $solenoids Array of solenoid data
     * @return void
     */
    private function generateExcelFile(array $solenoids): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers (letter style like ASRS)
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Type');
        $sheet->setCellValue('C1', 'Subtype');
        $sheet->setCellValue('D1', 'Min Stock');
        $sheet->setCellValue('E1', 'Created At');
        $sheet->setCellValue('F1', 'Updated At');
        $sheet->setCellValue('G1', 'Editor');

        // Apply header styling using common helper
        $highestColumn = $sheet->getHighestColumn();
        apply_excel_header_style($sheet, "A1:{$highestColumn}1");

        // Add data
        $row = 2;
        foreach ($solenoids as $solenoid) {
            $sheet->setCellValue("A{$row}", $solenoid['id']);
            $sheet->setCellValue("B{$row}", $solenoid['type']);
            $sheet->setCellValue("C{$row}", $solenoid['subtype']);
            $sheet->setCellValue("D{$row}", $solenoid['min_stock'] ?? '');
            $sheet->setCellValue("E{$row}", $solenoid['created_at']);
            $sheet->setCellValue("F{$row}", $solenoid['updated_at']);
            $sheet->setCellValue("G{$row}", $solenoid['editor']);
            $row++;
        }

        // Auto-filter and auto-size columns using common helper
        $sheet->setAutoFilter('A1:G1');
        auto_size_excel_columns($sheet, 'A', 'G');

        $filename = 'Data Solenoid.xlsx';
        output_excel_file($spreadsheet, $filename);
    }

    /**
     * Handle file upload posted to index (ASRS-style).
     * This mirrors the upload handling in ASRS User controller but for solenoid data.
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
                if (!$row['A'] && !$row['B'] && !$row['C']) {
                    continue; // skip empty row
                }

                if (!$row['A']) {
                    $skippedData[] = "Type tidak boleh kosong";
                    continue;
                }

                if (!$row['B']) {
                    $skippedData[] = "Subtype tidak boleh kosong";
                    continue;
                }

                $type = $row['A'] ?? '';
                $subtype = $row['B'] ?? '';

                if (strlen($type) > 15) {
                    $skippedData[] = "Type maksimal 15 karakter: {$type}";
                    continue;
                }

                if (strlen($subtype) > 50) {
                    $skippedData[] = "Subtype maksimal 50 karakter: {$subtype}";
                    continue;
                }

                if ($this->Solenoid_model->isSolenoidExists(strtoupper(trim($type)), trim($subtype))) {
                    $skippedData[] = "Kombinasi solenoid sudah terdaftar (Type: {$type}, Subtype: {$subtype})";
                    continue;
                }

                $insertData[] = [
                    'type'       => strtoupper(trim($type)),
                    'subtype'    => trim($subtype),
                    'min_stock'  => null,
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
                    'updated_at' => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
                    'editor'     => $this->session->userdata('user_data')['nik']
                ];
            }

            $insertCount  = 0;
            $skippedCount = count($skippedData);

            // Pre-validate types for each insert row to provide row-level errors instead of DB errors
            $validInsertData = [];
            foreach ($insertData as $rowIndex => $row) {
                $type = $row['type'] ?? '';
                if (empty($type) || !$this->Solenoid_type_model->getByType($type)) {
                    $skippedCount++;
                    $skippedData[] = "Type tidak tersedia atau tidak terdaftar: {$type}";
                    continue;
                }
                $validInsertData[] = $row;
            }

            $insertCount = count($validInsertData);

            if ($insertCount > 0) {
                $this->Solenoid_model->insertBatch($validInsertData);
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
            redirect('solenoid');
        } catch (Exception $e) {
            log_message('error', 'File upload error: ' . $e->getMessage());
            set_message(['danger', 'Terjadi kesalahan dalam membaca file Excel.']);
        }
    }

    /**
     * Handles session state management for search, filter, and sort.
     *
     * @return void
     */
    private function handleSessionState(): void
    {
        // Use the common helper for session state management
        handle_session_state('solenoid', []);
    }

    /**
     * Sets validation rules for solenoid forms.
     *
     * @param bool $isEdit Whether this is for edit form
     * @param string|null $solenoidId The solenoid ID to exclude from uniqueness check (for edit)
     * @return void
     */
    private function setValidationRules(bool $isEdit = false, ?string $solenoidId = null): void
    {
        foreach (self::CONFIG['validation'] as $field => $config) {
            $this->form_validation->set_rules($config['field'], $config['label'], $config['rules'], $config['errors']);
        }

        // Add custom validation for unique solenoid combination
        if ($isEdit && $solenoidId) {
            $this->form_validation->set_rules('type', 'Type', 'callback_check_solenoid_combination[' . $solenoidId . ']');
        } else {
            $this->form_validation->set_rules('type', 'Type', 'callback_check_solenoid_combination');
        }
    }

    /**
     * Custom validation callback to check if solenoid combination already exists.
     *
     * @param string $type The type value
     * @param string|null $excludeId The solenoid ID to exclude from check (for edit)
     * @return bool
     */
    public function check_solenoid_combination(string $type, ?string $excludeId = null): bool
    {
        $subtype = $this->input->post('subtype', true);

        if (!empty($type) && !empty($subtype)) {
            if ($this->Solenoid_model->isSolenoidExists($type, $subtype, $excludeId)) {
                $this->form_validation->set_message('check_solenoid_combination', 'Kombinasi solenoid (Type: {field}, Subtype: ' . $subtype . ') sudah terdaftar');
                return false;
            }
        }

        return true;
    }

    /**
     * Custom validation callback to check if selected type exists in solenoid_types table.
     *
     * @param string $type The selected type value
     * @return bool
     */
    public function check_type_exists(string $type): bool
    {
        if (!empty($type)) {
            $typeExists = $this->Solenoid_type_model->getByType($type);
            if (!$typeExists) {
                $this->form_validation->set_message('check_type_exists', 'Type {field} tidak tersedia. Silakan pilih type yang valid.');
                return false;
            }
        }
        return true;
    }
}
