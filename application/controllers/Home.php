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
        $this->load->helper('common');

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

        // Get low stock alerts
        try {
            $data['low_stock_items'] = $this->Storage_model->get_low_stock_items();
        } catch (Exception $e) {
            $data['low_stock_items'] = [];
        }

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

        // Get activity trend for the last 7 days (separate store/retrieve)
        try {
            $activity_trend = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-{$i} days"));

                // Count store activities
                $this->db->select('COUNT(*) as store_count');
                $this->db->from('as_report');
                $this->db->where('DATE(datetime)', $date);
                $this->db->where('action', 'store');
                $store_query = $this->db->get();
                $store_result = $store_query->row_array();

                // Count retrieve activities
                $this->db->select('COUNT(*) as retrieve_count');
                $this->db->from('as_report');
                $this->db->where('DATE(datetime)', $date);
                $this->db->where('action !=', 'store');
                $retrieve_query = $this->db->get();
                $retrieve_result = $retrieve_query->row_array();

                $activity_trend[] = [
                    'date' => date('d/m', strtotime($date)),
                    'store' => (int)($store_result['store_count'] ?? 0),
                    'retrieve' => (int)($retrieve_result['retrieve_count'] ?? 0),
                    'total' => (int)($store_result['store_count'] ?? 0) + (int)($retrieve_result['retrieve_count'] ?? 0)
                ];
            }
            $data['activity_trend'] = $activity_trend;
        } catch (Exception $e) {
            $data['activity_trend'] = array_fill(0, 7, ['date' => '', 'store' => 0, 'retrieve' => 0, 'total' => 0]);
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
