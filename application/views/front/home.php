<div class="container-fluid">
    <div class="mt-4 jumbotron jumbotron-fluid shadow-lg">
        <div class="container">
            <div class="text-center">
                <img src="<?= base_url('public/uploads/logo_instansi/' . $dataapp['logo_instansi']); ?>" class="card-img" style="width:15%;">
                <h1 class="display-5">
                    <?= (empty($dataapp['nama_instansi'])) ? '[Nama Instansi Belum Disetting]' : $dataapp['nama_instansi']; ?>
                </h1>
                <h4>
                    <div class="d-inline"><?= $greeting ?></div>, <?= $user['nama_lengkap'] ?>
                </h4>
                <p class="lead">
                    <marquee width="60%" direction="left"><?= (empty($dataapp['jumbotron_lead_set'])) ? '[Ubah Kalimat Pada Teks Ini Disetting Aplikasi]' : $dataapp['jumbotron_lead_set']; ?></marquee>
                </p>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-xl-7">
            <div class="card mb-4">
                <div class="card-header text-center">
                    <span class="fas fa-user mr-1"></span>Identitas Diri

                </div>
                <div class="card-body">
                    <div class="row detail">
                        <div class="col-md-2 col-sm-4 col-6 p-2">
                            <img class="img-thumbnail" src="<?= base_url('public/uploads/profile/' . $user['image']); ?>" class="card-img" style="width:100%;">
                        </div>
                        <div class="col-md-10 col-sm-8 col-6">
                            <dl class="row">
                                <dt class="col-sm-5">Nama Lengkap:</dt>
                                <dd class="col-sm-7"><?= $user['nama_lengkap'] ?></dd>
                                <dt class="col-sm-5">Umur:</dt>
                                <dd class="col-sm-7"><?= $user['umur'] ?><div class="ml-1 d-inline">Tahun</div>
                                </dd>
                                <dt class="col-sm-5">Instansi:</dt>
                                <dd class="col-sm-7 text-truncate"><?= $user['instansi'] ?></dd>
                                <dt class="col-sm-5">Jabatan:</dt>
                                <dd class="col-sm-7"><?= $user['jabatan'] ?></dd>
                                <dt class="col-sm-5">NPWP:</dt>
                                <dd class="col-sm-7"><?= $user['npwp'] ?></dd>
                                <dt class="col-sm-5">Tempat / Tanggal Lahir:</dt>
                                <dd class="col-sm-7"><?= $user['tempat_lahir'] ?>,<?= $user['tgl_lahir'] ?></dd>
                                <dt class="col-sm-5">Jenis Kelamin:</dt>
                                <dd class="col-sm-7"><?= $user['jenis_kelamin'] ?></dd>
                                <dt class="col-sm-5">Shift Bekerja:</dt>
                                <dd class="col-sm-7"><?= $shiftpegawai = ($user['bagian_shift'] == 1) ? '<span class="badge badge-success">Full Time</span>' : (($user['bagian_shift'] == 2) ? '<span class="badge badge-warning">Part Time</span>' : '<span class="badge badge-primary">Shift Time</span>'); ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Kode Pegawai: <?= $user['kode_pegawai'] ?></div>
                        <div class="text-muted">Akun Dibuat: <?= date('d F Y', $user['date_created']); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card mb-4">
                <div class="card-header text-center"><span class="fas fa-clock mr-1"></span>Absensi</div>
                <div class="card-body text-center">
                    <div id="infoabsensi"></div>
                    <?php if ($dataapp['maps_use'] == 1) : ?>
                        <div id='maps-absen' style='width: 100%; height:250px;'></div>
                        <hr>
                    <?php endif; ?>
                    <div id="location-maps" style="display: none;"></div>
                    <div id="date-and-clock">
                        <h3 id="clocknow"></h3>
                        <h3 id="datenow"></h3>
                    </div>

                    <?= form_dropdown('ket_absen', ['Bekerja Di Kantor' => 'Bekerja Di Kantor', 'Bekerja Di Rumah / WFH' => 'Bekerja Di Rumah / WFH', 'Sakit' => 'Sakit', 'Izin' => 'Izin'], '', ['class' => 'form-control align-content-center my-2', 'id' => 'ket_absen']); ?>
                    <div class="mt-2">
                        <div id="func-absensi">
                            <p class="font-weight-bold">Status Kehadiran:
                                <?php
                                $statuspegawai = '';

                                if (empty($dbabsensi['status_pegawai'])) {
                                    $statuspegawai = '<span class="badge badge-primary">Belum Absen</span>';
                                } else {
                                    switch ($dbabsensi['status_pegawai']) {
                                        case 1:
                                            $statuspegawai = '<span class="badge badge-success">Sudah Absen</span>';
                                            break;
                                        case 3:
                                            $statuspegawai = '<span class="badge badge-success">Sakit</span>';
                                            break;
                                        case 4:
                                            $statuspegawai = '<span class="badge badge-warning">Absen Izin</span>';
                                            break;
                                        default:
                                            $statuspegawai = '<span class="badge badge-danger">Absen Terlambat</span>';
                                    }
                                }

                                echo $statuspegawai;
                                ?>
                            </p>

                            <div id="jamabsen">
                                <p>Waktu Datang: <?= $jammasuk = (empty($dbabsensi['jam_masuk'])) ? '00:00:00' : $dbabsensi['jam_masuk']; ?></p>
                                <p>Waktu Pulang: <?= $jammasuk = (empty($dbabsensi['jam_pulang'])) ? '00:00:00' : $dbabsensi['jam_pulang']; ?></p>
                            </div>
                        </div>

                        <?php
                        $clocknow = date("H:i:s");
                        $sudahAbsen = false; 
                        
                        if (isset($dbabsensi['status_pegawai'], $dataapp['absen_pulang'])) {
                            $sudahAbsen = $dbabsensi['status_pegawai'] == 1 && strtotime($dataapp['absen_pulang']) <= strtotime($clocknow);
                        }
                        ?>

                        <?php if ($sudahAbsen) : ?>
                            <button class="btn btn-dark" id="btn-absensi">absen pulang</button>
                        <?php endif; ?>

                        <?php if (empty($dbabsensi['jam_masuk'])) : ?>
                            <button class="btn btn-dark" id="btn-absensi">absen</button>
                        <?php endif; ?>

                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="text-muted">Absen Datang Jam: <?= $dataapp['absen_mulai'] ?></div>
                        <div class="text-muted">Absen Pulang Jam: <?= $dataapp['absen_pulang']; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal QR Code -->
<div class="modal fade" id="qrcodemodal" tabindex="-1" role="dialog" aria-labelledby="qrcodemodal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-center" id="qrcodemodallabel"><span class="fas fa-qrcode mr-1"></span>QR Code Pegawai</h5>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <img class="img my-2" src="<?= $img_source = ($user['qr_code_image'] == 'no-qrcode.png' ? base_url('assets/img/no-qrcode.png') : base_url('storage/qrcode_pegawai/' . $user['qr_code_image'])); ?>" style="width:50%;">
                </div>
                <dl class="row">
                    <dt class="col-sm-5">Nama Lengkap:</dt>
                    <dd class="col-sm-7"><?= $user['nama_lengkap'] ?></dd>
                    <dt class="col-sm-5">NPWP:</dt>
                    <dd class="col-sm-7"><?= $user['npwp'] ?></dd>
                    <dt class="col-sm-5">Kode Pegawai:</dt>
                    <dd class="col-sm-7"><?= $user['kode_pegawai'] ?></dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>