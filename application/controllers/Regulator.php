<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Regulator extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Regulator_model');
        $this->load->helper(['common', 'url', 'date']);
        $this->load->library(['pagination', 'session']);

        // Check if user is logged in
        check_user_authentication();
    }

    public function index()
    {
        $config = [
            'base_url' => site_url('regulator/index'),
            'total_rows' => $this->Regulator_model->countRegulator(),
            'per_page' => 10,
            'uri_segment' => 3,
            'num_links' => 5,
            'use_page_numbers' => TRUE,
        ];

        $this->pagination->initialize($config);

        $page = $this->uri->segment(3) ? $this->uri->segment(3) : 1;
        $offset = ($page - 1) * $config['per_page'];

        // Get filter parameters
        $filter_type = $this->input->get('type');
        $search = $this->input->get('search');
        $sort_by = $this->input->get('sort_by') ?? 'updated_at';
        $sort_order = $this->input->get('sort_order') ?? 'DESC';

        $data = [
            'regulators' => $this->Regulator_model->getRegulatorFilter(
                $config['per_page'],
                $offset,
                $filter_type,
                $search,
                $sort_by,
                $sort_order
            ),
            'pagination' => $this->pagination->create_links(),
            'total_rows' => $config['total_rows'],
            'per_page' => $config['per_page'],
            'current_page' => $page,
            'filter_type' => $filter_type,
            'search' => $search,
            'sort_by' => $sort_by,
            'sort_order' => $sort_order,
            'title' => 'Kelola Regulator'
        ];

        render_view('regulator/index', $data);
    }

    public function add()
    {
        $data['title'] = 'Tambah Regulator';

        if ($this->input->method() === 'post') {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('type', 'Type', 'required|trim|max_length[20]');
            $this->form_validation->set_rules('min_stock', 'Minimum Stock', 'required|integer|greater_than_equal_to[0]');

            if ($this->form_validation->run() === FALSE) {
                render_view('regulator/add', $data);
                return;
            }

            $type = $this->input->post('type');

            // Generate regulator_id
            $regulator_id = $this->Regulator_model->generateRegulatorId($type);

            // Check if combination already exists
            if ($this->Regulator_model->check_regulator_combination($type)) {
                set_message('error', 'Regulator dengan type tersebut sudah ada.');
                render_view('regulator/add', $data);
                return;
            }

            $regulator_data = [
                'regulator_id' => $regulator_id,
                'type' => $type,
                'min_stock' => $this->input->post('min_stock')
            ];

            if ($this->Regulator_model->addRegulator($regulator_data)) {
                set_message('success', 'Regulator berhasil ditambahkan.');
                redirect('regulator');
            } else {
                set_message('error', 'Gagal menambahkan regulator.');
                render_view('regulator/add', $data);
            }
        } else {
            render_view('regulator/add', $data);
        }
    }

    public function edit($regulator_id)
    {
        $data['regulator'] = $this->Regulator_model->getRegulator($regulator_id);

        if (!$data['regulator']) {
            set_message('error', 'Regulator tidak ditemukan.');
            redirect('regulator');
            return;
        }

        $data['title'] = 'Edit Regulator';

        if ($this->input->method() === 'post') {
            $this->load->library('form_validation');

            $this->form_validation->set_rules('type', 'Type', 'required|trim|max_length[20]');
            $this->form_validation->set_rules('min_stock', 'Minimum Stock', 'required|integer|greater_than_equal_to[0]');

            if ($this->form_validation->run() === FALSE) {
                render_view('regulator/edit', $data);
                return;
            }

            $type = $this->input->post('type');

            // Generate new regulator_id if type changed
            $new_regulator_id = $this->Regulator_model->generateRegulatorId($type);

            // Check if combination already exists (excluding current regulator)
            if ($type !== $data['regulator']['type']) {
                if ($this->Regulator_model->check_regulator_combination($type)) {
                    set_message('error', 'Regulator dengan type tersebut sudah ada.');
                    render_view('regulator/edit', $data);
                    return;
                }
            }

            $regulator_data = [
                'regulator_id' => $new_regulator_id,
                'type' => $type,
                'min_stock' => $this->input->post('min_stock')
            ];

            if ($this->Regulator_model->editRegulator($regulator_id, $regulator_data)) {
                set_message('success', 'Regulator berhasil diperbarui.');
                redirect('regulator');
            } else {
                set_message('error', 'Gagal memperbarui regulator.');
                render_view('regulator/edit', $data);
            }
        } else {
            render_view('regulator/edit', $data);
        }
    }

    public function delete($regulator_id)
    {
        if ($this->Regulator_model->deleteRegulator($regulator_id)) {
            set_message('success', 'Regulator berhasil dihapus.');
        } else {
            set_message('error', 'Gagal menghapus regulator. Mungkin masih ada di storage.');
        }
        redirect('regulator');
    }

    public function download()
    {
        $regulators = $this->Regulator_model->getAllRegulator();

        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=regulator_data_' . date('YmdHis') . '.csv');

        $output = fopen('php://output', 'w');

        // Add BOM for UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Add CSV headers
        fputcsv($output, ['Regulator ID', 'Type', 'Min Stock', 'Created At', 'Updated At']);

        // Add data rows
        foreach ($regulators as $regulator) {
            fputcsv($output, [
                $regulator['regulator_id'],
                $regulator['type'],
                $regulator['min_stock'],
                $regulator['created_at'],
                $regulator['updated_at']
            ]);
        }

        fclose($output);
        exit;
    }

    public function downloadPDF()
    {
        $regulators = $this->Regulator_model->getAllRegulator();

        // Load TCPDF library
        require_once(APPPATH . '../vendor/autoload.php');

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Air System');
        $pdf->SetTitle('Data Regulator');
        $pdf->SetSubject('Regulator List');

        $pdf->SetHeaderData('', 0, 'Data Regulator', date('Y-m-d H:i:s'));
        $pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
        $pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->AddPage();

        $html = '<h1>Data Regulator</h1>';
        $html .= '<table border="1" cellpadding="4">';
        $html .= '<thead><tr style="background-color:#f0f0f0;">
                    <th>Regulator ID</th>
                    <th>Type</th>
                    <th>Min Stock</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                  </tr></thead><tbody>';

        foreach ($regulators as $regulator) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($regulator['regulator_id']) . '</td>';
            $html .= '<td>' . htmlspecialchars($regulator['type']) . '</td>';
            $html .= '<td>' . htmlspecialchars($regulator['min_stock']) . '</td>';
            $html .= '<td>' . htmlspecialchars($regulator['created_at']) . '</td>';
            $html .= '<td>' . htmlspecialchars($regulator['updated_at']) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('regulator_data_' . date('YmdHis') . '.pdf', 'D');
    }

    public function upload()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $config['upload_path'] = './uploads/';
        $config['allowed_types'] = 'csv';
        $config['max_size'] = 2048;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('csv_file')) {
            echo json_encode([
                'success' => false,
                'message' => $this->upload->display_errors('', '')
            ]);
            return;
        }

        $file_data = $this->upload->data();
        $file_path = $file_data['full_path'];

        $handle = fopen($file_path, 'r');
        if ($handle === false) {
            echo json_encode([
                'success' => false,
                'message' => 'Tidak dapat membuka file CSV.'
            ]);
            return;
        }

        // Skip header row
        fgetcsv($handle);

        $success_count = 0;
        $error_count = 0;
        $errors = [];

        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) < 2) {
                continue;
            }

            $type = trim($data[0]);
            $min_stock = isset($data[1]) ? intval($data[1]) : 5;

            if (empty($type)) {
                $error_count++;
                $errors[] = "Row skipped: empty type";
                continue;
            }

            // Generate regulator_id
            $regulator_id = $this->Regulator_model->generateRegulatorId($type);

            // Check if already exists
            if ($this->Regulator_model->check_regulator_combination($type)) {
                $error_count++;
                $errors[] = "Type '$type' already exists";
                continue;
            }

            $regulator_data = [
                'regulator_id' => $regulator_id,
                'type' => $type,
                'min_stock' => $min_stock
            ];

            if ($this->Regulator_model->addRegulator($regulator_data)) {
                $success_count++;
            } else {
                $error_count++;
                $errors[] = "Failed to add type '$type'";
            }
        }

        fclose($handle);
        unlink($file_path);

        echo json_encode([
            'success' => true,
            'message' => "Import selesai. Berhasil: $success_count, Gagal: $error_count",
            'details' => [
                'success' => $success_count,
                'error' => $error_count,
                'errors' => $errors
            ]
        ]);
    }

    public function download_template()
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=regulator_template.csv');

        $output = fopen('php://output', 'w');

        // Add BOM for UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Add CSV headers
        fputcsv($output, ['Type', 'Min Stock']);

        // Add example rows
        fputcsv($output, ['AR2000', '5']);
        fputcsv($output, ['AR3000', '10']);

        fclose($output);
        exit;
    }
}
