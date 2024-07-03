<?php
defined('BASEPATH') or exit('No direct script access allowed');
require 'vendor/autoload.php';

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
        $dataAbsensi = null;
        $nama_pegawai = $this->input->post('nama_pegawai');
        $absen_tahun = $this->input->post('absen_tahun');
        $absen_bulan = $this->input->post('absen_bulan');
    
        if (!empty($nama_pegawai)) {
            if (!empty($absen_tahun) && !empty($absen_bulan)) {
                $dataAbsensi = $this->db
                    ->like('tgl_absen', htmlspecialchars($absen_bulan, true))
                    ->like('tgl_absen', htmlspecialchars($absen_tahun, true))
                    ->where('kode_pegawai', htmlspecialchars($nama_pegawai, true))
                    ->order_by('nama_pegawai', 'ASC')
                    ->get('db_absensi')
                    ->result();
            } else if (!empty($absen_tahun) && empty($absen_bulan)) {
                $dataAbsensi = $this->db
                    ->like('tgl_absen', htmlspecialchars($absen_tahun, true))
                    ->where('kode_pegawai', htmlspecialchars($nama_pegawai, true))
                    ->order_by('nama_pegawai', 'ASC')
                    ->get('db_absensi')
                    ->result();
            }
        } else {
            $dataAbsensi = $this->db
                ->order_by('nama_pegawai', 'ASC')
                ->get('db_absensi')
                ->result();
        }
    
        // Export to Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
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
        $sheet->setCellValue('J1', 'Point Pegawai');
        $sheet->setCellValue('K1', 'Total Points');
    
        // Populate data
        $row = 2;
        $currentPegawai = '';
        $totalPoints = 0;
        $totalPerPegawai = [];
    
        foreach ($dataAbsensi as $index => $absen) {
            if ($currentPegawai != $absen->nama_pegawai) {
                if ($currentPegawai != '') {
                    // Add total points for previous pegawai
                    $sheet->setCellValue('K' . ($row), $totalPoints);
                    $row++; // Move to next row for the next pegawai
                }
                $currentPegawai = $absen->nama_pegawai;
                $totalPoints = 0; // Reset total points for the new pegawai
            }
    
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $absen->nama_pegawai);
            $sheet->setCellValue('C' . $row, $absen->tgl_absen);
            $sheet->setCellValue('D' . $row, $absen->jam_masuk);
            $sheet->setCellValue('E' . $row, $absen->jam_pulang);
            $sheet->setCellValue(
                'F' . $row,
                $absen->status_pegawai == 1 ? 'Sudah Absen' : ($absen->status_pegawai == 2 ? 'Absen Terlambat' : ($absen->status_pegawai == 3 ? 'Absen Izin' : 'Belum Absen'))
            );
            $sheet->setCellValue('G' . $row, $absen->keterangan_absen);
            $sheet->setCellValue('H' . $row, $absen->maps_absen);
            $sheet->setCellValue('I' . $row, $absen->kode_pegawai);
    
            // Calculate point
            $point = 0;
            if ($absen->status_pegawai == 1) {
                $point = 1;
            } elseif ($absen->status_pegawai == 2) {
                $point = 0.5;
            } elseif ($absen->status_pegawai == 3) {
                $point = -1;
            }
            $sheet->setCellValue('J' . $row, $point);
            $totalPoints += $point;
    
            $row++;
        }
    
        // Add total points for the last pegawai
        if ($currentPegawai != '') {
            $sheet->setCellValue('K' . ($row), $totalPoints);
        }
    
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = "absensipegawai_" . time() . "_export.xlsx";
    
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
    }
    
    
}
