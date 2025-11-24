<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Home controller for air-system.
 *
 * Handles the main dashboard/beranda page and system overview. Displays
 * system statistics, activity trends, and low stock alerts for administrators.
 *
 * @package AirSystem
 * @subpackage Controllers
 * @category Home
 * @author Apparel One Indonesia
 * @version 1.0.0
 * @property CI_Session $session
 * @property CI_Input $input
 * @property User_model $user_model
 * @property Pneumatic_model $Pneumatic_model
 * @property Fitting_model $Fitting_model
 * @property Storage_model $Storage_model
 */
class Home extends CI_Controller
{
    /**
     * Constructor for Home controller.
     *
     * Initializes the controller by checking user authentication and loading
     * required models for dashboard data display.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Check user authentication using common helper
        check_user_authentication('auth');

        // Load models needed for dashboard data
        $this->load->model(['user_model', 'Pneumatic_model', 'Fitting_model', 'Storage_model']);
    }

    /**
     * Display the main beranda/dashboard page.
     *
     * Shows system overview including user counts, inventory statistics,
     * storage utilization, recent activities, and activity trends.
     *
     * @return void
     */
    public function index(): void
    {
        $data['title'] = 'Beranda - Air System';

        // Get basic statistics for the dashboard with error handling
        try {
            $data['total_users'] = $this->user_model->countUser() ?? 0;
        } catch (Exception $e) {
            $data['total_users'] = 0;
        }

        try {
            $data['total_pneumatics'] = $this->Pneumatic_model->countPneumatic() ?? 0;
        } catch (Exception $e) {
            $data['total_pneumatics'] = 0;
        }

        try {
            $data['total_fittings'] = $this->Fitting_model->countFitting() ?? 0;
        } catch (Exception $e) {
            $data['total_fittings'] = 0;
        }

        // Get total storage items (active storage locations)
        try {
            $data['total_storage_items'] = $this->Storage_model->countStorageLocations() ?? 0;
        } catch (Exception $e) {
            $data['total_storage_items'] = 0;
        }

        // Get low stock alerts
        try {
            $data['low_stock_items'] = $this->Storage_model->get_low_stock_items();
        } catch (Exception $e) {
            $data['low_stock_items'] = [];
        }

        // Get total quantity stored
        try {
            $this->db->select_sum('amount');
            $query = $this->db->get('as_storage');
            $result = $query->row_array();
            $data['total_quantity_stored'] = $result['amount'] ?? 0;
        } catch (Exception $e) {
            $data['total_quantity_stored'] = 0;
        }

        // Get storage distribution by category
        try {
            $this->db->select('category, SUM(amount) as total_amount');
            $this->db->group_by('category');
            $this->db->having('SUM(amount) >', 0);
            $query = $this->db->get('as_storage');
            $data['storage_by_category'] = $query->result_array();
        } catch (Exception $e) {
            $data['storage_by_category'] = [];
        }

        // Get today's activity count
        try {
            $today = date('Y-m-d');
            $this->db->select('COUNT(*) as today_activities');
            $this->db->from('as_report');
            $this->db->where('DATE(datetime)', $today);
            $query = $this->db->get();
            $result = $query->row_array();
            $data['today_activities'] = $result['today_activities'] ?? 0;
        } catch (Exception $e) {
            $data['today_activities'] = 0;
        }

        // Get system information
        $data['php_version'] = PHP_VERSION;
        $data['server_software'] = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
        $data['memory_usage'] = round(memory_get_peak_usage(true) / 1024 / 1024, 1); // MB

        // Get recent activities (last 5 from report table)
        try {
            $this->db->select('r.*, u.name as user_name');
            $this->db->from('as_report r');
            $this->db->join('as_user u', 'r.nik = u.nik', 'left');
            $this->db->order_by('r.datetime', 'DESC');
            $this->db->limit(5);
            $query = $this->db->get();
            $data['recent_activities'] = $query->result_array();
        } catch (Exception $e) {
            $data['recent_activities'] = [];
        }

        // Get activity trend for the last 7 days (separate store/retrieve with amounts and categories)
        try {
            $activity_trend = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-{$i} days"));

                // Get store activities with amounts and categories
                $this->db->select('COUNT(*) as store_count, SUM(amount) as store_amount, category');
                $this->db->from('as_report');
                $this->db->where('DATE(datetime)', $date);
                $this->db->where('action', 'store');
                $this->db->group_by('category');
                $store_query = $this->db->get();
                $store_results = $store_query->result_array();

                // Get retrieve activities with amounts and categories
                $this->db->select('COUNT(*) as retrieve_count, SUM(amount) as retrieve_amount, category');
                $this->db->from('as_report');
                $this->db->where('DATE(datetime)', $date);
                $this->db->where('action !=', 'store');
                $this->db->group_by('category');
                $retrieve_query = $this->db->get();
                $retrieve_results = $retrieve_query->result_array();

                // Calculate totals
                $total_store_count = 0;
                $total_store_amount = 0;
                $store_by_category = [];
                foreach ($store_results as $row) {
                    $total_store_count += (int)$row['store_count'];
                    $total_store_amount += (int)$row['store_amount'];
                    $store_by_category[$row['category']] = [
                        'count' => (int)$row['store_count'],
                        'amount' => (int)$row['store_amount']
                    ];
                }

                $total_retrieve_count = 0;
                $total_retrieve_amount = 0;
                $retrieve_by_category = [];
                foreach ($retrieve_results as $row) {
                    $total_retrieve_count += (int)$row['retrieve_count'];
                    $total_retrieve_amount += (int)$row['retrieve_amount'];
                    $retrieve_by_category[$row['category']] = [
                        'count' => (int)$row['retrieve_count'],
                        'amount' => (int)$row['retrieve_amount']
                    ];
                }

                $activity_trend[] = [
                    'date' => date('d/m', strtotime($date)),
                    'store' => $total_store_count,
                    'store_amount' => $total_store_amount,
                    'store_by_category' => $store_by_category,
                    'retrieve' => $total_retrieve_count,
                    'retrieve_amount' => $total_retrieve_amount,
                    'retrieve_by_category' => $retrieve_by_category,
                    'total' => $total_store_count + $total_retrieve_count
                ];
            }
            $data['activity_trend'] = $activity_trend;
        } catch (Exception $e) {
            $data['activity_trend'] = array_fill(0, 7, [
                'date' => '',
                'store' => 0,
                'store_amount' => 0,
                'store_by_category' => [],
                'retrieve' => 0,
                'retrieve_amount' => 0,
                'retrieve_by_category' => [],
                'total' => 0
            ]);
        }

        // Get user information
        $userData = $this->session->userdata('user_data');
        $data['user_name'] = $userData['name'] ?? 'Guest';
        $data['user_level'] = $userData['user_level'] ?? 'UNKNOWN';

        // Get current date and time in Indonesian format
        $months = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember'
        ];

        $day = date('d');
        $month = $months[date('m')];
        $year = date('Y');

        $data['current_date'] = "$day $month $year";
        $data['current_time'] = date('H:i:s');

        render_view('home/index', $data);
    }
}
