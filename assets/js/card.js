// Kartu ID dengan QR (dipakai di Data Guru / Data Siswa). Tombol: .btn-card dengan data-nama, data-sub, data-info, data-qr
$(document).on('click', '.btn-card', function () {
  var d = $(this).data();
  var t = function (s) { return $('<i>').text(s == null ? '' : s).html(); };
  Swal.fire({
    html: '<div style="text-align:left;padding:0;border:2px solid #16a34a;border-radius:12px;overflow:hidden">' +
      '<div style="background:#16a34a;color:#fff;padding:8px 14px;font-weight:700">MA Ghoyatul Jihad Karawang</div>' +
      '<div style="padding:12px 14px"><div style="font-size:18px;font-weight:700">' + t(d.nama) + '</div>' +
      '<div style="color:#555">' + t(d.sub) + '</div><div style="margin:4px 0 8px">' + t(d.info) + '</div>' +
      '<div id="qrBox" style="display:flex;justify-content:center;padding:6px"></div>' +
      '<div style="text-align:center;font-size:11px;color:#777">Scan untuk absen</div></div></div>',
    showConfirmButton: false, showCloseButton: true,
    didOpen: function () { new QRCode(document.getElementById('qrBox'), { text: String(d.qr), width: 150, height: 150 }); }
  });
});
