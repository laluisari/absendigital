<div class="container-fluid">
    <h1 class="my-4"><span class="fas fa-file mr-2"></span>Export Absensi</h1>
    <div class="row mb-4">
        <div class="col-xl-6">
            <?= $this->session->flashdata('exportinfo'); ?>
            <div class="card mb-4">
                <div class="card-body">
                    <?= form_open('exportexcel'); ?>
                    <div class="form-group row">
                        <label for="nama_pegawai" class="col-sm-4 col-form-label">Nama Pegawai</label>
                        <div class="col-sm-8">
                            <select class="form-control" id="nama_pegawai" name="nama_pegawai">
                                <option value="" disabled selected>Pilih Nama Pegawai</option>
                                <?php foreach ($users as $employee) : ?>
                                    <option value="<?= $employee->kode_pegawai; ?>"><?= $employee->username; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="absen_tahun" class="col-sm-4 col-form-label">Tahun Absen</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="absen_tahun" name="absen_tahun" readonly>
                            <?= form_error('absen_tahun'); ?>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="absen_bulan" class="col-sm-4 col-form-label">Bulan Absen</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="absen_bulan" name="absen_bulan" readonly>
                            <?= form_error('absen_bulan'); ?>
                        </div>
                    </div>

                    <div class="form-group row justify-content-end">
                        <div class="col-sm-10">
                            <button type="submit" class="btn btn-primary"><span class="fas fa-file mr-1"></span>Export</button>
                        </div>
                    </div>
                    <?= form_close(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
