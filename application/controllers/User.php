<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * User controller for air-system.
 *
 * Uses ASRS UI and documentation standards: `render_view()` helper for
 * header/footer, `set_message()` helper for flash UI messages, and the
 * standard PHPDoc header.
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
    public function __construct()
    {
        parent::__construct();

        // Guard: ensure authenticated session (ASRS canonical expectation)
        if (!$this->session->userdata('user_data')) {
            redirect(base_url());
        }

        $this->load->model('User_model');
        $this->load->library(['form_validation', 'pagination']);
    }

    /**
     * List users with pagination and optional search. Renders via render_view().
     */
    public function index()
    {
        // handle simple session state for search
        $keyword = $this->input->get('q', true) ?: $this->session->userdata('user_keyword');
        if ($this->input->get('q', true) !== null) {
            $this->session->set_userdata('user_keyword', $keyword);
        }

        $page = (int) $this->uri->segment(3, 0);
        $perPage = 10;

        $total = $this->User_model->countUser($keyword);

        $config = [
            'base_url' => site_url('user/index'),
            'total_rows' => $total,
            'per_page' => $perPage,
        ];
        $this->pagination->initialize($config);

        $users = $this->User_model->getUser($perPage, $page, $keyword);

        $data = [
            'title' => 'Users',
            'users' => $users,
            'pagination' => $this->pagination->create_links(),
            'keyword' => $keyword,
        ];

        render_view('user_list', $data);
    }

    /**
     * Show add form and process create.
     */
    public function add()
    {
        $this->form_validation->set_rules('nik', 'NIK', 'required|numeric');
        $this->form_validation->set_rules('name', 'Name', 'required');

        if ($this->form_validation->run() === false) {
            $data = ['title' => 'Add User', 'user' => null];
            render_view('user_form', $data);
            return;
        }

        $this->User_model->addUser();
        // ASRS helper for consistent flash messages
        set_message(['success', 'User added']);

        redirect('user');
    }

    /**
     * Show edit form and process update.
     * @param int $nik
     */
    public function edit($nik = null)
    {
        if ($nik === null) {
            show_404();
            return;
        }

        $user = $this->User_model->getByNik((int) $nik);
        if (!$user) {
            set_message(['danger', 'User not found']);
            redirect('user');
            return;
        }

        $this->form_validation->set_rules('name', 'Name', 'required');

        if ($this->form_validation->run() === false) {
            $data = ['title' => 'Edit User', 'user' => $user];
            render_view('user_form', $data);
            return;
        }

        $this->User_model->editUser((int) $nik);
        set_message(['success', 'User updated']);
        redirect('user');
    }

    /**
     * Delete user by NIK.
     * @param int $nik
     */
    public function delete($nik = null)
    {
        if ($nik === null) {
            show_404();
            return;
        }

        $this->User_model->deleteUser((int) $nik);
        set_message(['success', 'User deleted']);

        redirect('user');
    }
}
