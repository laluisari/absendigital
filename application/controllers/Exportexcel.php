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
        $totalPoints = 0;
        $lastPegawai = '';
        $lastBulan = '';
        $pointsPerUser = [];
    
        foreach ($dataAbsensi as $index => $absen) {
            // Memisahkan tanggal untuk mendapatkan bulan
            $tgl_absen = explode(',', $absen->tgl_absen);
            $tanggal = explode(' ', trim($tgl_absen[1]));
            $bulan = $tanggal[1];
            $namaPegawai = $absen->nama_pegawai;
    
            // Jika nama pegawai atau bulan berbeda, tambahkan baris kosong
            if ($namaPegawai !== $lastPegawai || $bulan !== $lastBulan) {
                if ($index > 0) {
                    $row++;
                }
                $lastPegawai = $namaPegawai;
                $lastBulan = $bulan;
            }
    
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $absen->nama_pegawai);
            $sheet->setCellValue('C' . $row, $absen->tgl_absen);
            $sheet->setCellValue('D' . $row, $absen->jam_masuk);
            $sheet->setCellValue('E' . $row, $absen->jam_pulang);
            $sheet->setCellValue(
                'F' . $row,
                $absen->status_pegawai == 1 ? 'Sudah Absen' : ($absen->status_pegawai == 2 ? 'Absen Terlambat' : ($absen->status_pegawai == 3 ? 'Sakit' : ($absen->status_pegawai == 4 ? 'Izin' : 'Belum Absen')))
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
    
            // Add points to total points per user and per month
            if (!isset($pointsPerUser[$namaPegawai])) {
                $pointsPerUser[$namaPegawai] = [];
            }
            if (!isset($pointsPerUser[$namaPegawai][$bulan])) {
                $pointsPerUser[$namaPegawai][$bulan] = 0;
            }
            $pointsPerUser[$namaPegawai][$bulan] += $point;
    
            $totalPoints += $point;
    
            // Jika data terakhir dari user dan bulan, tambahkan total points
            if ($index + 1 === count($dataAbsensi) || $dataAbsensi[$index + 1]->nama_pegawai !== $namaPegawai || explode(' ', trim(explode(',', $dataAbsensi[$index + 1]->tgl_absen)[1]))[1] !== $bulan) {
                $sheet->setCellValue('K' . $row, $pointsPerUser[$namaPegawai][$bulan]);
            }
    
            $row++;
        }
    
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = "absensipegawai_" . time() . "_export.xlsx";
    
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
    }
    
    
    
}
