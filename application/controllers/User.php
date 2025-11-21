<?php
defined('BASEPATH') or exit('No direct script access allowed');

require 'vendor/autoload.php';

use TCPDF;

/**
 * User controller for air-system.
 *
 * Manage users: listing, search/filter/sort, CRUD operations, and CSV import/export with PDF download.
 * This controller handles all user management operations including data validation,
 * pagination, filtering, and CSV import/export with PDF functionality.
 *
 * @package AirSystem
 * @subpackage Controllers
 * @category User
 * @author Apparel One Indonesia
 * @version 1.0.0
 * @property CI_Session $session
 * @property CI_Input $input
 * @property CI_URI $uri
 * @property CI_Form_validation $form_validation
 * @property CI_Pagination $pagination
 * @property User_model $User_model
 */
class User extends CI_Controller
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
            'nik' => [
                'field' => 'nik',
                'label' => 'NIK',
                'rules' => 'required|numeric|exact_length[9]|is_unique[as_user.nik]',
                'errors' => [
                    'required'     => '%s harus diisi',
                    'numeric'      => '%s hanya menggunakan angka',
                    'exact_length' => '%s berjumlah 9 digit',
                    'is_unique'    => '%s tidak boleh sama',
                ],
            ],
            'name' => [
                'field' => 'name',
                'label' => 'Nama',
                'rules' => 'trim|required',
                'errors' => [
                    'required' => '%s harus diisi',
                ],
            ],
        ],
    ];

    /**
     * Constructor for User controller.
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

        $this->load->model('User_model');
        $this->load->library(['form_validation', 'pagination']);

        // Reset session data when switching controllers
        reset_controller_session('user');
    }

    /**
     * List users with pagination and optional search. Renders via render_view().
     *
     * @return void
     */
    public function index(): void
    {
        // Handle possible CSV file upload like ASRS implementation
        $this->handleFileUpload();
        $this->handleSessionState();

        $sessionData = [
            'search' => $this->session->userdata('keyword'),
            'filter' => $this->session->userdata('filter'),
            'sort'   => $this->session->userdata('sort'),
        ];

        // Setup pagination using common helper
        $totalRows = $this->User_model->countUser($sessionData['search'], $sessionData['filter']);
        $config = setup_pagination(site_url("user/index"), $totalRows, self::CONFIG['pagination']['items_per_page']);
        $this->pagination->initialize($config);

        $startData = (int) ($this->uri->segment(3) ?: 0);
        $users = $this->User_model->getUser(
            self::CONFIG['pagination']['items_per_page'],
            $startData,
            $sessionData['search'],
            $sessionData['filter'],
            $sessionData['sort']
        );

        // Prepare view data
        $userCount = count($users);
        $data = [
            'title'         => 'Data Pengguna',
            'users'         => $users,
            'display'       => ($startData + 1) . ' - ' . ($startData + $userCount) . ' dari ' . $config['total_rows'],
            'sortKeyword'   => ($sessionData['sort'] && strpos($sessionData['sort'], '-') !== false) ? explode('-', $sessionData['sort'], 2) : ['', ''],
            'searchKeyword' => $sessionData['search'],
            'filterKeyword' => $sessionData['filter'],
            'hasFilters'    => (!empty($sessionData['search']) || !empty($sessionData['filter']) || !empty($sessionData['sort'])),
        ];

        render_view('user/index', $data);
    }

    /**
     * Show add form and process create.
     *
     * @return void
     */
    public function add(): void
    {
        // Set validation rules using common helper
        $this->form_validation->set_rules(
            get_rules(self::CONFIG['validation'], ['nik', 'name'])
        );

        if ($this->form_validation->run() === false) {
            $data = ['title' => 'Tambah Pengguna Air System', 'user' => null];
            render_view('user/add', $data);
            return;
        }

        $this->User_model->addUser();
        set_message(['success', 'Pengguna berhasil ditambahkan']);

        redirect('user');
    }

    /**
     * Show edit form and process update.
     *
     * @param int $nik
     * @return void
     */
    public function edit($nik = null): void
    {
        if ($nik === null) {
            show_404();
            return;
        }

        $user = $this->User_model->getByNik((int) $nik);
        if (!$user) {
            set_message(['danger', 'Pengguna tidak ditemukan']);
            redirect('user');
            return;
        }

        // Set validation rules using common helper
        $this->form_validation->set_rules(
            get_rules(self::CONFIG['validation'], ['name'])
        );

        if ($this->form_validation->run() === false) {
            $data = ['title' => 'Edit Pengguna Air System', 'user' => $user];
            render_view('user/edit', $data);
            return;
        }

        $this->User_model->editUser((int) $nik);
        set_message(['success', 'Pengguna berhasil diperbarui']);
        redirect('user');
    }

    /**
     * Delete a user by NIK.
     *
     * @param string $nik URL-encoded + base64-encoded NIK.
     * @return void
     */
    public function delete(string $nik): void
    {
        $decodedNik = base64_decode(urldecode($nik), true);

        if ($decodedNik === false) {
            set_message(['danger', 'NIK tidak valid']);
            redirect('user');
        }

        $user = $this->User_model->getByNik($decodedNik);
        if (!$user) {
            set_message(['danger', 'Pengguna tidak ditemukan']);
            redirect('user');
        }

        $this->User_model->deleteUser($decodedNik);
        set_message(['success', 'Pengguna berhasil dihapus']);
        redirect('user');
    }

    ## Private Helper Methods

    /**
     * Handles updating session data for search, filter, and sort keywords.
     *
     * @return void
     */
    private function handleSessionState(): void
    {
        // Use the common helper for session state management
        handle_session_state('user', []);
    }

    /**
     * Downloads user data as CSV file.
     *
     * @return void
     */
    public function download(): void
    {
        $users = $this->User_model->getUser(1000, 0); // Get all users

        try {
            $this->generateCSVFile($users);
        } catch (Exception $e) {
            log_message('error', 'CSV download error: ' . $e->getMessage());
            set_message(['danger', 'Error creating file: ' . $e->getMessage()]);
            redirect('user');
        }
    }

    /**
     * Downloads user data as PDF file.
     *
     * @return void
     */
    public function downloadPDF(): void
    {
        $users = $this->User_model->getUser(1000, 0); // Get all users

        try {
            $this->generatePDFFile($users);
        } catch (Exception $e) {
            log_message('error', 'PDF download error: ' . $e->getMessage());
            set_message(['danger', 'Error creating file: ' . $e->getMessage()]);
            redirect('user');
        }
    }

    /**
     * Downloads CSV template for user upload.
     *
     * @return void
     */
    public function template(): void
    {
        try {
            $this->generateTemplateFile();
        } catch (Exception $e) {
            log_message('error', 'Template download error: ' . $e->getMessage());
            set_message(['danger', 'Error creating template: ' . $e->getMessage()]);
            redirect('user');
        }
    }

    /**
     * Handles CSV file upload and user import.
     *
     * @return void
     */
    // Upload handling is performed in index() via handleFileUpload() to match ASRS (no separate public upload endpoint)
    /**
     * Public wrapper for upload POSTs — delegates to handleFileUpload().
     * Keeps a single implementation while preventing 404 when a form posts to /user/upload.
     *
     * @return void
     */
    public function upload(): void
    {
        // Delegate to the central handler which expects a file in \$_FILES['file']
        $this->handleFileUpload();
    }

    ## Private Helper Methods

    /**
     * Generates CSV file for download.
     *
     * @param array $users Array of user data
     * @return void
     */
    private function generateCSVFile(array $users): void
    {
        $filename = 'data_pengguna_air_system_' . date('Y-m-d_H-i-s') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Add BOM for UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Write header row
        fputcsv($output, ['NIK', 'Nama', 'Dibuat', 'Diperbarui']);

        // Write data rows
        foreach ($users as $user) {
            fputcsv($output, [
                $user['nik'],
                $user['name'],
                date('d M Y H:i:s', strtotime($user['created_at'])),
                date('d M Y H:i:s', strtotime($user['updated_at']))
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Generates PDF file for download.
     *
     * @param array $users Array of user data
     * @return void
     */
    private function generatePDFFile(array $users): void
    {
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('Air System');
        $pdf->SetAuthor('Air System');
        $pdf->SetTitle('Data Pengguna');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);

        // Add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'Data Pengguna Air System', 0, 1, 'C');
        $pdf->Ln(5);

        // Table header
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(66, 139, 202);
        $pdf->SetTextColor(255, 255, 255);

        $pdf->Cell(40, 7, 'NIK', 1, 0, 'C', 1);
        $pdf->Cell(60, 7, 'Nama', 1, 0, 'C', 1);
        $pdf->Cell(40, 7, 'Dibuat', 1, 0, 'C', 1);
        $pdf->Cell(40, 7, 'Diperbarui', 1, 1, 'C', 1);

        // Table data
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        $fill = false;

        foreach ($users as $user) {
            $pdf->SetFillColor(245, 245, 245);
            $pdf->Cell(40, 6, $user['nik'], 1, 0, 'C', $fill);
            $pdf->Cell(60, 6, $user['name'], 1, 0, 'L', $fill);
            $pdf->Cell(40, 6, date('d M Y H:i:s', strtotime($user['created_at'])), 1, 0, 'C', $fill);
            $pdf->Cell(40, 6, date('d M Y H:i:s', strtotime($user['updated_at'])), 1, 1, 'C', $fill);
            $fill = !$fill;
        }

        $filename = 'data_pengguna_air_system_' . date('Y-m-d_H-i-s') . '.pdf';
        $pdf->Output($filename, 'D');
        exit;
    }

    /**
     * Generates CSV template for upload.
     *
     * @return void
     */
    private function generateTemplateFile(): void
    {
        $filename = 'template_pengguna_air_system.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Add BOM for UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Write header row
        fputcsv($output, ['NIK', 'Nama']);

        // Add example data
        fputcsv($output, ['123456789', 'John Doe']);

        fclose($output);
        exit;
    }

    /**
     * Process uploaded CSV file and insert valid user records (mirrors ASRS implementation).
     *
     * This method will run when a file is POSTed to the index route.
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

            foreach ($data as $row) {
                // Validate required fields
                if (count($row) < 2 || empty($row[0])) {
                    $skippedData[] = "NIK tidak boleh kosong";
                    continue;
                }

                if (empty($row[1])) {
                    $skippedData[] = "Nama tidak boleh kosong";
                    continue;
                }

                $rowNik = trim($row[0] ?? '');
                $rowName = ucwords(strtolower(trim($row[1] ?? '')));

                // Validate NIK format
                if (!ctype_digit($rowNik)) {
                    $skippedData[] = "NIK harus angka: {$rowNik}";
                    continue;
                }
                if (strlen($rowNik) !== 9) {
                    $skippedData[] = "NIK harus berjumlah 9 digit: {$rowNik}";
                    continue;
                }

                // Check if NIK already exists
                if ($this->User_model->isNikExists($rowNik)) {
                    $skippedData[] = "NIK sudah terdaftar: {$rowNik}";
                    continue;
                }

                $insertData[] = [
                    'nik'        => $rowNik,
                    'name'       => $rowName,
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
                    'updated_at' => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
                    'editor'     => $this->session->userdata('user_data')['nik'],
                ];
            }

            $insertCount  = count($insertData);
            $skippedCount = count($skippedData);

            if ($insertCount > 0 && $skippedCount > 0) {
                $this->User_model->insertBatch($insertData);
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
                $this->User_model->insertBatch($insertData);
                set_message(['success', "Data berhasil ditambahkan! ({$insertCount} data baru)"]);
            } else {
                set_message(['danger', 'Data kosong!']);
            }

            $this->session->unset_userdata(['keyword', 'sort', 'filter']);
            redirect('user');
        } catch (Exception $e) {
            log_message('error', 'File upload error: ' . $e->getMessage());
            set_message(['danger', 'Terjadi kesalahan dalam membaca file CSV.']);
        }
    }
}
