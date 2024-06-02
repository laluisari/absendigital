<?php
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Exportexcel extends CI_Controller
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
    public function export()
    {
        $dataAbsensi = $this->db->get('db_absensi')->result();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Nama Pegawai');
        $sheet->setCellValue('C1', 'Tanggal Absen');
        $sheet->setCellValue('D1', 'Jam Datang');
        $sheet->setCellValue('E1', 'Jam Pulang');
        $sheet->setCellValue('F1', 'Status Kehadiran');
        $sheet->setCellValue('G1', 'Keterangan Absen');
        $sheet->setCellValue('H1', 'Titik Lokasi Maps');
        $sheet->setCellValue('I1', 'Kode Pegawai');

        // Populate data
        $row = 2;
        foreach ($dataAbsensi as $index => $absen) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $absen->nama_pegawai);
            $sheet->setCellValue('C' . $row, $absen->tgl_absen);
            $sheet->setCellValue('D' . $row, $absen->jam_masuk);
            $sheet->setCellValue('E' . $row, $absen->jam_pulang);
            $sheet->setCellValue('F' . $row, $absen->status_pegawai == 1 ? 'Sudah Absen' : ($absen->status_pegawai == 2 ? 'Absen Terlambat' : 'Belum Absen'));
            $sheet->setCellValue('G' . $row, $absen->keterangan_absen);
            $sheet->setCellValue('H' . $row, $absen->maps_absen);
            $sheet->setCellValue('I' . $row, $absen->kode_pegawai);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $filename = "absensipegawai_" . time() . "_export.xlsx";

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
    }
}
