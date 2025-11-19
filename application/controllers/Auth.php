<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Authentication controller for air-system.
 *
 * Handles user authentication and login functionality. Validates NIK against
 * user database entries and manages user session data. Compatible with ASRS UI patterns.
 *
 * @package AirSystem
 * @subpackage Controllers
 * @category Authentication
 * @author Apparel One Indonesia
 * @version 1.0.0
 * @property CI_Session $session
 * @property CI_Input $input
 * @property CI_Form_validation $form_validation
 * @property User_model $user_model
 */
class Auth extends CI_Controller
{
    /**
     * Constructor for Auth controller.
     *
     * Initializes the controller by loading required models and helpers.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->helper('common');
    }

    public function index(): void
    {
        if ($this->session->userdata('user_data')) {
            redirect('home');
        }

        // air-system does not use machine registration. Skip machine checks.

        $this->load->library('form_validation');


        // Validate NIK exists and format. We will validate NIK against DB via getByNik in _login.
        $config = [
            [
                'field' => 'nik',
                'label' => 'NIK',
                'rules' => 'required|numeric|exact_length[9]',
                'errors' => [
                    'required' => 'Masukkan %s',
                    'numeric' => '%s tidak valid',
                    'exact_length' => '%s tidak valid',
                ],
            ],
        ];

        $this->form_validation->set_rules($config);

        if (!$this->form_validation->run()) {
            $this->load->view('auth/index');
        } else {
            $this->_login();
        }
    }

    private function _login(): void
    {
        $nik = $this->input->post('nik', true);
        $userDetail = $this->user_model->getByNik((int) $nik);
        if (!$userDetail) {
            set_message(['danger', 'NIK tidak terdaftar']);
            redirect('auth');
            return;
        }

        $userData = [
            'nik' => $userDetail['nik'],
            'name' => $userDetail['name'],
        ];

        $data = [
            'user_data' => $userData,
            'machine' => null,
        ];

        $this->session->set_userdata($data);
        // After successful login redirect the user to the home page
        redirect('home');
    }

    public function logout(): void
    {
        session_destroy();
        set_message(['success', 'Anda berhasil Logout']);
        redirect('auth');
    }
}
