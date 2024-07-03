<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Docs extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        is_logged_in();
        is_moderator();
        $this->get_datasess = $this->db->get_where('user', ['username' =>
        $this->session->userdata('username')])->row_array();
        $this->load->model('M_Front');
        $this->get_datasetupapp = $this->M_Front->fetchsetupapp();
        $timezone_all = $this->get_datasetupapp;
        date_default_timezone_set($timezone_all['timezone']);
        $this->load->database();
    }

    //Fitur Print
    public function print()
    {
        if (!empty($this->input->get('id_absen'))) {
            $id_absen = $this->input->get('id_absen');
            $querydata = $this->db->get_where('db_absensi', ['id_absen' => $id_absen])->row_array();
            $data = [
                'dataapp' => $this->get_datasetupapp,
                'dataabsensi' => $querydata
            ];
            ob_clean();
            $mpdf = new \Mpdf\Mpdf();
            $html = $this->load->view('layout/dataabsensi/printselfabsensi', $data, true);
            //$pdfFilePath = "storage/pdf_cache/absensipegawai_" . time() . "_download.pdf";
            $stylesheet = file_get_contents(FCPATH . 'assets/css/mpdf-bootstrap.css');
            $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
            $mpdf->WriteHTML(utf8_encode($html), \Mpdf\HTMLParserMode::HTML_BODY);
            $mpdf->SetTitle('Cetak Absen Pegawai');
            //$mpdf->Output(FCPATH . $pdfFilePath, "F");
            $mpdf->Output("absensipegawai_" . time() . "_self" . "_download.pdf", "I");
        } else {
            redirect(base_url('absensi'));
        }
    }

    public function export()
    {
        $validation = [
            [
                'field' => 'absen_tahun',
                'label' => 'Tahun Absen',
                'rules' => 'trim|required|xss_clean',
                'errors' => ['required' => 'You must provide a %s.', 'xss_clean' => 'Please check your form on %s.']
            ],
            [
                'field' => 'absen_bulan',
                'label' => 'Bulan Absen',
                'rules' => 'trim|required|xss_clean',
                'errors' => ['required' => 'You must provide a %s.', 'xss_clean' => 'Please check your form on %s.']
            ],
            [
                'field' => 'method_export_file',
                'label' => 'Metode Export File',
                'rules' => 'trim|required|xss_clean',
                'errors' => ['required' => 'You must provide a %s.', 'xss_clean' => 'Please check your form on %s.']
            ]
        ];
        $this->form_validation->set_rules($validation);
        $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
        $dataUser = $this->db->get('user')->result();

        if ($this->form_validation->run() == FALSE) {
            $data = [
                'title' => 'Export Data',
                'user' => $this->get_datasess,
                'users' => $dataUser,
                'dataapp' => $this->get_datasetupapp
            ];
            $this->load->view('layout/header', $data);
            $this->load->view('layout/navbar', $data);
            $this->load->view('layout/sidebar', $data);
            $this->load->view('admin/exportfile', $data);
            $this->load->view('layout/footer', $data);
        }
    }
}
