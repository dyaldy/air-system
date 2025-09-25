<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Home controller for air-system.
 *
 * Handles the main dashboard/beranda page and system overview.
 */
class Home extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('common');

        // Check if user is logged in, redirect to auth if not
        if (!$this->session->userdata('user_data')) {
            redirect('auth');
        }

        // Load models we might need for dashboard data
        $this->load->model(['user_model', 'Pneumatic_model', 'Fitting_model', 'Storage_model']);
    }

    /**
     * Display the main beranda/dashboard page
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

        try {
            $data['total_storage_items'] = $this->Storage_model->countStorage() ?? 0;
        } catch (Exception $e) {
            $data['total_storage_items'] = 0;
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
