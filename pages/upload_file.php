<?php
// Upload File (dulunya Cost Control) - login & hak akses sudah dicek di index.php


$uploadDir = "uploads/file/";
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}
if (!file_exists($uploadDir . '.htaccess')) {
    @file_put_contents($uploadDir . '.htaccess',
        "# dibuat otomatis: matikan eksekusi script di folder upload\n" .
        "Options -ExecCGI -Indexes\n" .
        "RemoveHandler .php .phtml .php3 .php4 .php5 .php7 .php8 .phar\n" .
        "<FilesMatch \"\\.(php[0-9]?|phtml|phar|pl|py|cgi|sh)$\">\n" .
        "    Require all denied\n" .
        "</FilesMatch>\n");
}

$pesanError = array();
$pesanSuccess = "";


function cc_can_upload()
{
    // Hanya pengelola (ADMIN, KEPALA MADRASAH, WAKIL KEPALA, TATA USAHA) yang boleh upload/hapus.
    // Semua peran boleh melihat & mengunduh.
    return in_array(role(), mgmtRoles(), true);
}

// Tampilkan DATE (Y-m-d) sebagai "Oktober 2026"
function cc_month_label($date)
{
    if (empty($date) || $date == '0000-00-00') {
        return '-';
    }
    $bulan = array(1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
    $ts = strtotime($date);
    return $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
}

/* ==========================================================
   HELPER ZIP
   $files = array( array('path/di/server', 'nama di dalam zip'), ... )
   ========================================================== */
function cc_send_zip($files, $zipName)
{
    if (!class_exists('ZipArchive')) {
        return false;
    }
    $tmp = tempnam(sys_get_temp_dir(), 'ccz');
    $zip = new ZipArchive();
    if ($zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        return false;
    }
    $used = array();
    $added = 0;
    foreach ($files as $f) {
        $name = $f[1];
        if (isset($used[$name])) {
            $info = pathinfo($name);
            $name = $info['filename'] . '_' . $used[$name] . (isset($info['extension']) ? '.' . $info['extension'] : '');
            $used[$f[1]]++;
        } else {
            $used[$name] = 1;
        }
        $zip->addFile($f[0], $name);
        $added++;
    }
    $zip->close();

    if ($added == 0) {
        @unlink($tmp);
        return false;
    }
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipName . '"');
    header('Content-Length: ' . filesize($tmp));
    header('Pragma: no-cache');
    readfile($tmp);
    unlink($tmp);
    exit;
}

/* ==========================================================
   DOWNLOAD ZIP - harus sebelum output HTML
   (boleh diakses semua divisi)
   ========================================================== */

// (1) Download semua file dalam satu riwayat -> dari tombol Download di list
if (isset($_GET['dl'])) {
    $costId = (int) $_GET['dl'];
    $files = array();
    $q = mysqli_query($koneksidb, "SELECT file_name, original_name FROM master_cost_control_file WHERE cost_id = $costId ORDER BY id");
    while ($r = mysqli_fetch_array($q)) {
        if (is_file($uploadDir . $r['file_name'])) {
            $files[] = array($uploadDir . $r['file_name'], $r['original_name']);
        }
    }
    if (count($files) == 1) {
        // hanya 1 file: langsung kirim file-nya, tidak perlu ZIP
        $path = $files[0][0];
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . str_replace('"', '', $files[0][1]) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }
    if (!cc_send_zip($files, "Upload_File_" . $costId . "_" . date('Ymd_His') . ".zip")) {
        $pesanError[] = "Tidak ada file yang dapat didownload.";
    }
}

// (2) Download file terpilih dari halaman Detail
if (isset($_POST['btnDownloadSelected'])) {
    $costId = isset($_POST['costId']) ? (int) $_POST['costId'] : 0;
    $selected = isset($_POST['selectedFiles']) && is_array($_POST['selectedFiles']) ? $_POST['selectedFiles'] : array();
    $files = array();
    foreach ($selected as $fid) {
        $fid = (int) $fid;
        // pastikan file memang milik riwayat tersebut
        $q = mysqli_query($koneksidb, "SELECT file_name, original_name FROM master_cost_control_file WHERE id = $fid AND cost_id = $costId");
        if ($q && ($r = mysqli_fetch_array($q)) && is_file($uploadDir . $r['file_name'])) {
            $files[] = array($uploadDir . $r['file_name'], $r['original_name']);
        }
    }
    if (!cc_send_zip($files, "Upload_File_" . $costId . "_" . date('Ymd_His') . ".zip")) {
        $pesanError[] = "Tidak ada file yang dapat didownload.";
    }
}

include "header.php";

$canUpload = cc_can_upload();
$canDelete = $canUpload;

$detailId = isset($_GET['detail']) ? (int) $_GET['detail'] : 0;

/* ==========================================================
   UPLOAD MULTIPLE -> 1 riwayat
   ========================================================== */
// Deteksi jika upload gagal total karena ukuran request melebihi post_max_size server
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES) && isset($_SERVER['CONTENT_LENGTH']) && (int)$_SERVER['CONTENT_LENGTH'] > 0) {
    $postMax = ini_get('post_max_size');
    $contentLenMB = round(((int)$_SERVER['CONTENT_LENGTH']) / (1024 * 1024), 1);
    $pesanError[] = "Total ukuran file ({$contentLenMB} MB) melebihi batas request server (post_max_size: {$postMax}). Silakan kurangi ukuran file atau naikkan setting post_max_size di server.";
}

if (isset($_POST['btnUpload'])) {
    if (!$canUpload) {
        $pesanError[] = "Anda tidak memiliki akses untuk upload.";
    } else {
        $noteRaw = isset($_POST['txtNote']) ? trim($_POST['txtNote']) : '';
        $noteEsc = mysqli_real_escape_string($koneksidb, $noteRaw);

        // input bulan (Y-m) disimpan sebagai tanggal 1 pada bulan tersebut
        $docMonth = isset($_POST['txtMonth']) ? $_POST['txtMonth'] : '';
        $dt = DateTime::createFromFormat('!Y-m', $docMonth);
        $docDateValid = ($dt && $dt->format('Y-m') === $docMonth);
        $docDateEsc = $docDateValid ? $docMonth . '-01' : '';

        $updatedBy = mysqli_real_escape_string($koneksidb, ($_SESSION['SES_NAMA'] ?? $_SESSION['SES_USER_NAME'] ?? 'user'));
        $updatedDate = date('Y-m-d H:i:s');
        // semua format boleh, kecuali file script/executable di server
        $blockedExt = array('php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar', 'pht', 'phps',
                            'cgi', 'pl', 'py', 'sh', 'exe', 'bat', 'cmd', 'com', 'msi', 'htaccess', 'htpasswd');

        if ($noteRaw === '') {
            $pesanError[] = "Keterangan / Note wajib diisi.";
        } elseif (!$docDateValid) {
            $pesanError[] = "Bulan tidak valid.";
        } elseif (!isset($_FILES['fileExcel']) || !is_array($_FILES['fileExcel']['name'])) {
            $pesanError[] = "Pilih file yang akan diupload.";
        } else {
            // buat 1 riwayat dulu
            $ins = mysqli_query($koneksidb, "INSERT INTO master_cost_control (note, doc_date, updated_by, updated_date)
                                             VALUES ('$noteEsc', '$docDateEsc', '$updatedBy', '$updatedDate')");
            if (!$ins) {
                $pesanError[] = "Gagal menyimpan ke database.";
            } else {
                $costId = mysqli_insert_id($koneksidb);
                $berhasil = 0;
                $total = count($_FILES['fileExcel']['name']);

                for ($i = 0; $i < $total; $i++) {
                    if ($_FILES['fileExcel']['error'][$i] == UPLOAD_ERR_NO_FILE) {
                        continue;
                    }
                    $fileName = $_FILES['fileExcel']['name'][$i];
                    $fileTmp  = $_FILES['fileExcel']['tmp_name'][$i];
                    $fileErr  = $_FILES['fileExcel']['error'][$i];
                    $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                    if ($fileErr != UPLOAD_ERR_OK) {
                        switch ($fileErr) {
                            case UPLOAD_ERR_INI_SIZE:
                                $pesanError[] = "$fileName : ukuran file melebihi batas maksimal server (upload_max_filesize: " . ini_get('upload_max_filesize') . ").";
                                break;
                            case UPLOAD_ERR_FORM_SIZE:
                                $pesanError[] = "$fileName : ukuran file melebihi batas form HTML.";
                                break;
                            case UPLOAD_ERR_PARTIAL:
                                $pesanError[] = "$fileName : file hanya terupload sebagian (koneksi terputus).";
                                break;
                            case UPLOAD_ERR_NO_TMP_DIR:
                                $pesanError[] = "$fileName : folder temporary server tidak ditemukan.";
                                break;
                            case UPLOAD_ERR_CANT_WRITE:
                                $pesanError[] = "$fileName : gagal menulis file ke disk server (disk penuh / permission).";
                                break;
                            default:
                                $pesanError[] = "$fileName : terjadi kesalahan saat upload (kode $fileErr).";
                                break;
                        }
                        continue;
                    }
                    // cek semua bagian nama (mis. "a.php.jpg" juga ditolak)
                    $parts = explode('.', strtolower($fileName));
                    array_shift($parts);
                    $isBlocked = (count(array_intersect($parts, $blockedExt)) > 0)
                              || (strtolower(trim($fileName, '.')) === 'htaccess');
                    if ($isBlocked) {
                        $pesanError[] = "$fileName : jenis file ini tidak diperbolehkan.";
                        continue;
                    }

                    $newFileName = time() . "_" . $costId . "_" . $i . "_" . preg_replace("/[^a-zA-Z0-9.-]/", "_", $fileName);
                    if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                        $sqlFile = "INSERT INTO master_cost_control_file (cost_id, file_name, original_name)
                                    VALUES ($costId, '" . mysqli_real_escape_string($koneksidb, $newFileName) . "', '" . mysqli_real_escape_string($koneksidb, $fileName) . "')";
                        if (mysqli_query($koneksidb, $sqlFile)) {
                            $berhasil++;
                        } else {
                            unlink($uploadDir . $newFileName);
                            $pesanError[] = "$fileName : gagal menyimpan ke database.";
                        }
                    } else {
                        $pesanError[] = "$fileName : gagal mengupload file.";
                    }
                }

                if ($berhasil > 0) {
                    $pesanSuccess = "$berhasil file berhasil diupload.";
                } else {
                    // tidak ada file yang masuk: batalkan riwayat kosong
                    mysqli_query($koneksidb, "DELETE FROM master_cost_control WHERE id = $costId");
                    if (count($pesanError) == 0) {
                        $pesanError[] = "Pilih file yang akan diupload.";
                    }
                }
            }
        }
    }
}

/* ==========================================================
   DELETE
   ========================================================== */
if (isset($_GET['action']) && in_array($_GET['action'], array('delete', 'delfile'))) {
    if (!$canDelete) {
        $pesanError[] = "Anda tidak memiliki akses untuk menghapus.";
    } elseif ($_GET['action'] == 'delete' && isset($_GET['id'])) {
        // hapus 1 riwayat beserta semua filenya
        $costId = (int) $_GET['id'];
        $q = mysqli_query($koneksidb, "SELECT file_name FROM master_cost_control_file WHERE cost_id = $costId");
        while ($r = mysqli_fetch_array($q)) {
            $p = $uploadDir . basename($r['file_name']);
            if (is_file($p)) {
                unlink($p);
            }
        }
        mysqli_query($koneksidb, "DELETE FROM master_cost_control_file WHERE cost_id = $costId");
        if (mysqli_query($koneksidb, "DELETE FROM master_cost_control WHERE id = $costId")) {
            $pesanSuccess = "Riwayat berhasil dihapus.";
        } else {
            $pesanError[] = "Gagal menghapus data dari database.";
        }
    } elseif ($_GET['action'] == 'delfile' && isset($_GET['fid']) && $detailId > 0) {
        // hapus 1 file di dalam riwayat
        $fid = (int) $_GET['fid'];
        $q = mysqli_query($koneksidb, "SELECT file_name FROM master_cost_control_file WHERE id = $fid AND cost_id = $detailId");
        if ($q && ($r = mysqli_fetch_array($q))) {
            $p = $uploadDir . basename($r['file_name']);
            if (is_file($p)) {
                unlink($p);
            }
            mysqli_query($koneksidb, "DELETE FROM master_cost_control_file WHERE id = $fid");
            $pesanSuccess = "File berhasil dihapus.";

            // jika file habis, hapus riwayatnya juga
            $c = mysqli_fetch_array(mysqli_query($koneksidb, "SELECT COUNT(*) AS n FROM master_cost_control_file WHERE cost_id = $detailId"));
            if ($c['n'] == 0) {
                mysqli_query($koneksidb, "DELETE FROM master_cost_control WHERE id = $detailId");
                $detailId = 0;
                $pesanSuccess = "File terakhir dihapus, riwayat ikut dihapus.";
            }
        }
    }
}

/* data riwayat untuk halaman detail */
$detailData = null;
if ($detailId > 0) {
    $q = mysqli_query($koneksidb, "SELECT * FROM master_cost_control WHERE id = $detailId");
    $detailData = $q ? mysqli_fetch_array($q) : null;
    if (!$detailData) {
        $pesanError[] = "Data riwayat tidak ditemukan.";
        $detailId = 0;
    }
}
?>

<div class="right_col" role="main">
    <div class="">
        <div class="page-title">
            <div class="title_left">
                <h3>Upload File <small><?php echo $detailId > 0 ? 'Detail File' : 'Riwayat Upload File'; ?></small></h3>
            </div>
        </div>

        <div class="clearfix"></div>

        <?php
        if (count($pesanError) > 0) {
            echo '<div class="alert alert-danger alert-dismissible fade in" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>';
            foreach ($pesanError as $pesan_tampil) {
                echo htmlspecialchars($pesan_tampil) . "<br>";
            }
            echo '</div>';
        }
        if ($pesanSuccess != "") {
            echo '<div class="alert alert-success alert-dismissible fade in" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                    ' . htmlspecialchars($pesanSuccess) . '
                  </div>';
        }
        ?>

<?php if ($detailId > 0) { ?>
        <!-- ================= DETAIL ================= -->
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Info Riwayat</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <?php
                        $tdL = 'border:0 !important; background:transparent !important; text-align:left !important; padding:4px 0 !important; vertical-align:top; font-weight:bold; width:150px; white-space:nowrap;';
                        $tdV = 'border:0 !important; background:transparent !important; text-align:left !important; padding:4px 0 !important; vertical-align:top; font-weight:normal;';
                        ?>
                        <table style="border:0; width:auto; margin-bottom:15px; background:transparent;">
                            <tr><td style="<?php echo $tdL; ?>">Keterangan</td><td style="<?php echo $tdV; ?>">: <?php echo nl2br(htmlspecialchars($detailData['note'])); ?></td></tr>
                            <tr><td style="<?php echo $tdL; ?>">Bulan</td><td style="<?php echo $tdV; ?>">: <?php echo cc_month_label($detailData['doc_date']); ?></td></tr>
                            <tr><td style="<?php echo $tdL; ?>">Diupdate Oleh</td><td style="<?php echo $tdV; ?>">: <?php echo htmlspecialchars($detailData['updated_by']); ?></td></tr>
                            <tr><td style="<?php echo $tdL; ?>">Tanggal Update</td><td style="<?php echo $tdV; ?>">: <?php echo date('d-m-Y H:i', strtotime($detailData['updated_date'])); ?></td></tr>
                        </table>
                        <a href="?page=Upload-File" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Kembali</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>File dalam Riwayat Ini</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content table-responsive">
                        <form method="POST" action="?page=Upload-File&detail=<?php echo $detailId; ?>" id="formDownload">
                            <input type="hidden" name="costId" value="<?php echo $detailId; ?>">
                            <div style="margin-bottom:10px;">
                                <button type="submit" name="btnDownloadSelected" id="btnDownloadSelected" class="btn btn-primary btn-sm" disabled>
                                    <i class="fa fa-download"></i> Download Terpilih (ZIP) <span id="selCount" class="badge">0</span>
                                </button>
                            </div>

                            <table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th width="30" class="text-center"><input type="checkbox" id="chkAll"></th>
                                        <th width="30">No</th>
                                        <th>Nama File</th>
                                        <th>Ukuran</th>
                                        <th width="200" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $qf = mysqli_query($koneksidb, "SELECT * FROM master_cost_control_file WHERE cost_id = $detailId ORDER BY id");
                                    $nomor = 0;
                                    while ($f = mysqli_fetch_array($qf)) {
                                        $nomor++;
                                        $fileUrl = $uploadDir . $f['file_name'];
                                        $size = is_file($fileUrl) ? round(filesize($fileUrl) / 1024, 1) . ' KB' : 'file tidak ditemukan';
                                    ?>
                                    <tr>
                                        <td class="text-center"><input type="checkbox" class="chk-file" value="<?php echo (int) $f['id']; ?>"></td>
                                        <td><?php echo $nomor; ?></td>
                                        <td><?php echo htmlspecialchars($f['original_name']); ?></td>
                                        <td><?php echo $size; ?></td>
                                        <td class="text-center">
                                            <a href="<?php echo htmlspecialchars($fileUrl); ?>" download="<?php echo htmlspecialchars($f['original_name']); ?>" class="btn btn-primary btn-xs"><i class="fa fa-download"></i> Download</a>
                                            <?php if ($canDelete) { ?>
                                            <a href="?page=Upload-File&detail=<?php echo $detailId; ?>&action=delfile&fid=<?php echo (int) $f['id']; ?>" class="btn btn-danger btn-xs" onclick="return confirm('Hapus file ini?');"><i class="fa fa-trash"></i> Hapus</a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>

<?php } else { ?>

        <?php if ($canUpload) { ?>
        <!-- ================= UPLOAD (pengelola) ================= -->
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Upload File</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <form method="POST" action="?page=Upload-File" enctype="multipart/form-data" class="form-horizontal form-label-left">
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="fileExcel">Pilih File <span class="required">*</span></label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input type="file" id="fileExcel" name="fileExcel[]" required="required" multiple class="form-control col-md-7 col-xs-12">
                                    <div id="fileList" style="margin-top:8px;"></div>
                                    <div style="margin-top:5px;">
                                        <small class="text-muted"><i class="fa fa-info-circle"></i> Limit server saat ini: Max per file <strong><?php echo ini_get('upload_max_filesize'); ?></strong>, Max total request <strong><?php echo ini_get('post_max_size'); ?></strong></small>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="txtNote">Keterangan / Note <span class="required">*</span></label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <textarea id="txtNote" name="txtNote" required="required" class="form-control col-md-7 col-xs-12" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="txtMonth">Bulan <span class="required">*</span></label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input type="month" id="txtMonth" name="txtMonth" required="required" class="form-control col-md-7 col-xs-12" value="<?php echo date('Y-m'); ?>">
                                </div>
                            </div>
                            <div class="ln_solid"></div>
                            <div class="form-group">
                                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                    <button type="submit" name="btnUpload" class="btn btn-success"><i class="fa fa-upload"></i> Upload</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>

        <!-- ================= RIWAYAT ================= -->
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Riwayat Upload File</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content table-responsive">
                        <table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th width="30">No</th>
                                    <th>Keterangan</th>
                                    <th>Bulan</th>
                                    <th>Diupdate Oleh</th>
                                    <th>Tanggal Update</th>
                                    <th width="250" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $mySql = "SELECT c.*, (SELECT COUNT(*) FROM master_cost_control_file f WHERE f.cost_id = c.id) AS jml
                                          FROM master_cost_control c
                                          ORDER BY c.updated_date DESC, c.id DESC";
                                $myQry = mysqli_query($koneksidb, $mySql);
                                $nomor = 0;
                                while ($myData = mysqli_fetch_array($myQry)) {
                                    $nomor++;
                                ?>
                                <tr>
                                    <td><?php echo $nomor; ?></td>
                                    <td><?php echo htmlspecialchars($myData['note']); ?></td>
                                    <td><?php echo cc_month_label($myData['doc_date']); ?></td>
                                    <td><?php echo htmlspecialchars($myData['updated_by']); ?></td>
                                    <td><?php echo date('d-m-Y H:i', strtotime($myData['updated_date'])); ?></td>
                                    <td class="text-center">
                                        <a href="?page=Upload-File&dl=<?php echo (int) $myData['id']; ?>" class="btn btn-primary btn-xs"><i class="fa fa-download"></i> Download</a>
                                        <a href="?page=Upload-File&detail=<?php echo (int) $myData['id']; ?>" class="btn btn-info btn-xs"><i class="fa fa-eye"></i> Detail</a>
                                        <?php if ($canDelete) { ?>
                                        <a href="?page=Upload-File&action=delete&id=<?php echo (int) $myData['id']; ?>" class="btn btn-danger btn-xs" onclick="return confirm('Hapus riwayat ini beserta semua filenya?');"><i class="fa fa-trash"></i> Hapus</a>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
<?php } ?>

    </div>
</div>

<?php
include "footer.php";
?>

<script>
$(function () {
    // daftar file yang dipilih di form upload
    $('#fileExcel').on('change', function () {
        var html = '';
        var totalBytes = 0;
        function formatBytes(bytes) {
            if (bytes >= 1048576) {
                return (bytes / 1048576).toFixed(2) + ' MB';
            }
            return (bytes / 1024).toFixed(1) + ' KB';
        }

        $.each(this.files, function (i, f) {
            totalBytes += f.size;
            html += '<div><i class="fa fa-file-excel-o text-success"></i> ' + $('<span>').text(f.name).html() +
                    ' <small class="text-muted">(' + formatBytes(f.size) + ')</small></div>';
        });

        if (this.files.length > 1) {
            html += '<div style="margin-top:4px; font-weight:bold; border-top:1px dashed #ccc; padding-top:4px;">Total: ' + formatBytes(totalBytes) + ' (' + this.files.length + ' file)</div>';
        }
        $('#fileList').html(html);
    });

    <?php if ($detailId > 0) { ?>
    // ambil semua checkbox di semua halaman DataTables
    function allChecks() {
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#datatable-responsive')) {
            return $('#datatable-responsive').DataTable().$('input.chk-file');
        }
        return $('input.chk-file');
    }

    function updateState() {
        var checks = allChecks();
        var n = checks.filter(':checked').length;
        $('#selCount').text(n);
        $('#btnDownloadSelected').prop('disabled', n === 0);
        $('#chkAll').prop('checked', checks.length > 0 && n === checks.length);
    }

    $('#chkAll').on('change', function () {
        allChecks().prop('checked', this.checked);
        updateState();
    });

    $(document).on('change', 'input.chk-file', updateState);

    // sertakan file terpilih dari semua halaman saat submit
    $('#formDownload').on('submit', function () {
        var form = this;
        $(form).find('input.hidden-sel').remove();
        allChecks().filter(':checked').each(function () {
            $('<input>', {type: 'hidden', name: 'selectedFiles[]', value: this.value, class: 'hidden-sel'}).appendTo(form);
        });
        $(form).find('input.chk-file').prop('disabled', true);
        setTimeout(function () { $(form).find('input.chk-file').prop('disabled', false); }, 500);
    });
    <?php } ?>
});
</script>