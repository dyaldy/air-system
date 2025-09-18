<?php
defined('BASEPATH') or exit('No direct script access allowed');

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * User controller for air-system.
 *
 * Manage users: listing, search/filter/sort, CRUD operations, and Excel import/export following ASRS conventions.
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

        $this->load->model('User_model');
        $this->load->library(['form_validation', 'pagination']);

        // Reset session if controller changed
        if ($this->session->userdata('controller') !== 'user') {
            $this->session->set_userdata('controller', 'user');
            $this->session->unset_userdata(['keyword', 'sort', 'filter']);
        }
    }

    /**
     * List users with pagination and optional search. Renders via render_view().
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

        $config = [
            'base_url'   => site_url('user/index'),
            'total_rows' => $this->User_model->countUser($sessionData['search'], $sessionData['filter']),
            'per_page'   => self::CONFIG['pagination']['items_per_page'],
        ];
        $this->pagination->initialize($config);

        $startData = (int) ($this->uri->segment(3) ? $this->uri->segment(3) : 0);
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
            'pagination'    => $this->pagination->create_links(),
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
        $this->form_validation->set_rules(
            self::CONFIG['validation']['nik']['field'],
            self::CONFIG['validation']['nik']['label'],
            self::CONFIG['validation']['nik']['rules'],
            self::CONFIG['validation']['nik']['errors']
        );
        $this->form_validation->set_rules(
            self::CONFIG['validation']['name']['field'],
            self::CONFIG['validation']['name']['label'],
            self::CONFIG['validation']['name']['rules'],
            self::CONFIG['validation']['name']['errors']
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

        $this->form_validation->set_rules(
            self::CONFIG['validation']['name']['field'],
            self::CONFIG['validation']['name']['label'],
            self::CONFIG['validation']['name']['rules'],
            self::CONFIG['validation']['name']['errors']
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
     * Delete user by NIK.
     *
     * @param int $nik
     * @return void
     */
    public function delete($nik = null): void
    {
        if ($nik === null) {
            show_404();
            return;
        }

        $this->User_model->deleteUser((int) $nik);
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
        if ($this->input->post('find')) {
            $this->session->set_userdata('keyword', $this->input->post('keyword', true));
        }

        if ($this->input->post('filter-submit')) {
            $this->session->set_userdata('filter', []);
        }

        if ($this->input->post('sort-send')) {
            $this->session->set_userdata('sort', $this->input->post('sort-send', true));
        }

        if ($this->input->post('reset')) {
            $this->session->unset_userdata(['keyword', 'sort', 'filter']);
        }
    }

    /**
     * Downloads user data as Excel file.
     *
     * @return void
     */
    public function download(): void
    {
        $users = $this->User_model->getUser(1000, 0); // Get all users

        try {
            $this->generateExcelFile($users);
        } catch (Exception $e) {
            log_message('error', 'Excel download error: ' . $e->getMessage());
            set_message(['danger', 'Error creating file: ' . $e->getMessage()]);
            redirect('user');
        }
    }

    /**
     * Downloads Excel template for user upload.
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
     * Handles Excel file upload and user import.
     *
     * @return void
     */
    public function upload(): void
    {
        // Accept file-only multipart POST submissions; don't rely on $this->input->post()
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            redirect('user');
            return;
        }

        // Basic server-side guard if no file provided
        if (empty($_FILES['file']) || (int)($_FILES['file']['error'] ?? UPLOAD_ERR_OK) === UPLOAD_ERR_NO_FILE) {
            set_message(['danger', 'Tidak ada file yang diunggah. Pilih file Excel terlebih dahulu.']);
            redirect('user');
            return;
        }

        $config['upload_path'] = './uploads/';
        $config['allowed_types'] = 'xlsx|xls';
        $config['max_size'] = 2048; // 2MB
        $config['file_name'] = 'user_upload_' . time();

        // Create upload directory if it doesn't exist
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, true);
        }

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('file')) {
            set_message(['danger', 'Upload error: ' . $this->upload->display_errors()]);
            redirect('user');
            return;
        }

        $uploadData = $this->upload->data();
        $filePath = $uploadData['full_path'];

        try {
            $result = $this->processExcelFile($filePath);
            unlink($filePath); // Remove uploaded file

            if ($result['success']) {
                set_message(['success', "Data berhasil diimport. {$result['inserted']} pengguna ditambahkan."]);
            } elseif ($result['inserted'] > 0) {
                $errorDetails = implode('<br>', $result['errorMessages']);
                set_message([
                    'warning',
                    "Import selesai dengan peringatan. {$result['inserted']} pengguna ditambahkan, {$result['errors']} error.<br><br>Detail error:<br>{$errorDetails}"
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

        redirect('user');
    }

    ## Private Helper Methods

    /**
     * Generates Excel file for download.
     *
     * @param array $users Array of user data
     * @return void
     */
    private function generateExcelFile(array $users): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'NIK');
        $sheet->setCellValue('B1', 'Nama');
        $sheet->setCellValue('C1', 'Dibuat');
        $sheet->setCellValue('D1', 'Diperbarui');

        // Style headers
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E9ECEF']
            ]
        ];
        $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);

        // Add data
        $row = 2;
        foreach ($users as $user) {
            $sheet->setCellValue("A{$row}", $user['nik']);
            $sheet->setCellValue("B{$row}", $user['name']);
            $sheet->setCellValue("C{$row}", date('d M Y H:i:s', strtotime($user['created_at'])));
            $sheet->setCellValue("D{$row}", date('d M Y H:i:s', strtotime($user['updated_at'])));
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'D') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Output file
        $filename = 'data_pengguna_air_system_' . date('Y-m-d_H-i-s') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Generates Excel template for upload.
     *
     * @return void
     */
    private function generateTemplateFile(): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'NIK');
        $sheet->setCellValue('B1', 'Nama');

        // Add example data
        $sheet->setCellValue('A2', '123456789');
        $sheet->setCellValue('B2', 'John Doe');

        // Style headers
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E9ECEF']
            ]
        ];
        $sheet->getStyle('A1:B1')->applyFromArray($headerStyle);

        // Auto-size columns
        foreach (range('A', 'B') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Output file
        $filename = 'template_pengguna_air_system.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Processes uploaded Excel file and imports users.
     *
     * @param string $filePath Path to uploaded Excel file
     * @return array Result with success status and counts
     */
    private function processExcelFile(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $inserted = 0;
        $errorMessages = [];
        $userData = [];

        // Skip header row, start from row 2
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $rowNumber = $i + 1; // Add 1 to account for header row

            // Skip empty rows
            if (empty($row[0]) && empty($row[1])) {
                continue;
            }

            $nik = $row[0] ?? '';
            $name = $row[1] ?? '';

            // Validate required fields
            if (empty($nik)) {
                $errorMessages[] = "Baris {$rowNumber}: NIK tidak boleh kosong";
                continue;
            }

            if (empty($name)) {
                $errorMessages[] = "Baris {$rowNumber}: Nama tidak boleh kosong";
                continue;
            }

            // Validate NIK format
            if (!is_numeric($nik)) {
                $errorMessages[] = "Baris {$rowNumber}: NIK harus berupa angka: {$nik}";
                continue;
            }

            if (strlen($nik) !== 9) {
                $errorMessages[] = "Baris {$rowNumber}: NIK harus berjumlah 9 digit: {$nik}";
                continue;
            }

            // Check if NIK already exists
            if ($this->User_model->isNikExists($nik)) {
                $errorMessages[] = "Baris {$rowNumber}: NIK sudah terdaftar: {$nik}";
                continue;
            }

            $userData[] = [
                'nik' => $nik,
                'name' => ucwords(strtolower(trim($name))),
                'created_at' => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s', now('Asia/Jakarta')),
                'editor' => $this->session->userdata('user_data')['nik']
            ];
            $inserted++;
        }

        // Batch insert
        if (!empty($userData)) {
            $this->User_model->insertBatch($userData);
        }

        return [
            'success' => count($errorMessages) === 0,
            'inserted' => $inserted,
            'errors' => count($errorMessages),
            'errorMessages' => $errorMessages
        ];
    }
}
