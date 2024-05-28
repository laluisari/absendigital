<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_Settings extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->appsetting = $this->db->get_where('db_setting', ['status_setting' => 1])->row_array();
    }

    public function init_setting($typeinit)
    {
        if ($typeinit == 1) {

            $data = [
                'status_setting' => 1,
                'nama_instansi' => '[Ubah Nama Instansi]',
                'jumbotron_lead_set' => '[Ubah Text Berjalan Halaman Depan Disini Pada Setting Aplikasi]',
                'nama_app_absensi' => 'Absensi Online',
                'logo_instansi' => 'default-logo.png',
                'timezone' => 'Asia/Jakarta',
                'absen_mulai' => '06:00:00',
                'absen_mulai_to' => '11:00:00',
                'absen_pulang' => '16:00:00',
                'maps_use' => 0
            ];
            $old_image = $this->appsetting['logo_instansi'];
            if ($old_image != 'default-logo.png') {
                unlink(FCPATH . 'storage/setting/' . $old_image);
            }
            $this->db->get_where('db_setting', ['status_setting' => 1])->row_array();
            $this->db->update('db_setting', $data);
            $this->db->update('user', ['instansi' => '[Ubah Nama Instansi]']);
        } elseif ($typeinit == 2) {
            $data = [
                'status_setting' => 1,
                'nama_instansi' => '[Ubah Nama Instansi]',
                'jumbotron_lead_set' => '[Ubah Text Berjalan Halaman Depan Disini Pada Setting Aplikasi]',
                'nama_app_absensi' => 'Absensi Online',
                'logo_instansi' => 'default-logo.png',
                'timezone' => 'Asia/Jakarta',
                'absen_mulai' => '06:00:00',
                'absen_mulai_to' => '11:00:00',
                'absen_pulang' => '16:00:00',
                'maps_use' => 0
            ];
            $this->db->insert('db_setting', $data);
        }
    }
    public function update_setting() 
    { 
        // Data to be saved
        $sendsave = [
            'nama_instansi' => htmlspecialchars($this->input->post('nama_instansi')),
            'jumbotron_lead_set' =>  htmlspecialchars($this->input->post('pesan_jumbotron')),
            'nama_app_absensi' =>  htmlspecialchars($this->input->post('nama_app_absen')),
            'timezone' =>  htmlspecialchars($this->input->post('timezone_absen')),
            'absen_mulai' =>  htmlspecialchars($this->input->post('absen_mulai')),
            'absen_mulai_to' =>  htmlspecialchars($this->input->post('absen_sampai')),
            'absen_pulang' =>  htmlspecialchars($this->input->post('absen_pulang_sampai')),
            'maps_use' =>  htmlspecialchars($this->input->post('lokasi_absensi')),
            'latitude' =>  htmlspecialchars($this->input->post('latitude')),
            'longitude' =>  htmlspecialchars($this->input->post('longitude')),
        ];

        // Image upload logic
        $upload_image = $_FILES['logo_instansi']['name'];

        if ($upload_image) {
            $config['allowed_types'] = 'gif|jpg|png|jpeg|bmp';
            $config['max_size'] = '2048';
            $config['encrypt_name'] = TRUE;
            $config['upload_path'] = './public/uploads/logo_instansi/';

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('logo_instansi')) {
                $gbr = $this->upload->data();
                $new_image = $gbr['file_name'];
                $image_path = 'public/uploads/logo_instansi/' . $new_image;

                // Save the new image path to the database
                $this->db->set('logo_instansi', $new_image);
            } else {
                // Handle upload error (keep existing image if any)
                $existing_image = $this->db->get_where('db_setting', ['status_setting' => 1])->row('logo_instansi');
                if ($existing_image) {
                    $this->db->set('logo_instansi', $existing_image);
                } else {
                    $this->db->set('logo_instansi', 'default.png');
                }
            }
        } else {
            // If no image is uploaded, keep the current image or set to default
            $existing_image = $this->db->get_where('db_setting', ['status_setting' => 1])->row('logo_instansi');
            if ($existing_image) {
                $this->db->set('logo_instansi', $existing_image);
            } else {
                $this->db->set('logo_instansi', 'default.png');
            }
        }

        // Update settings in the database
        $this->db->where('status_setting', 1);
        $this->db->update('db_setting', $sendsave);

        // Update the user table with the new instansi name
        $this->db->update('user', ['instansi' => htmlspecialchars($this->input->post('nama_instansi'))]);
    }
}
