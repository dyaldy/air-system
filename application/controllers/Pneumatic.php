<?php
defined('BASEPATH') or exit('No direct script access allowed');

require 'vendor/autoload.php';

use TCPDF;

/**
 * Pneumatic controller for air-system.
 *
 * Manage pneumatics: listing, search/filter/sort, CRUD operations, and CSV import/export with PDF download following ASRS conventions.
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
     * Constructor for Pneumatic controller.
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

        $this->load->model(['Pneumatic_model', 'Pneumatic_type_model']);
        $this->load->library(['form_validation', 'pagination']);

        // Reset session data when switching controllers
        reset_controller_session('pneumatic');
    }

    /**
     * List pneumatics with pagination and optional search. Renders via render_view().
     *
     * @return void
     */
    public function index(): void
    {
        // Handle CSV uploads using the ASRS-style helper
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
        $typeOptions = $this->Pneumatic_model->getPneumaticFilter('type', $sessionData['search'], $sessionData['filter']);

        $totalRows = $this->Pneumatic_model->countPneumatic($sessionData['search'], $sessionData['filter']);

        // Setup pagination using common helper
        $config = setup_pagination(site_url('pneumatic/index'), $totalRows, self::CONFIG['pagination']['items_per_page']);
        $this->pagination->initialize($config);

        $startData = (int) ($this->uri->segment(3) ?: 0);

        $pneumatics = $this->Pneumatic_model->getPneumatic(
            self::CONFIG['pagination']['items_per_page'],
            $startData,
            $sessionData['search'],
            $sessionData['filter'],
            $sessionData['sort']
        );

        $data = [
            'title'          => 'Data Pneumatic',
            'pneumatics'     => $pneumatics,
            'pagination'     => ['links' => $this->pagination->create_links()],
            'total_rows'     => $totalRows,
            'searchKeyword'  => $sessionData['search'],
            'sortKeyword'    => ($sessionData['sort'] && strpos($sessionData['sort'], '-') !== false) ? explode('-', $sessionData['sort'], 2) : ['', ''],
            'filterKeyword'  => $sessionData['filter'],
            'hasFilters'     => (!empty($sessionData['search']) || !empty($sessionData['filter']) || !empty($sessionData['sort'])),
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
     * Downloads pneumatic data as CSV file.
     *
     * @return void
     */
    public function download(): void
    {
        try {
            $pneumatics = $this->Pneumatic_model->getAllPneumatics();
            $this->generateCSVFile($pneumatics);
        } catch (Exception $e) {
            log_message('error', 'CSV download error: ' . $e->getMessage());
            set_message(['danger', 'Error downloading file: ' . $e->getMessage()]);
            redirect('pneumatic');
        }
    }

    /**
     * Downloads pneumatic data as PDF file.
     *
     * @return void
     */
    public function downloadPDF(): void
    {
        try {
            $pneumatics = $this->Pneumatic_model->getAllPneumatics();
            $this->generatePDFFile($pneumatics);
        } catch (Exception $e) {
            log_message('error', 'PDF download error: ' . $e->getMessage());
            set_message(['danger', 'Error downloading file: ' . $e->getMessage()]);
            redirect('pneumatic');
        }
    }

    /**
     * Downloads CSV template for pneumatic upload.
     *
     * @return void
     */
    public function template(): void
    {
        try {
            $filename = 'Template Data Pneumatic.csv';

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Write header row
            fputcsv($output, ['Type', 'Bore', 'Stroke']);

            fclose($output);
            exit;
        } catch (Exception $e) {
            log_message('error', 'Template download error: ' . $e->getMessage());
            show_error('Error generating template file: ' . $e->getMessage());
        }
    }

    /**
     * Handles CSV file upload and pneumatic import.
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
     * Generates CSV file for download.
     *
     * @param array $pneumatics Array of pneumatic data
     * @return void
     */
    private function generateCSVFile(array $pneumatics): void
    {
        $filename = 'Data Pneumatic ' . date('Y-m-d H-i-s') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Add BOM for UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Write header row
        fputcsv($output, ['Pneumatic ID', 'Type', 'Bore', 'Stroke', 'Created At', 'Updated At', 'Editor']);

        // Write data rows
        foreach ($pneumatics as $pneumatic) {
            fputcsv($output, [
                $pneumatic['pneumatic_id'],
                $pneumatic['type'],
                $pneumatic['bore'],
                $pneumatic['stroke'],
                $pneumatic['created_at'],
                $pneumatic['updated_at'],
                $pneumatic['editor']
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Generates PDF file for download.
     *
     * @param array $pneumatics Array of pneumatic data
     * @return void
     */
    private function generatePDFFile(array $pneumatics): void
    {
        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('Air System');
        $pdf->SetAuthor('Air System');
        $pdf->SetTitle('Data Pneumatic');

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
        $pdf->Cell(0, 10, 'Data Pneumatic', 0, 1, 'C');
        $pdf->Ln(5);

        // Table header
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(66, 139, 202);
        $pdf->SetTextColor(255, 255, 255);

        $pdf->Cell(50, 7, 'Pneumatic ID', 1, 0, 'C', 1);
        $pdf->Cell(30, 7, 'Type', 1, 0, 'C', 1);
        $pdf->Cell(20, 7, 'Bore', 1, 0, 'C', 1);
        $pdf->Cell(20, 7, 'Stroke', 1, 0, 'C', 1);
        $pdf->Cell(40, 7, 'Created At', 1, 0, 'C', 1);
        $pdf->Cell(40, 7, 'Updated At', 1, 0, 'C', 1);
        $pdf->Cell(30, 7, 'Editor', 1, 1, 'C', 1);

        // Table data
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(0, 0, 0);
        $fill = false;

        foreach ($pneumatics as $pneumatic) {
            $pdf->SetFillColor(245, 245, 245);
            $pdf->Cell(50, 6, $pneumatic['pneumatic_id'], 1, 0, 'L', $fill);
            $pdf->Cell(30, 6, $pneumatic['type'], 1, 0, 'L', $fill);
            $pdf->Cell(20, 6, $pneumatic['bore'], 1, 0, 'C', $fill);
            $pdf->Cell(20, 6, $pneumatic['stroke'], 1, 0, 'C', $fill);
            $pdf->Cell(40, 6, $pneumatic['created_at'], 1, 0, 'C', $fill);
            $pdf->Cell(40, 6, $pneumatic['updated_at'], 1, 0, 'C', $fill);
            $pdf->Cell(30, 6, $pneumatic['editor'], 1, 1, 'C', $fill);
            $fill = !$fill;
        }

        $filename = 'Data Pneumatic ' . date('Y-m-d H-i-s') . '.pdf';
        $pdf->Output($filename, 'D');
        exit;
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
            // Read CSV file
            $handle = fopen($file, 'r');
            if ($handle === false) {
                throw new Exception('Unable to open CSV file');
            }

            // Skip BOM if present
            $bom = fread($handle, 3);
            if ($bom !== chr(0xEF) . chr(0xBB) . chr(0xBF)) {
                rewind($handle);
            }

            $data = [];
            while (($row = fgetcsv($handle)) !== false) {
                $data[] = $row;
            }
            fclose($handle);

            if (empty($data)) {
                throw new Exception('CSV file is empty');
            }
            array_shift($data); // remove header row

            $skippedData = [];
            $insertData  = [];

            foreach ($data as $rowIndex => $row) {
                // Validate required fields
                if (count($row) < 3 || (!$row[0] && !$row[1] && !$row[2])) {
                    continue; // skip empty row
                }

                if (empty($row[0])) {
                    $skippedData[] = "Type tidak boleh kosong";
                    continue;
                }

                if (empty($row[1])) {
                    $skippedData[] = "Bore tidak boleh kosong";
                    continue;
                }

                if (empty($row[2])) {
                    $skippedData[] = "Stroke tidak boleh kosong";
                    continue;
                }

                $type = $row[0] ?? '';
                $bore = $row[1] ?? '';
                $stroke = $row[2] ?? '';

                if (strlen($type) > 15) {
                    $skippedData[] = "Type maksimal 15 karakter: {$type}";
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

                $pneumaticId = 'pnm-' . strtolower(trim($type)) . '-' . $bore . '-' . $stroke;

                if ($this->Pneumatic_model->isPneumaticIdExists($pneumaticId)) {
                    $skippedData[] = "Kombinasi pneumatic sudah terdaftar (Type: {$type}, Bore: {$bore}, Stroke: {$stroke})";
                    continue;
                }

                $insertData[] = [
                    'pneumatic_id' => $pneumaticId,
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
            set_message(['danger', 'Terjadi kesalahan dalam membaca file CSV.']);
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
        handle_session_state('pneumatic', []);
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
            $this->form_validation->set_rules('type', 'Type', 'callback_check_pneumatic_combination');
        }
    }

    /**
     * Custom validation callback to check if pneumatic combination already exists.
     *
     * @param string $type The type value
     * @return bool
     */
    public function check_pneumatic_combination(string $type): bool
    {
        $bore = $this->input->post('bore', true);
        $stroke = $this->input->post('stroke', true);

        if (!empty($type) && !empty($bore) && !empty($stroke)) {
            $pneumaticId = 'pnm-' . strtolower($type) . '-' . $bore . '-' . $stroke;

            if ($this->Pneumatic_model->isPneumaticIdExists($pneumaticId)) {
                $this->form_validation->set_message('check_pneumatic_combination', 'Kombinasi pneumatic (Type: {field}, Bore: ' . $bore . ', Stroke: ' . $stroke . ') sudah terdaftar');
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
