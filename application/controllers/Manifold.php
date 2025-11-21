<?php
defined('BASEPATH') or exit('No direct script access allowed');

require 'vendor/autoload.php';

use TCPDF;

/**
 * Manifold controller for air-system.
 *
 * Manage manifolds: listing, search/filter/sort, CRUD operations, and CSV import/export with PDF download following ASRS conventions.
 *
 * @package AirSystem
 * @subpackage Controllers
 * @category Manifold
 * @author Apparel One Indonesia
 * @version 1.0.0
 * @property CI_Session $session
 * @property CI_Input $input
 * @property CI_URI $uri
 * @property CI_Form_validation $form_validation
 * @property CI_Pagination $pagination
 * @property Manifold_model $Manifold_model
 */
class Manifold extends CI_Controller
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
            'block' => [
                'field' => 'block',
                'label' => 'Block',
                'rules' => 'required|numeric|greater_than[0]|callback_check_manifold_combination',
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
    public function __construct()
    {
        parent::__construct();

        // Check user authentication using common helper
        check_user_authentication();

        $this->load->model('Manifold_model');
        $this->load->library(['form_validation', 'pagination']);

        // Reset session data when switching controllers
        reset_controller_session('manifold');
    }

    /**
     * List manifolds with pagination and optional search. Renders via render_view().
     *
     * @return void
     */
    public function index(): void
    {
        // Handle CSV uploads using the ASRS-style helper
        $this->handleFileUpload();

        $this->handleSessionState();

        $sessionData = [
            'search' => $this->session->userdata('keyword'),
            'filter' => $this->session->userdata('filter'),
            'sort'   => $this->session->userdata('sort'),
        ];

        // Provide distinct values for filter dropdowns (respect current search/filter state)
        $blockOptions = $this->Manifold_model->getManifoldFilter('block', $sessionData['search'], $sessionData['filter']);

        $totalRows = $this->Manifold_model->countManifold($sessionData['search'], $sessionData['filter']);

        // Setup pagination using common helper
        $config = setup_pagination(site_url('manifold/index'), $totalRows, self::CONFIG['pagination']['items_per_page']);
        $this->pagination->initialize($config);

        $startData = (int) ($this->uri->segment(3) ?: 0);

        $manifolds = $this->Manifold_model->getManifold(
            self::CONFIG['pagination']['items_per_page'],
            $startData,
            $sessionData['search'],
            $sessionData['filter'],
            $sessionData['sort']
        );

        $data = [
            'title'          => 'Data Manifold',
            'manifolds'      => $manifolds,
            'pagination'     => ['links' => $this->pagination->create_links()],
            'total_rows'     => $totalRows,
            'searchKeyword'  => $sessionData['search'],
            'sortKeyword'    => ($sessionData['sort'] && strpos($sessionData['sort'], '-') !== false) ? explode('-', $sessionData['sort'], 2) : ['', ''],
            'filterKeyword'  => $sessionData['filter'],
            'hasFilters'     => (!empty($sessionData['search']) || !empty($sessionData['filter']) || !empty($sessionData['sort'])),
            'block_options'  => $blockOptions,
        ];

        render_view('manifold/index', $data);
    }

    /**
     * Display the add manifold form and handle form submission.
     *
     * @return void
     */
    public function add(): void
    {
        if ($this->input->method() === 'post') {
            $this->setValidationRules();

            if ($this->form_validation->run()) {
                $this->Manifold_model->addManifold();
                set_message(['success', 'Data manifold berhasil ditambahkan!']);
                redirect('manifold');
            }
        }

        $data = [
            'title' => 'Tambah Manifold',
        ];
        render_view('manifold/add', $data);
    }

    /**
     * Display the edit manifold form and handle form submission.
     *
     * @param string $manifoldId The manifold ID to edit.
     *
     * @return void
     */
    public function edit(string $manifoldId): void
    {
        $manifold = $this->Manifold_model->getById($manifoldId);

        if (!$manifold) {
            show_404();
        }

        if ($this->input->method() === 'post') {
            $this->setValidationRules(true);

            if ($this->form_validation->run()) {
                $this->Manifold_model->editManifold($manifoldId);
                set_message(['success', 'Data manifold berhasil diperbarui!']);
                redirect('manifold');
            }
        }

        $data['manifold'] = $manifold;
        $data['title'] = 'Edit Manifold';
        render_view('manifold/edit', $data);
    }

    /**
     * Handle manifold deletion.
     *
     * @param string $manifoldId The manifold ID to delete.
     *
     * @return void
     */
    public function delete(string $manifoldId): void
    {
        $manifold = $this->Manifold_model->getById($manifoldId);

        if (!$manifold) {
            set_message(['danger', 'Data manifold tidak ditemukan!']);
        } else {
            $this->Manifold_model->deleteManifold($manifoldId);
            set_message(['success', 'Data manifold berhasil dihapus!']);
        }

        redirect('manifold');
    }

    /**
     * Downloads manifold data as CSV file.
     *
     * @return void
     */
    public function download(): void
    {
        try {
            $manifolds = $this->Manifold_model->getAllManifolds();
            $this->generateCSVFile($manifolds);
        } catch (Exception $e) {
            log_message('error', 'CSV download error: ' . $e->getMessage());
            set_message(['danger', 'Error downloading file: ' . $e->getMessage()]);
            redirect('manifold');
        }
    }

    /**
     * Downloads manifold data as PDF file.
     *
     * @return void
     */
    public function downloadPDF(): void
    {
        try {
            $manifolds = $this->Manifold_model->getAllManifolds();
            $this->generatePDFFile($manifolds);
        } catch (Exception $e) {
            log_message('error', 'PDF download error: ' . $e->getMessage());
            set_message(['danger', 'Error downloading file: ' . $e->getMessage()]);
            redirect('manifold');
        }
    }

    /**
     * Downloads CSV template for manifold upload.
     *
     * @return void
     */
    public function template(): void
    {
        try {
            $filename = 'Template Data Manifold.csv';

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Write header row
            fputcsv($output, ['Block']);

            fclose($output);
            exit;
        } catch (Exception $e) {
            log_message('error', 'Template download error: ' . $e->getMessage());
            show_error('Error generating template file: ' . $e->getMessage());
        }
    }

    /**
     * Public wrapper for upload POSTs — delegates to handleFileUpload().
     * Prevents 404 for forms that POST to /manifold/upload while keeping logic centralized.
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
     * @param array $manifolds Array of manifold data
     * @return void
     */
    private function generateCSVFile(array $manifolds): void
    {
        $filename = 'Data Manifold ' . date('Y-m-d H-i-s') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Add BOM for UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Write header row
        fputcsv($output, ['Manifold ID', 'Block', 'Created At', 'Updated At', 'Editor']);

        // Write data rows
        foreach ($manifolds as $manifold) {
            fputcsv($output, [
                $manifold['manifold_id'],
                $manifold['block'],
                $manifold['created_at'],
                $manifold['updated_at'],
                $manifold['editor']
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Generates PDF file for download.
     *
     * @param array $manifolds Array of manifold data
     * @return void
     */
    private function generatePDFFile(array $manifolds): void
    {
        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('Air System');
        $pdf->SetAuthor('Air System');
        $pdf->SetTitle('Data Manifold');

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
        $pdf->Cell(0, 10, 'Data Manifold', 0, 1, 'C');
        $pdf->Ln(5);

        // Table header
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(66, 139, 202);
        $pdf->SetTextColor(255, 255, 255);

        $pdf->Cell(70, 7, 'Manifold ID', 1, 0, 'C', 1);
        $pdf->Cell(30, 7, 'Block', 1, 0, 'C', 1);
        $pdf->Cell(50, 7, 'Created At', 1, 0, 'C', 1);
        $pdf->Cell(50, 7, 'Updated At', 1, 0, 'C', 1);
        $pdf->Cell(40, 7, 'Editor', 1, 1, 'C', 1);

        // Table data
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        $fill = false;

        foreach ($manifolds as $manifold) {
            $pdf->SetFillColor(245, 245, 245);
            $pdf->Cell(70, 6, $manifold['manifold_id'], 1, 0, 'L', $fill);
            $pdf->Cell(30, 6, $manifold['block'], 1, 0, 'C', $fill);
            $pdf->Cell(50, 6, $manifold['created_at'], 1, 0, 'C', $fill);
            $pdf->Cell(50, 6, $manifold['updated_at'], 1, 0, 'C', $fill);
            $pdf->Cell(40, 6, $manifold['editor'], 1, 1, 'C', $fill);
            $fill = !$fill;
        }

        $filename = 'Data Manifold ' . date('Y-m-d H-i-s') . '.pdf';
        $pdf->Output($filename, 'D');
        exit;
    }

    /**
     * Handle file upload posted to index (ASRS-style).
     * This mirrors the upload handling in ASRS User controller but for manifold data.
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
                if (count($row) < 1 || !$row[0]) {
                    continue; // skip empty row
                }

                if (empty($row[0])) {
                    $skippedData[] = "Block tidak boleh kosong";
                    continue;
                }

                $block = $row[0] ?? '';

                if (!is_numeric($block) || $block <= 0) {
                    $skippedData[] = "Block harus berupa angka positif: {$block}";
                    continue;
                }

                $manifoldId = 'mnf-' . $block;

                if ($this->Manifold_model->isManifoldIdExists($manifoldId)) {
                    $skippedData[] = "Manifold dengan Block: {$block} sudah terdaftar";
                    continue;
                }

                $insertData[] = [
                    'manifold_id' => $manifoldId,
                    'block'       => (int)$block,
                    'created_at'  => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
                    'updated_at'  => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
                    'editor'      => $this->session->userdata('user_data')['nik']
                ];
            }

            $insertCount  = count($insertData);
            $skippedCount = count($skippedData);

            if ($insertCount > 0) {
                $this->Manifold_model->insertBatch($insertData);
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
            redirect('manifold');
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
        handle_session_state('manifold', []);
    }

    /**
     * Sets validation rules for manifold forms.
     *
     * @param bool $isEdit Whether this is for edit form
     * @return void
     */
    private function setValidationRules(bool $isEdit = false): void
    {
        foreach (self::CONFIG['validation'] as $field => $config) {
            $this->form_validation->set_rules($config['field'], $config['label'], $config['rules'], $config['errors']);
        }
    }

    /**
     * Custom validation callback to check if manifold combination already exists.
     *
     * @param string $block The block value
     * @return bool
     */
    public function check_manifold_combination(string $block): bool
    {
        if (!empty($block)) {
            $manifoldId = 'mnf-' . $block;

            if ($this->Manifold_model->isManifoldIdExists($manifoldId)) {
                $this->form_validation->set_message('check_manifold_combination', 'Manifold dengan Block: {field} sudah terdaftar');
                return false;
            }
        }

        return true;
    }
}
