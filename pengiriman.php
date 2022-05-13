<?php
date_default_timezone_set('Asia/Jakarta');
error_reporting(0);
header('Content-Type: application/json');

$JasaPengiriman = array(
	'rekomtoped'
);

$Kurir = strtolower($_GET['kurir']);
$Resi = $_GET['resi'];

if ($Kurir == null && $Resi == null) {
	$CekResi = array();
	$CekResi['name'] = null;
	$CekResi['site'] = null;
	$CekResi['error'] = true;
	$CekResi['message'] = 'Anda belum memasukkan jasa pengiriman & resi!';
	print_r(json_encode($CekResi));
}

elseif ($Kurir == null && $Resi != null) {
	$CekResi = array();
	$CekResi['name'] = null;
	$CekResi['site'] = null;
	$CekResi['error'] = true;
	$CekResi['message'] = 'Anda hanya memasukkan resi saja, mohon tambahkan jasa pengiriman!';
	print_r(json_encode($CekResi));
}

elseif (in_array($Kurir, $JasaPengiriman) && $Resi == null) {
	$CekResi = array();
	$CekResi['name'] = null;
	$CekResi['site'] = null;
	$CekResi['error'] = true;
	$CekResi['message'] = 'Anda hanya memasukkan jasa pengiriman saja, mohon tambahkan resi!';
	print_r(json_encode($CekResi));
}

elseif ($Kurir == 'rekomtoped') {
	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_URL => "https://orchestra.tokopedia.com/orc/v1/microsite/tracking?airwaybill=$Resi",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "GET",
		CURLOPT_HTTPHEADER => array(
			"origin: https://www.tokopedia.com"
		) ,
	));

	$ResponcURL = json_decode(curl_exec($curl) , true);
	curl_close($curl);

	$CekResi = array();

	if ($ResponcURL['data'][0]['airwaybill'] == null) {
		$CekResi['name'] = 'Kurir Rekomendasi Tokopedia';
		$CekResi['site'] = 'tokopedia.com';
		$CekResi['error'] = true;
		$CekResi['message'] = 'Nomor resi tidak ditemukan.';
		print_r(json_encode($CekResi));
	}

	else {
		$CekResi['name'] = 'Kurir Rekomendasi Tokopedia';
		$CekResi['site'] = 'tokopedia.com';
		$CekResi['error'] = false;
		$CekResi['message'] = 'success';

		$Keterangan = array(
			'info' => array(
				'no_awb' => $ResponcURL['data'][0]['airwaybill'],
				'service' => $ResponcURL['data'][0]['service'],
				'status' => strtoupper($ResponcURL['data'][0]['status']) ,
				'tanggal_kirim' => null,
				'tanggal_terima' => null,
				'harga' => null,
				'berat' => null,
				'catatan' => null,
			) ,
		);

		$Pengirim = array(
			'pengirim' => array(
				'nama' => $ResponcURL['data'][0]['seller']['name'],
				'phone' => null,
				'alamat' => $ResponcURL['data'][0]['seller']['address'],
			) ,
		);

		$Penerima = array(
			'penerima' => array(
				'nama' => $ResponcURL['data'][0]['buyer']['name'],
				'nama_penerima' => null,
				'phone' => null,
				'alamat' => $ResponcURL['data'][0]['buyer']['address'],
			) ,
		);

		$Riwayat = array();
		foreach ($ResponcURL['data'][0]['tracking_data'] as $k => $v) {
			$Riwayat[$k]['tanggal'] = $ResponcURL['data'][0]['tracking_data'][$k]['tracking_time'];
			$Riwayat[$k]['posisi'] = null;
			$Riwayat[$k]['message'] = $ResponcURL['data'][0]['tracking_data'][$k]['message'];
		}

		$BalikRiwayat = array_reverse($Riwayat);

		$HasilRiwayat = array(
			'history' => $BalikRiwayat,
		);

		$Hasil = array_merge($CekResi, $Keterangan, $Pengirim, $Penerima, $HasilRiwayat);
		print_r(json_encode($Hasil));
	}
}

else {
	$CekResi = array();
	$CekResi['name'] = null;
	$CekResi['site'] = null;
	$CekResi['error'] = true;
	$CekResi['message'] = 'Jasa pengiriman belum didukung!';
	print_r(json_encode($CekResi));
}
