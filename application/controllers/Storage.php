<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once FCPATH . 'vendor/autoload.php';

/**
 * Storage controller for air-system.
 *
 * Manage storage operations: inventory tracking, storage/retrieval transactions,
 * location management, and reporting. Handles both regular and project-based
 * storage operations with proper auditing and batch tracking.
 *
 * @package AirSystem
 * @subpackage Controllers
 * @category Storage
 * @author Apparel One Indonesia
 * @version 1.0.0
 * @property CI_Session $session
 * @property CI_Input $input
 * @property CI_URI $uri
 * @property CI_Form_validation $form_validation
 * @property CI_Pagination $pagination
 * @property Storage_model $Storage_model
 * @property Report_model $Report_model
 * @property Pneumatic_model $Pneumatic_model
 * @property Fitting_model $Fitting_model
 * @property Solenoid_model $Solenoid_model
 * @property Manifold_model $Manifold_model
 * @property Project_batch_model $Project_batch_model
 */
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

    /**
     * Constructor for Storage controller.
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

        // Load required models and libraries
        $this->load->model([
            'Storage_model',
            'Report_model',
            'Pneumatic_model',
            'Pneumatic_type_model',
            'Fitting_model',
            'Fitting_type_model',
            'Solenoid_model',
            'Solenoid_type_model',
            'Manifold_model',
            'Project_batch_model'
        ]);
        $this->load->library(['form_validation', 'session', 'pagination']);

        // Reset session data when switching controllers
        reset_controller_session('storage');
    }

    /**
     * Display storage overview with search and filtering capabilities.
     *
     * Shows the main storage dashboard with inventory listings, search functionality,
     * and pagination. Handles GET parameters for search and maintains session state.
     *
     * @return void
     */
    public function index(): void
    {
        // Handle search from GET parameters
        $keyword = $this->input->get('keyword');
        if ($this->input->get('find') && $keyword !== null) {
            $this->session->set_userdata('keyword', $keyword);
            $this->session->unset_userdata(['sort', 'filter']);
            redirect('storage?keyword=' . urlencode($keyword));
        }

        // Handle reset (if no keyword in URL)
        if (!$this->input->get('keyword')) {
            $this->session->unset_userdata(['keyword', 'sort', 'filter']);
        }

        $data['title'] = 'Storage Overview';
        $data['user_data'] = $this->session->userdata('user_data');

        try {
            // Get storage overview data using available methods
            $keyword = $this->session->userdata('keyword');
            $data['storage_overview'] = $this->Storage_model->get_storage_overview($keyword);

            // Use search-aware methods for locations and transactions when keyword is present
            if ($keyword) {
                $data['locations'] = $this->Storage_model->get_locations_with_search($keyword);
                $data['recent_transactions'] = $this->Report_model->get_transactions_with_search($keyword, 7);
            } else {
                $data['locations'] = $this->Storage_model->get_all_locations();
                $data['recent_transactions'] = $this->Report_model->get_all_transactions(7);
            }

            // Get search and filter data from session
            $data['keyword'] = $keyword ?: '';

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

        $location_id = strtoupper($location_id);

        $data['title'] = 'Storage Location: ' . $location_id;
        $data['location_id'] = $location_id;

        // Get storage items with type images
        $storage_items = $this->Storage_model->get_storage_by_location($location_id);

        // Add type images to each storage item
        foreach ($storage_items as &$item) {
            $type_image = null;
            $base_type = str_replace('_PROJECT', '', $item['type_id']);

            if ($item['category'] === 'pneumatic') {
                // Get pneumatic type image
                $this->db->select('pt.image');
                $this->db->from('as_pneumatic p');
                $this->db->join('as_pneumatic_types pt', 'p.type = pt.type', 'left');
                $this->db->where('p.pneumatic_id', $base_type);
                $this->db->limit(1);
                $query = $this->db->get();
                if ($query->num_rows() > 0) {
                    $result = $query->row_array();
                    $type_image = $result['image'];
                }
            } elseif ($item['category'] === 'fitting') {
                // Get fitting type image
                $this->db->select('ft.image');
                $this->db->from('as_fitting f');
                $this->db->join('as_fitting_types ft', 'f.type = ft.type', 'left');
                $this->db->where('f.fitting_id', $base_type);
                $this->db->limit(1);
                $query = $this->db->get();
                if ($query->num_rows() > 0) {
                    $result = $query->row_array();
                    $type_image = $result['image'];
                }
            } elseif ($item['category'] === 'solenoid') {
                // Get solenoid type image
                $this->db->select('st.image');
                $this->db->from('as_solenoid s');
                $this->db->join('as_solenoid_types st', 's.type = st.type', 'left');
                $this->db->where('s.solenoid_id', $base_type);
                $this->db->limit(1);
                $query = $this->db->get();
                if ($query->num_rows() > 0) {
                    $result = $query->row_array();
                    $type_image = $result['image'];
                }
            } elseif ($item['category'] === 'manifold') {
                // Manifold doesn't have type images, use default
                $type_image = null;
            }

            $item['type_image'] = $type_image;
        }

        $data['storage_items'] = $storage_items;
        $data['location_transactions'] = $this->Report_model->get_transactions_by_location($location_id, 20);
        $data['project_batches'] = $this->Project_batch_model->get_batches_by_location($location_id);

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
        $data['pneumatic_items'] = $this->get_pneumatics_with_images();
        $data['fitting_items'] = $this->get_fittings_with_images();
        $data['solenoid_items'] = $this->get_solenoids_with_images();
        $data['manifold_items'] = $this->get_manifolds_with_images();
        $data['locations'] = $this->Storage_model->get_all_locations();

        // Set validation rules
        $this->form_validation->set_rules('location_id', 'Location ID', 'required|max_length[3]');
        $this->form_validation->set_rules('category', 'Category', 'required|max_length[15]');
        $this->form_validation->set_rules('type_id', 'Type ID', 'required|max_length[50]');
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
        $location_id = strtoupper($this->input->post('location_id'));
        $category = $this->input->post('category');
        $type_id = $this->input->post('type_id');
        $quantity = (int)$this->input->post('quantity');
        $note = $this->input->post('note');
        $is_project_item = $this->input->post('is_project_item') ? true : false;
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

        // Validate if fitting exists (for fitting category)
        if ($category === 'fitting') {
            $fitting = $this->Fitting_model->getById($type_id);
            if (!$fitting) {
                $this->session->set_flashdata('error', 'Fitting item not found!');
                redirect('storage/store');
                return;
            }
        }

        // Validate if solenoid exists (for solenoid category)
        if ($category === 'solenoid') {
            $solenoid = $this->Solenoid_model->getById($type_id);
            if (!$solenoid) {
                $this->session->set_flashdata('error', 'Solenoid item not found!');
                redirect('storage/store');
                return;
            }
        }

        // Validate if manifold exists (for manifold category)
        if ($category === 'manifold') {
            $manifold = $this->Manifold_model->getById($type_id);
            if (!$manifold) {
                $this->session->set_flashdata('error', 'Manifold item not found!');
                redirect('storage/store');
                return;
            }
        }

        // Prepare storage data for project flag
        $storage_data = null;
        $type_id_for_db = $type_id;
        $batch_id = null;

        if ($is_project_item) {
            $type_id_for_db = $type_id . '_PROJECT';

            // Create project batch for tracking notes
            $project_name = $this->input->post('project_name') ?: 'Unnamed Project';
            $batch_id = $this->Project_batch_model->create_batch(
                $location_id,
                $category,
                $type_id_for_db,
                $project_name,
                $note,
                $quantity,
                $editor_nik
            );

            if (!$batch_id) {
                $this->session->set_flashdata('error', 'Failed to create project batch!');
                redirect('storage/store');
                return;
            }
        }

        // Store the items
        $store_result = $this->Storage_model->store_items($location_id, $category, $type_id_for_db, $quantity, $editor_nik, $storage_data);

        if ($store_result) {
            // Log the transaction
            $this->Report_model->log_store_transaction($location_id, $category, $type_id, $editor_nik, $note, $quantity, $is_project_item, $batch_id);

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
        $data['storage_items'] = $this->get_storage_items_with_images();
        $data['locations'] = $this->Storage_model->get_all_locations();

        // Set validation rules
        $this->form_validation->set_rules('location_id', 'Location ID', 'required|callback_validate_location_id');
        $this->form_validation->set_rules('category', 'Category', 'required|max_length[15]');
        $this->form_validation->set_rules('type_id', 'Type ID', 'required|max_length[50]');
        $this->form_validation->set_rules('quantity', 'Quantity', 'required|integer|greater_than[0]');
        $this->form_validation->set_rules('note', 'Note', 'max_length[255]');
        $this->form_validation->set_rules('batch_id', 'Batch ID', 'callback_validate_batch_id');

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
        $location_value = $this->input->post('location_id');
        $category = $this->input->post('category');
        $type_id = $this->input->post('type_id');
        $quantity = (int)$this->input->post('quantity');
        $note = $this->input->post('note');
        $batch_id = $this->input->post('batch_id'); // For project items
        $editor_nik = $this->session->userdata('user_data')['nik'];

        // Parse location value to separate location_id and project status
        $is_project = strpos($location_value, '_project') !== false;
        $location_id = strtoupper($is_project ? str_replace('_project', '', $location_value) : $location_value);

        // For project items, append _PROJECT to type_id
        $type_id_for_db = $is_project ? $type_id . '_PROJECT' : $type_id;

        if ($is_project && $batch_id) {
            // Handle project batch taking
            $batch_result = $this->Project_batch_model->take_from_specific_batch($batch_id, $quantity);

            if (!$batch_result['success']) {
                $this->session->set_flashdata('error', $batch_result['message']);
                redirect('storage/take');
                return;
            }

            // Update storage table
            $take_result = $this->Storage_model->take_items($location_id, $category, $type_id_for_db, $quantity, $editor_nik);

            if ($take_result['success']) {
                // Log the transaction with batch information
                $this->Report_model->log_take_transaction($location_id, $category, $type_id, $editor_nik, $note, $quantity, $is_project, $batch_id);

                $this->session->set_flashdata('success', 'Items taken from project batch successfully!');
                redirect('storage/location/' . $location_id);
            } else {
                // Rollback batch changes if storage update failed
                $this->Project_batch_model->add_back_to_batch($batch_id, $quantity);
                $this->session->set_flashdata('error', $take_result['message']);
                redirect('storage/take');
            }
        } else {
            // Handle regular item taking
            $take_result = $this->Storage_model->take_items($location_id, $category, $type_id_for_db, $quantity, $editor_nik);

            if ($take_result['success']) {
                // Log the transaction
                $this->Report_model->log_take_transaction($location_id, $category, $type_id, $editor_nik, $note, $quantity, $is_project);

                $this->session->set_flashdata('success', $take_result['message']);
                redirect('storage/location/' . $location_id);
            } else {
                $this->session->set_flashdata('error', $take_result['message']);
                redirect('storage/take');
            }
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
     * Get type image for AJAX requests
     */
    public function get_type_image()
    {
        $category = $this->input->get('category');
        $type_id = $this->input->get('type_id');

        $response = array('success' => false);

        if ($category && $type_id) {
            $type_image = null;

            // Remove _PROJECT suffix if present to get the base type_id
            $base_type_id = str_replace('_PROJECT', '', $type_id);

            if ($category === 'pneumatic') {
                $this->db->select('pt.image');
                $this->db->from('as_pneumatic p');
                $this->db->join('as_pneumatic_types pt', 'p.type = pt.type', 'left');
                $this->db->where('p.pneumatic_id', $base_type_id);
                $this->db->limit(1);
                $query = $this->db->get();
                if ($query->num_rows() > 0) {
                    $result = $query->row_array();
                    $type_image = $result['image'];
                }
            } elseif ($category === 'fitting') {
                $this->db->select('ft.image');
                $this->db->from('as_fitting f');
                $this->db->join('as_fitting_types ft', 'f.type = ft.type', 'left');
                $this->db->where('f.fitting_id', $base_type_id);
                $this->db->limit(1);
                $query = $this->db->get();
                if ($query->num_rows() > 0) {
                    $result = $query->row_array();
                    $type_image = $result['image'];
                }
            } elseif ($category === 'solenoid') {
                $this->db->select('st.image');
                $this->db->from('as_solenoid s');
                $this->db->join('as_solenoid_types st', 's.type = st.type', 'left');
                $this->db->where('s.solenoid_id', $base_type_id);
                $this->db->limit(1);
                $query = $this->db->get();
                if ($query->num_rows() > 0) {
                    $result = $query->row_array();
                    $type_image = $result['image'];
                }
            }

            if ($type_image) {
                $response = array(
                    'success' => true,
                    'type_image' => $type_image
                );
            } else {
                $response = array(
                    'success' => false,
                    'message' => 'No image found for this type'
                );
            }
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
        $location_id = strtoupper($this->input->get('location_id'));
        $category = $this->input->get('category');
        $type_id = $this->input->get('type_id');

        if ($location_id && $category && $type_id) {
            $item = $this->Storage_model->get_storage_item($location_id, $category, $type_id);

            if ($item) {
                $response = array(
                    'success' => true,
                    'item' => $item
                );

                // Check if this is a project item and get all project notes
                $is_project = strpos($type_id, '_PROJECT') !== false;
                if ($is_project) {
                    // Load Project_batch_model if not loaded
                    $this->load->model('Project_batch_model');

                    // Get all project batches for this item
                    $project_batches = $this->Project_batch_model->get_project_batches($location_id, $category, $type_id);

                    if (!empty($project_batches)) {
                        $response['project_batches'] = $project_batches;
                    }
                }
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
        // Add error logging for debugging
        log_message('debug', 'Quick action called with POST data: ' . json_encode($_POST));

        $action = $this->input->post('action'); // 'store' or 'take'
        $location_id = strtoupper($this->input->post('location_id'));
        $category = $this->input->post('category');
        $type_id = $this->input->post('type_id');
        $quantity = (int)$this->input->post('quantity');
        $note = $this->input->post('note');
        $batch_id = $this->input->post('batch_id'); // For project items

        // Check if user session is valid
        $user_data = $this->session->userdata('user_data');
        if (!$user_data || !isset($user_data['nik'])) {
            log_message('error', 'Invalid user session in quick_action');
            $response = array('success' => false, 'message' => 'Session expired. Please login again.');
            header('Content-Type: application/json');
            echo json_encode($response);
            return;
        }

        $editor_nik = $user_data['nik'];

        // Validate required fields
        if (!$action || !$location_id || !$category || !$type_id || !$quantity) {
            log_message('error', 'Missing required fields in quick_action');
            $response = array('success' => false, 'message' => 'Missing required fields');
            header('Content-Type: application/json');
            echo json_encode($response);
            return;
        }

        if ($action === 'store') {
            $result = $this->Storage_model->store_items($location_id, $category, $type_id, $quantity, $editor_nik);
            if ($result) {
                $this->Report_model->log_store_transaction($location_id, $category, $type_id, $editor_nik, $note, $quantity);
                $this->session->set_flashdata('success', 'Barang berhasil disimpan!');
                $response = array('success' => true, 'message' => 'Items stored successfully');
            } else {
                $response = array('success' => false, 'message' => 'Failed to store items');
            }
        } elseif ($action === 'take') {
            log_message('debug', 'Take action - batch_id: ' . $batch_id . ', type_id: ' . $type_id);

            // For project items, include batch_id in the take operation
            if ($batch_id && strpos($type_id, '_PROJECT') !== false) {
                log_message('debug', 'Using take_project_items with batch_id: ' . $batch_id);
                $result = $this->Storage_model->take_project_items($location_id, $category, $type_id, $quantity, $editor_nik, $batch_id);
            } else {
                log_message('debug', 'Using regular take_items');
                $result = $this->Storage_model->take_items($location_id, $category, $type_id, $quantity, $editor_nik);
            }

            log_message('debug', 'Take result: ' . json_encode($result));

            if ($result['success']) {
                // Determine if it's a project transaction
                $is_project = strpos($type_id, '_PROJECT') !== false;
                // Only pass batch_id if it's actually a project item with a batch
                $log_batch_id = ($batch_id && $is_project) ? $batch_id : null;
                $this->Report_model->log_take_transaction($location_id, $category, $type_id, $editor_nik, $note, $quantity, $is_project, $log_batch_id);
                $this->session->set_flashdata('success', 'Barang berhasil diambil!');
            }
            $response = $result;
        } else {
            $response = array('success' => false, 'message' => 'Invalid action');
        }

        // Ensure no extra output before JSON
        ob_clean();
        header('Content-Type: application/json');
        http_response_code(200); // Ensure 200 status code
        echo json_encode($response);
        exit(); // Prevent any additional output
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

        $location_id = strtoupper($location_id);

        $items = $this->Storage_model->get_storage_by_location($location_id);

        // Set filename and headers for CSV download
        $filename = 'storage_location_' . $location_id . '_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // Open output stream
        $output = fopen('php://output', 'w');

        // Add UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Write headers
        fputcsv($output, ['Category', 'Type ID', 'Amount', 'Updated At']);

        // Write data
        foreach ($items as $item) {
            fputcsv($output, [
                $item['category'],
                $item['type_id'],
                $item['amount'],
                $item['updated_at']
            ]);
        }

        fclose($output);
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

        // Set filename and headers for CSV download
        $filename = 'storage_search_results_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // Open output stream
        $output = fopen('php://output', 'w');

        // Add UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Write headers
        fputcsv($output, ['Location ID', 'Category', 'Type ID', 'Amount', 'Updated At']);

        // Write data
        foreach ($results as $item) {
            fputcsv($output, [
                $item['location_id'],
                $item['category'],
                $item['type_id'],
                $item['amount'],
                $item['updated_at']
            ]);
        }

        fclose($output);
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

        // Set filename and headers for CSV download
        $filename = 'storage_transactions_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // Open output stream
        $output = fopen('php://output', 'w');

        // Add UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Write headers
        fputcsv($output, ['Storing ID', 'Location ID', 'DateTime', 'Category', 'Type ID', 'Action', 'Amount', 'Note', 'NIK']);

        // Write data
        foreach ($transactions as $transaction) {
            fputcsv($output, [
                $transaction['storing_id'],
                $transaction['location_id'],
                $transaction['datetime'],
                $transaction['category'],
                $transaction['type_id'],
                $transaction['action'],
                isset($transaction['amount']) ? (int)$transaction['amount'] : 1,
                $transaction['note'],
                $transaction['nik']
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Export location storage to PDF
     */
    public function export_location_pdf($location_id = null)
    {
        if (!$location_id) {
            show_404();
            return;
        }

        $location_id = strtoupper($location_id);
        $items = $this->Storage_model->get_storage_by_location($location_id);

        // Generate simple HTML for PDF
        $html = $this->generate_location_pdf_html($location_id, $items);

        // Set filename and headers for PDF download
        $filename = 'storage_location_' . $location_id . '_' . date('Y-m-d') . '.pdf';

        $this->output_pdf($html, $filename);
    }

    /**
     * Export transactions to PDF
     */
    public function export_transactions_pdf()
    {
        $start_date = $this->input->get('start_date');
        $end_date = $this->input->get('end_date');

        if ($start_date && $end_date) {
            $transactions = $this->Report_model->get_transactions_by_date($start_date, $end_date, 1000);
        } else {
            $transactions = $this->Report_model->get_all_transactions(1000);
        }

        // Generate simple HTML for PDF
        $html = $this->generate_transactions_pdf_html($transactions, $start_date, $end_date);

        // Set filename and headers for PDF download
        $filename = 'storage_transactions_' . date('Y-m-d') . '.pdf';

        $this->output_pdf($html, $filename);
    }

    /**
     * Generate HTML for location PDF
     */
    private function generate_location_pdf_html($location_id, $items)
    {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
        $html .= '<style>body{font-family:Arial,sans-serif;font-size:12px;}';
        $html .= 'table{width:100%;border-collapse:collapse;margin-top:20px;}';
        $html .= 'th,td{border:1px solid #ddd;padding:8px;text-align:left;}';
        $html .= 'th{background-color:#f2f2f2;font-weight:bold;}';
        $html .= 'h2{color:#333;}</style></head><body>';
        $html .= '<h2>Storage Location Report: ' . htmlspecialchars($location_id) . '</h2>';
        $html .= '<p>Generated: ' . date('Y-m-d H:i:s') . '</p>';
        $html .= '<table><thead><tr>';
        $html .= '<th>Category</th><th>Type ID</th><th>Amount</th><th>Updated At</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($items as $item) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($item['category']) . '</td>';
            $html .= '<td>' . htmlspecialchars($item['type_id']) . '</td>';
            $html .= '<td>' . htmlspecialchars($item['amount']) . '</td>';
            $html .= '<td>' . htmlspecialchars($item['updated_at']) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table></body></html>';
        return $html;
    }

    /**
     * Generate HTML for transactions PDF
     */
    private function generate_transactions_pdf_html($transactions, $start_date, $end_date)
    {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
        $html .= '<style>body{font-family:Arial,sans-serif;font-size:10px;}';
        $html .= 'table{width:100%;border-collapse:collapse;margin-top:20px;}';
        $html .= 'th,td{border:1px solid #ddd;padding:6px;text-align:left;}';
        $html .= 'th{background-color:#f2f2f2;font-weight:bold;}';
        $html .= 'h2{color:#333;}</style></head><body>';
        $html .= '<h2>Storage Transactions Report</h2>';

        if ($start_date && $end_date) {
            $html .= '<p>Period: ' . htmlspecialchars($start_date) . ' to ' . htmlspecialchars($end_date) . '</p>';
        }

        $html .= '<p>Generated: ' . date('Y-m-d H:i:s') . '</p>';
        $html .= '<table><thead><tr>';
        $html .= '<th>Storing ID</th><th>Location</th><th>DateTime</th><th>Category</th>';
        $html .= '<th>Type ID</th><th>Action</th><th>Amount</th><th>Note</th><th>NIK</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($transactions as $transaction) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($transaction['storing_id']) . '</td>';
            $html .= '<td>' . htmlspecialchars($transaction['location_id']) . '</td>';
            $html .= '<td>' . htmlspecialchars($transaction['datetime']) . '</td>';
            $html .= '<td>' . htmlspecialchars($transaction['category']) . '</td>';
            $html .= '<td>' . htmlspecialchars($transaction['type_id']) . '</td>';
            $html .= '<td>' . htmlspecialchars($transaction['action']) . '</td>';
            $html .= '<td>' . htmlspecialchars(isset($transaction['amount']) ? (int)$transaction['amount'] : 1) . '</td>';
            $html .= '<td>' . htmlspecialchars($transaction['note']) . '</td>';
            $html .= '<td>' . htmlspecialchars($transaction['nik']) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table></body></html>';
        return $html;
    }

    /**
     * Output PDF using TCPDF library
     */
    private function output_pdf($html, $filename)
    {
        // Create new PDF document
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('Air System');
        $pdf->SetAuthor('Air System');
        $pdf->SetTitle($filename);

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);

        // Add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', '', 10);

        // Output the HTML content
        $pdf->writeHTML($html, true, false, true, false, '');

        // Close and output PDF document
        $pdf->Output($filename, 'D'); // 'D' = force download
        exit;
    }

    /**
     * Custom validation callback for location_id field
     * Handles compound values like "A01_project"
     */
    public function validate_location_id($location_value)
    {
        if (empty($location_value)) {
            $this->form_validation->set_message('validate_location_id', 'The Location ID field is required.');
            return FALSE;
        }

        // Extract the actual location_id (remove _project suffix if present)
        $location_id = strpos($location_value, '_project') !== false
            ? str_replace('_project', '', $location_value)
            : $location_value;

        // Validate that the actual location_id is 3 characters or less
        if (strlen($location_id) > 3) {
            $this->form_validation->set_message('validate_location_id', 'The Location ID field cannot exceed 3 characters in length.');
            return FALSE;
        }

        // Validate that it contains only valid characters (alphanumeric)
        if (!preg_match('/^[A-Za-z0-9]+$/', $location_id)) {
            $this->form_validation->set_message('validate_location_id', 'The Location ID field may only contain alphanumeric characters.');
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Custom validation callback for batch_id field
     * Required for project items only
     */
    public function validate_batch_id($batch_id)
    {
        $location_value = $this->input->post('location_id');
        $is_project = strpos($location_value, '_project') !== false;

        if ($is_project && empty($batch_id)) {
            $this->form_validation->set_message('validate_batch_id', 'The Batch ID field is required for project items.');
            return FALSE;
        }

        if (!empty($batch_id)) {
            // Validate that the batch exists and has remaining quantity
            $batch = $this->Project_batch_model->get_batch_by_id($batch_id);
            if (!$batch) {
                $this->form_validation->set_message('validate_batch_id', 'The selected batch does not exist.');
                return FALSE;
            }

            if ($batch->remaining_quantity <= 0) {
                $this->form_validation->set_message('validate_batch_id', 'The selected batch has no remaining quantity.');
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * User history - shows transaction history for a specific user
     */
    public function user_history($nik_encoded = null)
    {
        if (!$nik_encoded) {
            redirect('user');
        }

        $nik = base64_decode(urldecode($nik_encoded), true);
        if ($nik === false) {
            set_message(['danger', 'NIK tidak valid']);
            redirect('user');
        }

        // Load User model to get user details
        $this->load->model('User_model');
        $user = $this->User_model->getByNik($nik);
        if (!$user) {
            set_message(['danger', 'Pengguna tidak ditemukan']);
            redirect('user');
        }

        // Handle session state for user history
        $this->handleUserHistorySessionState($nik_encoded);

        $sessionData = [
            'search' => $this->session->userdata('keyword'),
            'filter' => $this->session->userdata('filter'),
        ];

        // Setup pagination
        $config = [
            'base_url'   => site_url('storage/user_history/' . $nik_encoded),
            'total_rows' => $this->Report_model->count_user_transactions($nik, $sessionData['search'], $sessionData['filter']),
            'per_page'   => self::CONFIG['pagination']['items_per_page'],
            'reuse_query_string' => true,
        ];
        $this->pagination->initialize($config);

        // Get current page
        $startData = (int) ($this->uri->segment(4) ? $this->uri->segment(4) : 0);

        // Get user transactions
        $transactions = $this->Report_model->search_user_transactions(
            $nik,
            $sessionData['search'],
            $sessionData['filter'],
            $config['per_page'],
            $startData
        );

        // Prepare view data
        $transactionCount = count($transactions);
        $data = [
            'title'         => 'History User - ' . $user['name'],
            'user'          => $user,
            'transactions'  => $transactions,
            'display'       => ($startData + 1) . ' - ' . ($startData + $transactionCount) . ' dari ' . $config['total_rows'],
            'pagination_links' => $this->pagination->create_links(),
            'hasFilters'    => (!empty($sessionData['search']) || !empty($sessionData['filter'])),
            'actions'       => $this->Report_model->get_user_transaction_filters($nik, 'action'),
            'locations'     => $this->Report_model->get_user_transaction_filters($nik, 'location_id'),
            'categories'    => $this->Report_model->get_user_transaction_filters($nik, 'category'),
        ];

        $this->load->view('templates/header', $data);
        $this->load->view('storage/user_history', $data);
        $this->load->view('templates/footer');
    }

    /**
     * Handle session state for user history
     */
    private function handleUserHistorySessionState($nik_encoded)
    {
        // Set unique controller session for user history
        if ($this->session->userdata('controller') !== 'user_history_storage') {
            $this->session->set_userdata('controller', 'user_history_storage');
            $this->session->unset_userdata(['keyword', 'filter']);
        }

        $redirectUrl = 'storage/user_history/' . $nik_encoded;

        // Handle search
        if ($this->input->post('find')) {
            $keyword = trim($this->input->post('keyword', true));
            $this->session->set_userdata('keyword', $keyword);
            redirect($redirectUrl);
        }

        // Handle filter
        $filterKeys = ['action' => 'action', 'location' => 'location_id', 'category' => 'category'];
        foreach ($filterKeys as $postKey => $sessionKey) {
            if ($this->input->post($postKey)) {
                $filterValues = $this->input->post("filter-{$postKey}", true);
                $filters = $this->session->userdata('filter') ?: [];
                $filters[$sessionKey] = $filterValues;
                $this->session->set_userdata('filter', $filters);
                redirect($redirectUrl);
            }
        }

        // Handle reset
        if ($this->input->post('reset')) {
            $this->session->unset_userdata(['keyword', 'filter']);
            redirect($redirectUrl);
        }
    }

    /**
     * Edit item - updates category and type_id across all related tables
     */
    public function edit_item()
    {
        $this->load->library('form_validation');

        $this->form_validation->set_rules('original_category', 'Original Category', 'required');
        $this->form_validation->set_rules('original_type_id', 'Original Type ID', 'required');
        $this->form_validation->set_rules('new_category', 'New Category', 'required|trim');
        $this->form_validation->set_rules('new_type_id', 'New Type ID', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            $response = array(
                'success' => false,
                'message' => validation_errors()
            );
        } else {
            $original_category = $this->input->post('original_category');
            $original_type_id = $this->input->post('original_type_id');
            $new_category = $this->input->post('new_category');
            $new_type_id = $this->input->post('new_type_id');

            // Start transaction
            $this->db->trans_start();

            try {
                // Update storage table
                $this->db->where('category', $original_category);
                $this->db->where('type_id', $original_type_id);
                $this->db->update('as_storage', array(
                    'category' => $new_category,
                    'type_id' => $new_type_id
                ));

                // Update report table
                $this->db->where('category', $original_category);
                $this->db->where('type_id', $original_type_id);
                $this->db->update('as_report', array(
                    'category' => $new_category,
                    'type_id' => $new_type_id
                ));

                // Update project batches table
                $this->db->where('category', $original_category);
                $this->db->where('type_id', $original_type_id);
                $this->db->update('as_project_batches', array(
                    'category' => $new_category,
                    'type_id' => $new_type_id
                ));

                $this->db->trans_complete();

                if ($this->db->trans_status() === FALSE) {
                    $response = array(
                        'success' => false,
                        'message' => 'Failed to update item'
                    );
                } else {
                    $response = array(
                        'success' => true,
                        'message' => 'Item updated successfully'
                    );
                }
            } catch (Exception $e) {
                $this->db->trans_rollback();
                $response = array(
                    'success' => false,
                    'message' => 'Error updating item: ' . $e->getMessage()
                );
            }
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    /**
     * Delete item - removes all data for a specific category and type_id from a specific location (or all locations if no location specified)
     * This should only be called for items with no stock remaining
     */
    public function delete_item()
    {
        $input = json_decode($this->input->raw_input_stream, true);

        if (!isset($input['category']) || !isset($input['type_id'])) {
            $response = array(
                'success' => false,
                'message' => 'Missing required parameters'
            );
        } else {
            $category = $input['category'];
            $type_id = $input['type_id'];
            $location_id = isset($input['location_id']) ? strtoupper($input['location_id']) : null;

            // Double-check if there's any stock left (safety check)
            $total_stock = $this->Storage_model->get_total_stock($category, $type_id);
            if ($total_stock > 0) {
                $response = array(
                    'success' => false,
                    'message' => 'Cannot delete item - stock remaining: ' . $total_stock . ' items. Please remove all stock first.'
                );
            } else {
                // Start transaction
                $this->db->trans_start();

                try {
                    // Delete from project batches first (foreign key constraint)
                    $this->db->where('category', $category);
                    $this->db->where('type_id', $type_id);
                    if ($location_id) {
                        $this->db->where('location_id', $location_id);
                    }
                    $this->db->delete('as_project_batches');

                    // Delete from report table
                    $this->db->where('category', $category);
                    $this->db->where('type_id', $type_id);
                    if ($location_id) {
                        $this->db->where('location_id', $location_id);
                    }
                    $this->db->delete('as_report');

                    // Delete from storage table
                    $this->db->where('category', $category);
                    $this->db->where('type_id', $type_id);
                    if ($location_id) {
                        $this->db->where('location_id', $location_id);
                    }
                    $this->db->delete('as_storage');

                    $this->db->trans_complete();

                    if ($this->db->trans_status() === FALSE) {
                        $response = array(
                            'success' => false,
                            'message' => 'Failed to delete item'
                        );
                    } else {
                        $message = $location_id ? 'Item deleted successfully from this location' : 'Item deleted successfully from all locations';
                        $response = array(
                            'success' => true,
                            'message' => $message
                        );
                    }
                } catch (Exception $e) {
                    $this->db->trans_rollback();
                    $response = array(
                        'success' => false,
                        'message' => 'Error deleting item: ' . $e->getMessage()
                    );
                }
            }
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    /**
     * Get item batches for management interface
     */
    public function get_item_batches()
    {
        $category = $this->input->get('category');
        $type_id = $this->input->get('type_id');

        if (!$category || !$type_id) {
            $response = array(
                'success' => false,
                'message' => 'Category and type_id are required'
            );
        } else {
            // Get batches for this item
            $batches = $this->Project_batch_model->get_batches_by_item($category, $type_id);

            // Get total stock
            $total_stock = $this->Storage_model->get_total_stock($category, $type_id);

            $response = array(
                'success' => true,
                'batches' => $batches,
                'total_stock' => $total_stock
            );
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    /**
     * Get item locations for management interface
     */
    public function get_item_locations()
    {
        $category = $this->input->get('category');
        $type_id = $this->input->get('type_id');

        if (!$category || !$type_id) {
            $response = array(
                'success' => false,
                'message' => 'Category and type_id are required'
            );
        } else {
            $locations = $this->Storage_model->get_item_locations($category, $type_id);

            $response = array(
                'success' => true,
                'locations' => $locations
            );
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    /**
     * Update batch information
     */
    public function update_batch()
    {
        // Set JSON response header
        header('Content-Type: application/json');

        try {
            $batch_id = $this->input->post('batch_id');
            $project_name = $this->input->post('project_name');
            $project_notes = $this->input->post('project_notes');

            // Validate required fields
            if (!$batch_id) {
                $response = array(
                    'success' => false,
                    'message' => 'Batch ID is required'
                );
                echo json_encode($response);
                return;
            }

            if (!$project_name || trim($project_name) === '') {
                $response = array(
                    'success' => false,
                    'message' => 'Project name is required'
                );
                echo json_encode($response);
                return;
            }

            // Check if batch exists first
            $batch = $this->Project_batch_model->get_batch_by_id($batch_id);
            if (!$batch) {
                $response = array(
                    'success' => false,
                    'message' => 'Batch not found with ID: ' . $batch_id
                );
                echo json_encode($response);
                return;
            }

            // Only update editable fields: project_name and project_notes
            // Initial quantity and remaining quantity are managed automatically by the system
            $update_result = $this->Project_batch_model->update_batch_info($batch_id, trim($project_name), $project_notes);

            if ($update_result) {
                $response = array(
                    'success' => true,
                    'message' => 'Batch updated successfully'
                );
            } else {
                $response = array(
                    'success' => false,
                    'message' => 'Failed to update batch. Database update returned false.'
                );
            }
        } catch (Exception $e) {
            $response = array(
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            );
        }

        echo json_encode($response);
    }

    /**
     * Delete a batch
     */
    public function delete_batch()
    {
        $json = json_decode($this->input->raw_input_stream, true);
        $batch_id = isset($json['batch_id']) ? $json['batch_id'] : null;

        if (!$batch_id) {
            $response = array(
                'success' => false,
                'message' => 'Batch ID is required'
            );
        } else {
            // Check if batch has remaining quantity
            $batch_info = $this->Project_batch_model->get_batch_by_id($batch_id);

            if ($batch_info && $batch_info->remaining_quantity > 0) {
                $response = array(
                    'success' => false,
                    'message' => 'Cannot delete batch with remaining quantity. Please take all items first.'
                );
            } else {
                $delete_result = $this->Project_batch_model->delete_batch($batch_id);

                if ($delete_result) {
                    $response = array(
                        'success' => true,
                        'message' => 'Batch deleted successfully'
                    );
                } else {
                    $response = array(
                        'success' => false,
                        'message' => 'Failed to delete batch'
                    );
                }
            }
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    /**
     * Create new batch
     */
    public function create_batch()
    {
        $category = $this->input->post('category');
        $type_id = $this->input->post('type_id');
        $project_name = $this->input->post('project_name');
        $project_notes = $this->input->post('project_notes');
        $quantity = $this->input->post('quantity');
        $location_id = $this->input->post('location_id');

        if (!$category || !$type_id || !$project_name || !$quantity || !$location_id) {
            $response = array(
                'success' => false,
                'message' => 'All fields are required'
            );
        } else {
            $this->db->trans_start();

            try {
                // Create batch
                $batch_id = $this->Project_batch_model->create_item_batch($category, $type_id, $project_name, $project_notes, $quantity);

                if ($batch_id) {
                    // Add to storage
                    $type_id_for_db = ($category == 'Screw') ? (int)$type_id : $type_id;
                    $add_result = $this->Storage_model->store_items($location_id, $category, $type_id_for_db, $quantity, 'system');

                    if ($add_result) {
                        $this->db->trans_complete();

                        $response = array(
                            'success' => true,
                            'message' => 'Batch created successfully',
                            'batch_id' => $batch_id
                        );
                    } else {
                        throw new Exception('Failed to add items to storage');
                    }
                } else {
                    throw new Exception('Failed to create batch');
                }
            } catch (Exception $e) {
                $this->db->trans_rollback();
                $response = array(
                    'success' => false,
                    'message' => 'Error creating batch: ' . $e->getMessage()
                );
            }
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    /**
     * Get all locations for dropdown
     */
    public function get_all_locations()
    {
        $locations = $this->Storage_model->get_all_locations();

        $response = array(
            'success' => true,
            'locations' => $locations
        );

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    /**
     * Update location stock - DISABLED to prevent direct stock editing
     */
    public function update_location_stock()
    {
        $response = array(
            'success' => false,
            'message' => 'Direct stock editing is not allowed. Use store/take operations instead.'
        );

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    /**
     * Remove item from location
     */
    public function remove_from_location()
    {
        $json = json_decode($this->input->raw_input_stream, true);
        $category = isset($json['category']) ? $json['category'] : null;
        $type_id = isset($json['type_id']) ? $json['type_id'] : null;
        $location_id = isset($json['location_id']) ? $json['location_id'] : null;

        if (!$category || !$type_id || !$location_id) {
            $response = array(
                'success' => false,
                'message' => 'All parameters are required'
            );
        } else {
            $type_id_for_db = ($category == 'Screw') ? (int)$type_id : $type_id;
            $remove_result = $this->Storage_model->remove_from_location($location_id, $category, $type_id_for_db);

            if ($remove_result) {
                $response = array(
                    'success' => true,
                    'message' => 'Item removed from location successfully'
                );
            } else {
                $response = array(
                    'success' => false,
                    'message' => 'Failed to remove item from location'
                );
            }
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    /**
     * Get pneumatics with type images
     * 
     * @return array Array of pneumatic items with type images
     */
    private function get_pneumatics_with_images(): array
    {
        $this->db->select('p.*, pt.image as type_image');
        $this->db->from('as_pneumatic p');
        $this->db->join('as_pneumatic_types pt', 'p.type = pt.type', 'left');
        $this->db->order_by('p.updated_at', 'DESC');
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Get fittings with type images
     * 
     * @return array Array of fitting items with type images
     */
    private function get_fittings_with_images(): array
    {
        $this->db->select('f.*, ft.image as type_image');
        $this->db->from('as_fitting f');
        $this->db->join('as_fitting_types ft', 'f.type = ft.type', 'left');
        $this->db->order_by('f.fitting_id', 'ASC');
        $this->db->limit(1000);
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Get solenoids with type images
     * 
     * @return array Array of solenoid items with type images
     */
    private function get_solenoids_with_images(): array
    {
        $this->db->select('s.*, st.image as type_image');
        $this->db->from('as_solenoid s');
        $this->db->join('as_solenoid_types st', 's.type = st.type', 'left');
        $this->db->order_by('s.updated_at', 'DESC');
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Get manifolds (no type images for manifolds)
     * 
     * @return array Array of manifold items
     */
    private function get_manifolds_with_images(): array
    {
        $this->db->select("m.*, '' as type_image");
        $this->db->from('as_manifold m');
        $this->db->order_by('m.updated_at', 'DESC');
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Get storage items with type images
     * 
     * @return array Array of storage items with type images
     */
    private function get_storage_items_with_images(): array
    {
        $storage_items = $this->Storage_model->get_all_storage();

        // Add type images to each storage item
        foreach ($storage_items as &$item) {
            $type_image = null;
            $base_type = str_replace('_PROJECT', '', $item['type_id']);

            if ($item['category'] === 'pneumatic') {
                // Get pneumatic type image
                $this->db->select('pt.image');
                $this->db->from('as_pneumatic p');
                $this->db->join('as_pneumatic_types pt', 'p.type = pt.type', 'left');
                $this->db->where('p.pneumatic_id', $base_type);
                $this->db->limit(1);
                $query = $this->db->get();
                if ($query->num_rows() > 0) {
                    $result = $query->row_array();
                    $type_image = $result['image'];
                }
            } elseif ($item['category'] === 'fitting') {
                // Get fitting type image
                $this->db->select('ft.image');
                $this->db->from('as_fitting f');
                $this->db->join('as_fitting_types ft', 'f.type = ft.type', 'left');
                $this->db->where('f.fitting_id', $base_type);
                $this->db->limit(1);
                $query = $this->db->get();
                if ($query->num_rows() > 0) {
                    $result = $query->row_array();
                    $type_image = $result['image'];
                }
            } elseif ($item['category'] === 'solenoid') {
                // Get solenoid type image
                $this->db->select('st.image');
                $this->db->from('as_solenoid s');
                $this->db->join('as_solenoid_types st', 's.type = st.type', 'left');
                $this->db->where('s.solenoid_id', $base_type);
                $this->db->limit(1);
                $query = $this->db->get();
                if ($query->num_rows() > 0) {
                    $result = $query->row_array();
                    $type_image = $result['image'];
                }
            } elseif ($item['category'] === 'manifold') {
                // Manifold doesn't have type images, use default
                $type_image = null;
            }

            $item['type_image'] = $type_image;
        }

        return $storage_items;
    }
}
