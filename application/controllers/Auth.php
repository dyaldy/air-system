<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Authentication controller for air-system.
 *
 * Minimal login page compatible with ASRS UI. Validates NIK against
 * entries in `user_model->getUserFilter('nik')` and sets `user_data` in session.
 */
class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_model');
    }

    public function index(): void
    {
        if ($this->session->userdata('user_data')) {
            redirect('user');
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
            redirect(base_url());
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
        // After successful login redirect the user to the user page
        redirect('user');
    }

    public function logout(): void
    {
        session_destroy();
        set_message(['success', 'Anda berhasil Logout']);
        redirect(base_url());
    }
}
