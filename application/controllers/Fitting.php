<?php
defined('BASEPATH') or exit('No direct script access allowed');

require 'vendor/autoload.php';

use TCPDF;

/**
 * Fitting controller for air-system.
 *
 * Manage fittings: listing, search/filter/sort, CRUD operations, and CSV import/export with PDF download following pneumatic conventions.
 *
 * @package AirSystem
 * @subpackage Controllers
 * @category Fitting
 * @author Apparel One Indonesia
 * @version 1.0.0
 * @property CI_Session $session
 * @property CI_Input $input
 * @property CI_URI $uri
 * @property CI_Form_validation $form_validation
 * @property CI_Pagination $pagination
 * @property Fitting_model $Fitting_model
 */
class Fitting extends CI_Controller
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
        'upload' => [
            'max_size' => 2048, // KB
            'allowed_types' => 'csv'
        ],
        'validation_rules' => [
            'type' => [
                'field' => 'type',
                'label' => 'Type',
                'rules' => 'required|trim|max_length[30]',
                'errors' => [
                    'required'   => '%s harus diisi',
                    'max_length' => '%s maksimal 30 karakter',
                ],
            ],
            'subtype' => [
                'field' => 'subtype',
                'label' => 'Subtype',
                'rules' => 'required|trim|max_length[50]|callback_validate_subtype_format',
                'errors' => [
                    'required'   => '%s harus diisi',
                    'max_length' => '%s maksimal 50 karakter',
                ],
            ],
            'D1' => [
                'field' => 'D1',
                'label' => 'D1',
                'rules' => 'required|numeric|greater_than[0]',
                'errors' => [
                    'required'     => '%s harus diisi',
                    'numeric'      => '%s harus berupa angka',
                    'greater_than' => '%s harus lebih besar dari 0',
                ],
            ],
            'D2' => [
                'field' => 'D2',
                'label' => 'D2',
                'rules' => 'required|numeric|greater_than[0]',
                'errors' => [
                    'required'     => '%s harus diisi',
                    'numeric'      => '%s harus berupa angka',
                    'greater_than' => '%s harus lebih besar dari 0',
                ],
            ],
            'D3' => [
                'field' => 'D3',
                'label' => 'D3',
                'rules' => 'required|numeric|greater_than[0]',
                'errors' => [
                    'required'     => '%s harus diisi',
                    'numeric'      => '%s harus berupa angka',
                    'greater_than' => '%s harus lebih besar dari 0',
                ],
            ],
            'R_DRAT' => [
                'field' => 'R_DRAT',
                'label' => 'R(DRAT)',
                'rules' => 'required|trim|max_length[20]',
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
    public function __construct()
    {
        parent::__construct();

        // Check user authentication using common helper
        check_user_authentication();

        $this->load->model('Fitting_model');
        $this->load->model('Fitting_type_model');
        $this->load->model('Fitting_subtype_model');
        $this->load->library(['form_validation', 'pagination']);

        // Reset session data when switching controllers
        reset_controller_session('fitting');
    }

    /**
     * List fittings with pagination and optional search. Renders via render_view().
     *
     * @return void
     */
    public function index(): void
    {
        // Handle CSV uploads
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
            $getType = strtoupper($getType);
            $this->session->set_userdata('filter', ['type' => [$getType]]);
        }

        // Handle search, filter, and sort form submissions
        $this->handleSessionState();

        $sessionData = [
            'search' => $this->session->userdata('keyword'),
            'filter' => $this->session->userdata('filter'),
            'sort'   => $this->session->userdata('sort'),
        ];

        // Provide distinct values for filter dropdowns (respect current search/filter state)
        $typeOptions = $this->Fitting_model->getFittingFilter('type', $sessionData['search'], $sessionData['filter']);
        $subtypeOptions = $this->Fitting_model->getFittingFilter('subtype', $sessionData['search'], $sessionData['filter']);

        // Count total records
        $totalRows = $this->Fitting_model->countFitting($sessionData['search'], $sessionData['filter']);

        // Setup pagination using common helper
        $config = setup_pagination(site_url('fitting/index'), $totalRows, self::CONFIG['pagination']['items_per_page']);
        $this->pagination->initialize($config);

        $startData = (int) ($this->uri->segment(3) ?: 0);

        $fittings = $this->Fitting_model->getFitting(
            self::CONFIG['pagination']['items_per_page'],
            $startData,
            $sessionData['search'],
            $sessionData['filter'],
            $sessionData['sort']
        );

        $data = [
            'title'          => 'Data Fitting',
            'fittings'       => $fittings,
            'pagination'     => ['links' => $this->pagination->create_links()],
            'total_rows'     => $totalRows,
            'searchKeyword'  => $sessionData['search'],
            'sortKeyword'    => ($sessionData['sort'] && strpos($sessionData['sort'], '-') !== false) ? explode('-', $sessionData['sort'], 2) : ['', ''],
            'filterKeyword'  => $sessionData['filter'],
            'hasFilters'     => (!empty($sessionData['search']) || !empty($sessionData['filter']) || !empty($sessionData['sort'])),
            'type_options'   => $typeOptions,
            'subtype_options' => $subtypeOptions,
        ];

        render_view('fitting/index', $data);
    }

    /**
     * Show type selection page before entering fitting index.
     * Displays available types as image cards; clicking a type navigates to index filtered by that type.
     *
     * @return void
     */
    public function type(): void
    {
        // Fetch all types from the types table - show all available types, not just those with fitting records
        $allTypes = $this->Fitting_type_model->getAllTypes();

        // Build list of type rows with image information
        $typeOptions = [];
        $imgPath = FCPATH . 'assets/img/fitting_types/';
        $defaultUrl = base_url('assets/img/placeholder-image.svg');

        foreach ($allTypes as $typeRow) {
            $type = $typeRow['type'];
            $imageUrl = $defaultUrl; // default fallback

            if (!empty($typeRow['image']) && file_exists($imgPath . $typeRow['image'])) {
                $imageUrl = base_url('assets/img/fitting_types/' . $typeRow['image']);
            }

            $typeOptions[] = [
                'type' => $type,
                'image_url' => $imageUrl,
            ];
        }

        $data = [
            'title' => 'Pilih Type Fitting',
            'type_options' => $typeOptions,
        ];

        render_view('fitting/type', $data);
    }

    /**
     * Display the add fitting form and handle form submission.
     *
     * @return void
     */
    public function add(): void
    {
        if ($this->input->method() === 'post') {
            $this->setValidationRules();

            if ($this->form_validation->run()) {
                try {
                    $this->Fitting_model->addFitting();
                    set_message(['success', 'Data fitting berhasil ditambahkan!']);
                    redirect('fitting');
                } catch (Exception $e) {
                    set_message(['danger', 'Error: ' . $e->getMessage()]);
                }
            }
        }

        // Fetch available fitting types for dropdown
        $fittingTypes = $this->Fitting_type_model->getAllTypes();

        // Check if type is pre-selected from URL parameter or form data
        $preselectedType = $this->input->get('type', true) ?: set_value('type');

        // Get subtypes for preselected type if available
        $subtypes = [];
        if ($preselectedType) {
            $subtypes = $this->Fitting_subtype_model->getSubtypesByParentType($preselectedType);
        }

        $data = [
            'title' => 'Tambah Fitting',
            'fitting_types' => $fittingTypes,
            'preselected_type' => $preselectedType,
            'subtypes' => $subtypes
        ];
        render_view('fitting/add', $data);
    }

    /**
     * Display the edit fitting form and handle form submission.
     *
     * @param string $fittingId The fitting ID to edit (base64 encoded).
     *
     * @return void
     */
    public function edit(string $fittingId): void
    {
        // Decode the base64-encoded fitting ID to handle special characters like slashes
        // Base64 encoding is used because Apache blocks URL-encoded slashes (%2F)
        $fittingId = base64_decode($fittingId);

        $fitting = $this->Fitting_model->getById($fittingId);

        if (!$fitting) {
            show_404();
        }

        if ($this->input->method() === 'post') {
            $this->setValidationRules(true);

            if ($this->form_validation->run()) {
                $this->Fitting_model->editFitting($fittingId);
                set_message(['success', 'Data fitting berhasil diperbarui!']);
                redirect('fitting');
            }
        }

        // Fetch available fitting types for dropdown
        $fittingTypes = $this->Fitting_type_model->getAllTypes();

        // Get subtypes for the current fitting's type
        $subtypes = [];
        if ($fitting && isset($fitting['type'])) {
            $subtypes = $this->Fitting_subtype_model->getSubtypesByParentType($fitting['type']);
        }

        $data['fitting'] = $fitting;
        $data['title'] = 'Edit Fitting';
        $data['fitting_types'] = $fittingTypes;
        $data['subtypes'] = $subtypes;
        render_view('fitting/edit', $data);
    }

    /**
     * Handle fitting deletion.
     *
     * @param string $fittingId The fitting ID to delete (base64 encoded).
     *
     * @return void
     */
    public function delete(string $fittingId): void
    {
        // Decode the base64-encoded fitting ID to handle special characters like slashes
        // Base64 encoding is used because Apache blocks URL-encoded slashes (%2F)
        $fittingId = base64_decode($fittingId);

        $fitting = $this->Fitting_model->getById($fittingId);

        if (!$fitting) {
            set_message(['danger', 'Data fitting tidak ditemukan!']);
        } else {
            $this->Fitting_model->deleteFitting($fittingId);
            set_message(['success', 'Data fitting berhasil dihapus!']);
        }

        redirect('fitting');
    }

    /**
     * Downloads fitting data as CSV file.
     *
     * @return void
     */
    public function download(): void
    {
        try {
            $fittings = $this->Fitting_model->getAllFittings();
            $this->generateCSVFile($fittings);
        } catch (Exception $e) {
            log_message('error', 'CSV download error: ' . $e->getMessage());
            set_message(['danger', 'Error downloading file: ' . $e->getMessage()]);
            redirect('fitting');
        }
    }

    /**
     * Downloads fitting data as PDF file.
     *
     * @return void
     */
    public function downloadPDF(): void
    {
        try {
            $fittings = $this->Fitting_model->getAllFittings();
            $this->generatePDFFile($fittings);
        } catch (Exception $e) {
            log_message('error', 'PDF download error: ' . $e->getMessage());
            set_message(['danger', 'Error downloading file: ' . $e->getMessage()]);
            redirect('fitting');
        }
    }

    /**
     * Downloads CSV template for fitting upload.
     *
     * @return void
     */
    public function template(): void
    {
        try {
            $filename = 'Template Data Fitting.csv';

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Write header row
            fputcsv($output, ['Type', 'D1', 'D2', 'D3', 'R(DRAT)']);

            fclose($output);
            exit;
        } catch (Exception $e) {
            log_message('error', 'Template download error: ' . $e->getMessage());
            show_error('Error generating template file: ' . $e->getMessage());
        }
    }

    /**
     * Public wrapper for upload POSTs — delegates to handleFileUpload().
     * Prevents 404 for forms that POST to /fitting/upload while keeping logic centralized.
     *
     * @return void
     */
    public function upload(): void
    {
        $this->handleFileUpload();
    }

    ## Private Helper Methods

    /**
     * Handles session state for search, filter, and sort operations.
     *
     * @return void
     */
    private function handleSessionState(): void
    {
        // Use the common helper for session state management
        handle_session_state('fitting', []);
    }

    /**
     * Generates CSV file for download.
     *
     * @param array $fittings Array of fitting data
     * @return void
     */
    private function generateCSVFile(array $fittings): void
    {
        $filename = 'Data Fitting ' . date('Y-m-d H-i-s') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Add BOM for UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Write header row
        fputcsv($output, ['Fitting ID', 'Type', 'D1', 'D2', 'D3', 'R(DRAT)', 'Created At', 'Updated At', 'Editor']);

        // Write data rows
        foreach ($fittings as $fitting) {
            fputcsv($output, [
                $fitting['fitting_id'],
                $fitting['type'],
                !empty($fitting['D1']) ? $fitting['D1'] : '',
                !empty($fitting['D2']) ? $fitting['D2'] : '',
                !empty($fitting['D3']) ? $fitting['D3'] : '',
                !empty($fitting['R_DRAT']) ? $fitting['R_DRAT'] : '',
                $fitting['created_at'],
                $fitting['updated_at'],
                $fitting['editor']
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Generates PDF file for download.
     *
     * @param array $fittings Array of fitting data
     * @return void
     */
    private function generatePDFFile(array $fittings): void
    {
        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('Air System');
        $pdf->SetAuthor('Air System');
        $pdf->SetTitle('Data Fitting');

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
        $pdf->Cell(0, 10, 'Data Fitting', 0, 1, 'C');
        $pdf->Ln(5);

        // Table header
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetFillColor(66, 139, 202);
        $pdf->SetTextColor(255, 255, 255);

        $pdf->Cell(50, 7, 'Fitting ID', 1, 0, 'C', 1);
        $pdf->Cell(30, 7, 'Type', 1, 0, 'C', 1);
        $pdf->Cell(20, 7, 'D1', 1, 0, 'C', 1);
        $pdf->Cell(20, 7, 'D2', 1, 0, 'C', 1);
        $pdf->Cell(20, 7, 'D3', 1, 0, 'C', 1);
        $pdf->Cell(20, 7, 'R(DRAT)', 1, 0, 'C', 1);
        $pdf->Cell(35, 7, 'Created At', 1, 0, 'C', 1);
        $pdf->Cell(35, 7, 'Updated At', 1, 0, 'C', 1);
        $pdf->Cell(20, 7, 'Editor', 1, 1, 'C', 1);

        // Table data
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetTextColor(0, 0, 0);
        $fill = false;

        foreach ($fittings as $fitting) {
            $pdf->SetFillColor(245, 245, 245);
            $pdf->Cell(50, 6, $fitting['fitting_id'], 1, 0, 'L', $fill);
            $pdf->Cell(30, 6, $fitting['type'], 1, 0, 'L', $fill);
            $pdf->Cell(20, 6, !empty($fitting['D1']) ? $fitting['D1'] : '', 1, 0, 'C', $fill);
            $pdf->Cell(20, 6, !empty($fitting['D2']) ? $fitting['D2'] : '', 1, 0, 'C', $fill);
            $pdf->Cell(20, 6, !empty($fitting['D3']) ? $fitting['D3'] : '', 1, 0, 'C', $fill);
            $pdf->Cell(20, 6, !empty($fitting['R_DRAT']) ? $fitting['R_DRAT'] : '', 1, 0, 'C', $fill);
            $pdf->Cell(35, 6, $fitting['created_at'], 1, 0, 'C', $fill);
            $pdf->Cell(35, 6, $fitting['updated_at'], 1, 0, 'C', $fill);
            $pdf->Cell(20, 6, $fitting['editor'], 1, 1, 'C', $fill);
            $fill = !$fill;
        }

        $filename = 'Data Fitting ' . date('Y-m-d H-i-s') . '.pdf';
        $pdf->Output($filename, 'D');
        exit;
    }

    /**
     * Set validation rules for fitting forms.
     *
     * @param bool $isEdit Whether this is an edit operation.
     * @return void
     */
    private function setValidationRules(bool $isEdit = false): void
    {
        // Set basic required rules for type and subtype
        foreach (['type', 'subtype'] as $fieldName) {
            if (isset(self::CONFIG['validation_rules'][$fieldName])) {
                $rules = self::CONFIG['validation_rules'][$fieldName];
                $this->form_validation->set_rules(
                    $rules['field'],
                    $rules['label'],
                    $rules['rules'],
                    $rules['errors'] ?? []
                );
            }
        }

        // Set conditional rules for D1, D2, D3, R_DRAT based on checkboxes
        $dimensionFields = ['D1', 'D2', 'D3', 'R_DRAT'];
        foreach ($dimensionFields as $fieldName) {
            $checkboxName = 'enable_' . strtolower($fieldName);
            $isFieldEnabled = $this->input->post($checkboxName);

            if ($isFieldEnabled) {
                // Field is enabled, apply validation rules
                if (in_array($fieldName, ['D1', 'D2', 'D3'])) {
                    $this->form_validation->set_rules(
                        $fieldName,
                        $fieldName,
                        'required|numeric|greater_than[0]',
                        [
                            'required'     => $fieldName . ' harus diisi',
                            'numeric'      => $fieldName . ' harus berupa angka',
                            'greater_than' => $fieldName . ' harus lebih besar dari 0',
                        ]
                    );
                } elseif ($fieldName === 'R_DRAT') {
                    $this->form_validation->set_rules(
                        'R_DRAT',
                        'R(DRAT)',
                        'required|trim|max_length[20]',
                        [
                            'required'   => 'R(DRAT) harus diisi',
                            'max_length' => 'R(DRAT) maksimal 20 karakter',
                        ]
                    );
                }
            } else {
                // Field is not enabled, set optional validation
                if (in_array($fieldName, ['D1', 'D2', 'D3'])) {
                    $this->form_validation->set_rules(
                        $fieldName,
                        $fieldName,
                        'callback_validate_optional_numeric',
                        [
                            'validate_optional_numeric' => $fieldName . ' harus berupa angka yang valid jika diisi'
                        ]
                    );
                } elseif ($fieldName === 'R_DRAT') {
                    $this->form_validation->set_rules(
                        'R_DRAT',
                        'R(DRAT)',
                        'trim|max_length[20]',
                        [
                            'max_length' => 'R(DRAT) maksimal 20 karakter',
                        ]
                    );
                }
            }
        }

        // Custom validation to ensure at least one dimension field is enabled
        $this->form_validation->set_rules(
            'enable_d1',
            'Field Selection',
            'callback_validate_at_least_one_field',
            [
                'validate_at_least_one_field' => 'Minimal satu field dimensi harus dipilih'
            ]
        );
    }

    /**
     * Handle file uploads and Excel processing.
     *
     * @return void
     */
    private function handleFileUpload(): void
    {
        if ($this->input->method() === 'post' && isset($_FILES['file'])) {
            try {
                $this->processExcelUpload();
            } catch (Exception $e) {
                log_message('error', 'File upload error: ' . $e->getMessage());
                set_message(['danger', 'Error processing file: ' . $e->getMessage()]);
                redirect('fitting');
            }
        }
    }

    /**
     * Process CSV file upload and import data.
     *
     * @return void
     * @throws Exception
     */
    private function processExcelUpload(): void
    {
        if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload error.');
        }

        $inputFileName = $_FILES['file']['tmp_name'];

        // Read CSV file
        $handle = fopen($inputFileName, 'r');
        if ($handle === false) {
            throw new Exception('Unable to open CSV file');
        }

        // Skip BOM if present
        $bom = fread($handle, 3);
        if ($bom !== chr(0xEF) . chr(0xBB) . chr(0xBF)) {
            rewind($handle);
        }

        // Skip header row
        fgetcsv($handle);

        $batchData = [];
        $errors = [];
        $processedCount = 0;
        $rowNum = 2; // Start from 2 (after header)

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 5) {
                $rowNum++;
                continue; // Skip incomplete rows
            }

            $type = trim($row[0]);
            $D1 = $row[1];
            $D2 = $row[2];
            $D3 = $row[3];
            $R_DRAT = trim($row[4]);

            // Skip empty rows
            if (empty($type) && empty($D1) && empty($D2) && empty($D3) && empty($R_DRAT)) {
                $rowNum++;
                continue;
            }

            // Validate required type field
            if (empty($type)) {
                $errors[] = "Baris {$rowNum}: Type harus diisi";
                $rowNum++;
                continue;
            }

            // Check if at least one dimension field is provided
            if (empty($D1) && empty($D2) && empty($D3) && empty($R_DRAT)) {
                $errors[] = "Baris {$rowNum}: Minimal satu field dimensi (D1, D2, D3, atau R_DRAT) harus diisi";
                $rowNum++;
                continue;
            }

            // Validate numeric fields if provided
            $validatedD1 = null;
            $validatedD2 = null;
            $validatedD3 = null;

            if (!empty($D1)) {
                if (!is_numeric($D1) || (float)$D1 <= 0) {
                    $errors[] = "Baris {$rowNum}: D1 harus berupa angka positif";
                    $rowNum++;
                    continue;
                }
                $validatedD1 = (float)$D1;
            }

            if (!empty($D2)) {
                if (!is_numeric($D2) || (float)$D2 <= 0) {
                    $errors[] = "Baris {$rowNum}: D2 harus berupa angka positif";
                    $rowNum++;
                    continue;
                }
                $validatedD2 = (float)$D2;
            }

            if (!empty($D3)) {
                if (!is_numeric($D3) || (float)$D3 <= 0) {
                    $errors[] = "Baris {$rowNum}: D3 harus berupa angka positif";
                    $rowNum++;
                    continue;
                }
                $validatedD3 = (float)$D3;
            }

            $type = strtoupper($type);

            // Generate fitting_id with only non-null values
            $idParts = ['fit', strtolower(str_replace(' ', '_', $type))];

            if ($validatedD1 !== null) $idParts[] = number_format($validatedD1, 1);
            if ($validatedD2 !== null) $idParts[] = number_format($validatedD2, 1);
            if ($validatedD3 !== null) $idParts[] = number_format($validatedD3, 1);
            if (!empty($R_DRAT)) $idParts[] = str_replace('"', '', $R_DRAT);

            $fittingId = implode('-', $idParts);

            // Check for duplicate ID
            if ($this->Fitting_model->isFittingIdExists($fittingId)) {
                $errors[] = "Baris {$rowNum}: Fitting dengan ID '{$fittingId}' sudah ada";
                $rowNum++;
                continue;
            }

            $batchData[] = [
                'fitting_id' => $fittingId,
                'type' => $type,
                'D1' => $validatedD1,
                'D2' => $validatedD2,
                'D3' => $validatedD3,
                'R_DRAT' => !empty($R_DRAT) ? $R_DRAT : null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'editor' => $this->session->userdata('user_data')['nik'],
            ];
            $processedCount++;
            $rowNum++;
        }

        fclose($handle);

        if (!empty($batchData)) {
            $this->Fitting_model->insertBatch($batchData);
            $message = "Berhasil mengimpor {$processedCount} data fitting";
            if (!empty($errors)) {
                $message .= '. Terdapat ' . count($errors) . ' baris dengan error.';
            }
            set_message(['success', $message]);
        } else {
            set_message(['warning', 'Tidak ada data yang diimpor. ' . implode(', ', array_slice($errors, 0, 3))]);
        }

        redirect('fitting');
    }

    /**
     * Get subtypes for a specific fitting type via AJAX.
     * 
     * @return void
     */
    public function getSubtypes(): void
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $type = $this->input->post('type', true);

        if (!$type) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Type is required']));
            return;
        }

        $subtypes = $this->Fitting_subtype_model->getSubtypesByParentType($type);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'subtypes' => $subtypes
            ]));
    }



    /**
     * Custom validation for subtype format (uppercase letters, numbers, and underscores only)
     */
    public function validate_subtype_format($str): bool
    {
        if (!preg_match('/^[A-Z0-9_]+$/', $str)) {
            $this->form_validation->set_message('validate_subtype_format', 'Subtype harus menggunakan huruf besar, angka, dan underscore saja (contoh: MALE_THREAD, FEMALE_THREAD, 90_DEGREE)');
            return false;
        }
        return true;
    }

    /**
     * Custom validation for optional numeric fields
     */
    public function validate_optional_numeric($str): bool
    {
        if (empty($str)) {
            return true; // Allow empty values
        }

        if (!is_numeric($str) || floatval($str) <= 0) {
            return false;
        }

        return true;
    }

    /**
     * Custom validation to ensure at least one dimension field is selected
     */
    public function validate_at_least_one_field($str): bool
    {
        $enableD1 = $this->input->post('enable_d1');
        $enableD2 = $this->input->post('enable_d2');
        $enableD3 = $this->input->post('enable_d3');
        $enableRDrat = $this->input->post('enable_r_drat');

        return ($enableD1 || $enableD2 || $enableD3 || $enableRDrat);
    }
}
