<?php
defined('BASEPATH') or exit('No direct script access allowed');

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Storage extends CI_Controller
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
            // Add validation rules here if needed
        ]
    ];

    public function __construct()
    {
        parent::__construct();

        // Check if user is logged in - same as User controller
        if (!$this->session->userdata('user_data')) {
            redirect(base_url());
        }

        // Load required models and libraries
        $this->load->model(['Storage_model', 'Report_model', 'Pneumatic_model', 'Pneumatic_type_model']);
        $this->load->library(['form_validation', 'session', 'pagination']);
        $this->load->helper(['url', 'common']);

        // Set session controller to storage
        if ($this->session->userdata('controller') !== 'storage') {
            $this->session->set_userdata('controller', 'storage');
            $this->session->unset_userdata(['keyword', 'sort', 'filter']);
        }
    }

    /**
     * Storage dashboard - overview of all storage locations
     */
    public function index()
    {
        // Handle session state (search, filter, sort, reset)
        handle_session_state('storage');

        $data['title'] = 'Storage Overview';
        $data['user_data'] = $this->session->userdata('user_data');

        try {
            // Get storage overview data using available methods
            $data['storage_overview'] = $this->Storage_model->get_storage_overview();
            $data['recent_transactions'] = $this->Report_model->get_all_transactions(10);
            $data['locations'] = $this->Storage_model->get_all_locations();

            // Get search and filter data from session
            $data['keyword'] = $this->session->userdata('keyword') ?: '';
            $data['sort'] = $this->session->userdata('sort') ?: 'item_code';

            render_view('storage/index', $data);
        } catch (Exception $e) {
            // Fallback if models have issues
            $data['error'] = 'Storage system temporarily unavailable: ' . $e->getMessage();
            render_view('storage/index', $data);
        }
    }

    /**
     * Test method to verify controller is working
     */
    public function test()
    {
        echo "Storage controller is working!";
    }

    /**
     * View storage by location
     */
    public function location($location_id = null)
    {
        if (!$location_id) {
            redirect('storage');
        }

        $data['title'] = 'Storage Location: ' . $location_id;
        $data['location_id'] = $location_id;
        $data['storage_items'] = $this->Storage_model->get_storage_by_location($location_id);
        $data['location_transactions'] = $this->Report_model->get_transactions_by_location($location_id, 20);

        $this->load->view('templates/header', $data);
        $this->load->view('storage/location', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Store items form
     */
    public function store()
    {
        $data['title'] = 'Store Items';
        $data['pneumatic_items'] = $this->Pneumatic_model->getAllPneumatics();
        $data['locations'] = $this->Storage_model->get_all_locations();

        // Set validation rules
        $this->form_validation->set_rules('location_id', 'Location ID', 'required|max_length[3]');
        $this->form_validation->set_rules('category', 'Category', 'required|max_length[15]');
        $this->form_validation->set_rules('type_id', 'Type ID', 'required|max_length[30]');
        $this->form_validation->set_rules('quantity', 'Quantity', 'required|integer|greater_than[0]');
        $this->form_validation->set_rules('note', 'Note', 'max_length[255]');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('templates/header', $data);
            $this->load->view('storage/store_form', $data);
            $this->load->view('templates/footer');
        } else {
            $this->process_store();
        }
    }

    /**
     * Process storing items
     */
    private function process_store()
    {
        $location_id = $this->input->post('location_id');
        $category = $this->input->post('category');
        $type_id = $this->input->post('type_id');
        $quantity = (int)$this->input->post('quantity');
        $note = $this->input->post('note');
        $editor_nik = $this->session->userdata('user_data')['nik'];

        // Validate if pneumatic exists (for pneumatic category)
        if ($category === 'pneumatic') {
            $pneumatic = $this->Pneumatic_model->getById($type_id);
            if (!$pneumatic) {
                $this->session->set_flashdata('error', 'Pneumatic item not found!');
                redirect('storage/store');
                return;
            }
        }

        // Store the items
        $store_result = $this->Storage_model->store_items($location_id, $category, $type_id, $quantity, $editor_nik);

        if ($store_result) {
            // Log the transaction
            $this->Report_model->log_store_transaction($location_id, $category, $type_id, $editor_nik, $note, $quantity);

            $this->session->set_flashdata('success', 'Items stored successfully!');
            redirect('storage/location/' . $location_id);
        } else {
            $this->session->set_flashdata('error', 'Failed to store items!');
            redirect('storage/store');
        }
    }

    /**
     * Take items form
     */
    public function take()
    {
        $data['title'] = 'Take Items';
        $data['storage_items'] = $this->Storage_model->get_all_storage();
        $data['locations'] = $this->Storage_model->get_all_locations();

        // Set validation rules
        $this->form_validation->set_rules('location_id', 'Location ID', 'required|max_length[3]');
        $this->form_validation->set_rules('category', 'Category', 'required|max_length[15]');
        $this->form_validation->set_rules('type_id', 'Type ID', 'required|max_length[30]');
        $this->form_validation->set_rules('quantity', 'Quantity', 'required|integer|greater_than[0]');
        $this->form_validation->set_rules('note', 'Note', 'max_length[255]');

        if ($this->form_validation->run() === FALSE) {
            $this->load->view('templates/header', $data);
            $this->load->view('storage/take_form', $data);
            $this->load->view('templates/footer');
        } else {
            $this->process_take();
        }
    }

    /**
     * Process taking items
     */
    private function process_take()
    {
        $location_id = $this->input->post('location_id');
        $category = $this->input->post('category');
        $type_id = $this->input->post('type_id');
        $quantity = (int)$this->input->post('quantity');
        $note = $this->input->post('note');
        $editor_nik = $this->session->userdata('user_data')['nik'];

        // Take the items
        $take_result = $this->Storage_model->take_items($location_id, $category, $type_id, $quantity, $editor_nik);

        if ($take_result['success']) {
            // Log the transaction
            $this->Report_model->log_take_transaction($location_id, $category, $type_id, $editor_nik, $note, $quantity);

            $this->session->set_flashdata('success', $take_result['message']);
            redirect('storage/location/' . $location_id);
        } else {
            $this->session->set_flashdata('error', $take_result['message']);
            redirect('storage/take');
        }
    }

    /**
     * Search storage items
     */
    public function search()
    {
        $search_term = $this->input->get('q');
        $location_id = $this->input->get('location');
        $category = $this->input->get('category');

        $data['title'] = 'Search Storage';
        $data['search_term'] = $search_term;
        $data['selected_location'] = $location_id;
        $data['selected_category'] = $category;
        $data['locations'] = $this->Storage_model->get_all_locations();

        if ($search_term || $location_id || $category) {
            $data['search_results'] = $this->Storage_model->search_storage($search_term, $location_id, $category);
        } else {
            $data['search_results'] = array();
        }

        $this->load->view('templates/header', $data);
        $this->load->view('storage/search', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Get available stock for AJAX requests
     */
    public function get_stock()
    {
        $category = $this->input->get('category');
        $type_id = $this->input->get('type_id');

        if ($category && $type_id) {
            $stock_locations = $this->Storage_model->get_available_stock($category, $type_id);
            $total_stock = $this->Storage_model->get_total_stock($category, $type_id);

            $response = array(
                'success' => true,
                'stock_locations' => $stock_locations,
                'total_stock' => $total_stock
            );
        } else {
            $response = array(
                'success' => false,
                'message' => 'Category and Type ID are required'
            );
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    /**
     * Get storage item details for AJAX requests
     */
    public function get_item_details()
    {
        $location_id = $this->input->get('location_id');
        $category = $this->input->get('category');
        $type_id = $this->input->get('type_id');

        if ($location_id && $category && $type_id) {
            $item = $this->Storage_model->get_storage_item($location_id, $category, $type_id);

            if ($item) {
                $response = array(
                    'success' => true,
                    'item' => $item
                );
            } else {
                $response = array(
                    'success' => false,
                    'message' => 'Item not found'
                );
            }
        } else {
            $response = array(
                'success' => false,
                'message' => 'Missing required parameters'
            );
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    /**
     * Reports page
     */
    public function reports()
    {
        $data['title'] = 'Storage Reports';

        // Get filter parameters
        $start_date = $this->input->get('start_date');
        $end_date = $this->input->get('end_date');
        $action = $this->input->get('action');
        $location_id = $this->input->get('location');
        $category = $this->input->get('category');

        $filters = array();
        if ($start_date) $filters['start_date'] = $start_date;
        if ($end_date) $filters['end_date'] = $end_date;
        if ($action) $filters['action'] = $action;
        if ($location_id) $filters['location_id'] = $location_id;
        if ($category) $filters['category'] = $category;

        $data['filters'] = $filters;
        $data['locations'] = $this->Storage_model->get_all_locations();

        // Pagination configuration
        $config = [
            'base_url'   => site_url('storage/reports'),
            'total_rows' => $this->Report_model->count_transactions($filters),
            'per_page'   => self::CONFIG['pagination']['items_per_page'],
            'reuse_query_string' => true,
        ];
        $this->pagination->initialize($config);

        // Get current page
        $startData = (int) ($this->uri->segment(3) ? $this->uri->segment(3) : 0);

        // Get transactions with pagination
        if (!empty($filters)) {
            $data['transactions'] = $this->Report_model->search_transactions('', $filters, $config['per_page'], $startData);
        } else {
            $data['transactions'] = $this->Report_model->get_all_transactions($config['per_page'], $startData);
        }

        // Add pagination data to view
        $transactionCount = count($data['transactions']);
        $data['display'] = ($startData + 1) . ' - ' . ($startData + $transactionCount) . ' dari ' . $config['total_rows'];
        $data['pagination_links'] = $this->pagination->create_links();
        $data['total_transactions'] = $config['total_rows'];
        $data['current_page'] = $startData;
        $data['per_page'] = $config['per_page'];

        // Get statistics
        $data['stats'] = $this->Report_model->get_transaction_stats($start_date, $end_date);
        $data['daily_summary'] = $this->Report_model->get_daily_summary($start_date, $end_date);

        $this->load->view('templates/header', $data);
        $this->load->view('storage/reports', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Quick actions - for AJAX quick store/take
     */
    public function quick_action()
    {
        $action = $this->input->post('action'); // 'store' or 'take'
        $location_id = $this->input->post('location_id');
        $category = $this->input->post('category');
        $type_id = $this->input->post('type_id');
        $quantity = (int)$this->input->post('quantity');
        $note = $this->input->post('note');
        $editor_nik = $this->session->userdata('user_data')['nik'];

        if ($action === 'store') {
            $result = $this->Storage_model->store_items($location_id, $category, $type_id, $quantity, $editor_nik);
            if ($result) {
                $this->Report_model->log_store_transaction($location_id, $category, $type_id, $editor_nik, $note, $quantity);
                $response = array('success' => true, 'message' => 'Items stored successfully');
            } else {
                $response = array('success' => false, 'message' => 'Failed to store items');
            }
        } elseif ($action === 'take') {
            $result = $this->Storage_model->take_items($location_id, $category, $type_id, $quantity, $editor_nik);
            if ($result['success']) {
                $this->Report_model->log_take_transaction($location_id, $category, $type_id, $editor_nik, $note, $quantity);
            }
            $response = $result;
        } else {
            $response = array('success' => false, 'message' => 'Invalid action');
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    /**
     * Export location data to Excel
     */
    public function export_location_excel($location_id = null)
    {
        if (!$location_id) {
            show_404();
            return;
        }

        $items = $this->Storage_model->get_storage_by_location($location_id);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'Category');
        $sheet->setCellValue('B1', 'Type ID');
        $sheet->setCellValue('C1', 'Amount');
        $sheet->setCellValue('D1', 'Updated At');

        // Style headers
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);
        $sheet->getStyle('A1:D1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFCCCCCC');

        // Add data
        $row = 2;
        foreach ($items as $item) {
            $sheet->setCellValue('A' . $row, $item['category']);
            $sheet->setCellValue('B' . $row, $item['type_id']);
            $sheet->setCellValue('C' . $row, $item['amount']);
            $sheet->setCellValue('D' . $row, $item['updated_at']);
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set filename and download
        $filename = 'storage_location_' . $location_id . '_' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Export search results to Excel
     */
    public function export_search_excel()
    {
        $search_term = $this->input->get('search');
        $location_id = $this->input->get('location');
        $category = $this->input->get('category');

        $results = $this->Storage_model->search_storage($search_term, $location_id, $category);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'Location ID');
        $sheet->setCellValue('B1', 'Category');
        $sheet->setCellValue('C1', 'Type ID');
        $sheet->setCellValue('D1', 'Amount');
        $sheet->setCellValue('E1', 'Updated At');

        // Style headers
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        $sheet->getStyle('A1:E1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFCCCCCC');

        // Add data
        $row = 2;
        foreach ($results as $item) {
            $sheet->setCellValue('A' . $row, $item['location_id']);
            $sheet->setCellValue('B' . $row, $item['category']);
            $sheet->setCellValue('C' . $row, $item['type_id']);
            $sheet->setCellValue('D' . $row, $item['amount']);
            $sheet->setCellValue('E' . $row, $item['updated_at']);
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set filename and download
        $filename = 'storage_search_results_' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Export transactions to Excel
     */
    public function export_transactions_excel()
    {
        $start_date = $this->input->get('start_date');
        $end_date = $this->input->get('end_date');

        if ($start_date && $end_date) {
            $transactions = $this->Report_model->get_transactions_by_date($start_date, $end_date, 1000);
        } else {
            $transactions = $this->Report_model->get_all_transactions(1000);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'Storing ID');
        $sheet->setCellValue('B1', 'Location ID');
        $sheet->setCellValue('C1', 'DateTime');
        $sheet->setCellValue('D1', 'Category');
        $sheet->setCellValue('E1', 'Type ID');
        $sheet->setCellValue('F1', 'Action');
        $sheet->setCellValue('G1', 'Amount');
        $sheet->setCellValue('H1', 'Note');
        $sheet->setCellValue('I1', 'NIK');

        // Style headers
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        $sheet->getStyle('A1:I1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFCCCCCC');

        // Add data
        $row = 2;
        foreach ($transactions as $transaction) {
            $sheet->setCellValue('A' . $row, $transaction['storing_id']);
            $sheet->setCellValue('B' . $row, $transaction['location_id']);
            $sheet->setCellValue('C' . $row, $transaction['datetime']);
            $sheet->setCellValue('D' . $row, $transaction['category']);
            $sheet->setCellValue('E' . $row, $transaction['type_id']);
            $sheet->setCellValue('F' . $row, $transaction['action']);
            $sheet->setCellValue('G' . $row, isset($transaction['amount']) ? (int)$transaction['amount'] : 1);
            $sheet->setCellValue('H' . $row, $transaction['note']);
            $sheet->setCellValue('I' . $row, $transaction['nik']);
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set filename and download
        $filename = 'storage_transactions_' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
