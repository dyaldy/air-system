<?php
defined('BASEPATH') or exit('No direct script access allowed');

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Fitting controller for air-system.
 *
 * Manage fittings: listing, search/filter/sort, CRUD operations, and Excel import/export following pneumatic conventions.
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
            'allowed_types' => 'xlsx|xls'
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

        // Check authentication
        if (!$this->session->userdata('user_data')) {
            redirect(base_url());
        }

        $this->load->model('Fitting_model');
        $this->load->model('Fitting_type_model');
        $this->load->model('Fitting_subtype_model');
        $this->load->library(['form_validation', 'pagination']);
        $this->load->helper('common');

        // Reset session if controller changed
        if ($this->session->userdata('controller') !== 'fitting') {
            $this->session->set_userdata('controller', 'fitting');
            $this->session->unset_userdata(['keyword', 'sort', 'filter']);
        }
    }

    /**
     * List fittings with pagination and optional search. Renders via render_view().
     *
     * @return void
     */
    public function index(): void
    {
        // Handle Excel uploads
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

        // Pagination configuration
        $config = [
            'base_url'   => site_url('fitting/index'),
            'total_rows' => $totalRows,
            'per_page'   => self::CONFIG['pagination']['items_per_page'],
        ];
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
     * @param string $fittingId The fitting ID to edit.
     *
     * @return void
     */
    public function edit(string $fittingId): void
    {
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
     * @param string $fittingId The fitting ID to delete.
     *
     * @return void
     */
    public function delete(string $fittingId): void
    {
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
     * Downloads fitting data as Excel file.
     *
     * @return void
     */
    public function download(): void
    {
        try {
            $fittings = $this->Fitting_model->getAllFittings();
            $this->generateExcelFile($fittings);
        } catch (Exception $e) {
            log_message('error', 'Excel download error: ' . $e->getMessage());
            set_message(['danger', 'Error downloading file: ' . $e->getMessage()]);
            redirect('fitting');
        }
    }

    /**
     * Downloads Excel template for fitting upload.
     *
     * @return void
     */
    public function template(): void
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            // Use letter-style headers
            $sheet->setCellValue('A1', 'Type');
            $sheet->setCellValue('B1', 'D1');
            $sheet->setCellValue('C1', 'D2');
            $sheet->setCellValue('D1', 'D3');
            $sheet->setCellValue('E1', 'R(DRAT)');

            $filename = 'Template Data Fitting.xlsx';
            $this->outputExcelFile($spreadsheet, $filename);
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

        if ($this->input->post('clear_all')) {
            $this->session->unset_userdata(['keyword', 'sort', 'filter']);
            redirect('fitting');
        }
    }

    /**
     * Generates Excel file for download.
     *
     * @param array $fittings Array of fitting data
     * @return void
     */
    private function generateExcelFile(array $fittings): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'Fitting ID');
        $sheet->setCellValue('B1', 'Type');
        $sheet->setCellValue('C1', 'D1');
        $sheet->setCellValue('D1', 'D2');
        $sheet->setCellValue('E1', 'D3');
        $sheet->setCellValue('F1', 'R(DRAT)');
        $sheet->setCellValue('G1', 'Created At');
        $sheet->setCellValue('H1', 'Updated At');
        $sheet->setCellValue('I1', 'Editor');

        // Style headers
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E9ECEF']
            ]
        ];
        $highestColumn = $sheet->getHighestColumn();
        $sheet->getStyle("A1:{$highestColumn}1")->applyFromArray($headerStyle);

        // Add data
        $row = 2;
        foreach ($fittings as $fitting) {
            $sheet->setCellValue("A{$row}", $fitting['fitting_id']);
            $sheet->setCellValue("B{$row}", $fitting['type']);
            $sheet->setCellValue("C{$row}", !empty($fitting['D1']) ? $fitting['D1'] : '');
            $sheet->setCellValue("D{$row}", !empty($fitting['D2']) ? $fitting['D2'] : '');
            $sheet->setCellValue("E{$row}", !empty($fitting['D3']) ? $fitting['D3'] : '');
            $sheet->setCellValue("F{$row}", !empty($fitting['R_DRAT']) ? $fitting['R_DRAT'] : '');
            $sheet->setCellValue("G{$row}", $fitting['created_at']);
            $sheet->setCellValue("H{$row}", $fitting['updated_at']);
            $sheet->setCellValue("I{$row}", $fitting['editor']);
            $row++;
        }

        // Auto-filter and auto-size columns
        $sheet->setAutoFilter('A1:I1');
        foreach (range('A', 'I') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $filename = 'Data Fitting ' . date('Y-m-d H:i:s') . '.xlsx';
        $this->outputExcelFile($spreadsheet, $filename);
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
     * Process Excel file upload and import data.
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
        $spreadsheet = IOFactory::load($inputFileName);
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();

        $batchData = [];
        $errors = [];
        $processedCount = 0;

        for ($row = 2; $row <= $highestRow; $row++) {
            $type = trim($worksheet->getCell('A' . $row)->getValue());
            $D1 = $worksheet->getCell('B' . $row)->getValue();
            $D2 = $worksheet->getCell('C' . $row)->getValue();
            $D3 = $worksheet->getCell('D' . $row)->getValue();
            $R_DRAT = trim($worksheet->getCell('E' . $row)->getValue());

            // Skip empty rows
            if (empty($type) && empty($D1) && empty($D2) && empty($D3) && empty($R_DRAT)) {
                continue;
            }

            // Validate required type field
            if (empty($type)) {
                $errors[] = "Baris {$row}: Type harus diisi";
                continue;
            }

            // Check if at least one dimension field is provided
            if (empty($D1) && empty($D2) && empty($D3) && empty($R_DRAT)) {
                $errors[] = "Baris {$row}: Minimal satu field dimensi (D1, D2, D3, atau R_DRAT) harus diisi";
                continue;
            }

            // Validate numeric fields if provided
            $validatedD1 = null;
            $validatedD2 = null;
            $validatedD3 = null;

            if (!empty($D1)) {
                if (!is_numeric($D1) || (float)$D1 <= 0) {
                    $errors[] = "Baris {$row}: D1 harus berupa angka positif";
                    continue;
                }
                $validatedD1 = (float)$D1;
            }

            if (!empty($D2)) {
                if (!is_numeric($D2) || (float)$D2 <= 0) {
                    $errors[] = "Baris {$row}: D2 harus berupa angka positif";
                    continue;
                }
                $validatedD2 = (float)$D2;
            }

            if (!empty($D3)) {
                if (!is_numeric($D3) || (float)$D3 <= 0) {
                    $errors[] = "Baris {$row}: D3 harus berupa angka positif";
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
                $errors[] = "Baris {$row}: Fitting dengan ID '{$fittingId}' sudah ada";
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
        }

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
     * Output Excel file for download.
     *
     * @param Spreadsheet $spreadsheet
     * @param string $filename
     * @return void
     */
    private function outputExcelFile(Spreadsheet $spreadsheet, string $filename): void
    {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
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
