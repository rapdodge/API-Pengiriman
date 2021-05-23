<?php
date_default_timezone_set('Asia/Jakarta');
error_reporting(0);
$Kurir = strtolower($_GET['kurir']);
$Resi = $_GET['resi'];

if ($Kurir == 'jne') {
	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => "http://apiv2.jne.co.id:10101/tracing/api/list/myjne/cnote/$Resi",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "POST",
	  CURLOPT_POSTFIELDS => "username=JNEONE&api_key=504fbae0d815bf3e73a7416be328fcf2",
	  CURLOPT_HTTPHEADER => array(
	    "Content-Type: application/x-www-form-urlencoded"
	  ),
	));

	$ResponcURL = json_decode(curl_exec($curl), true);

	curl_close($curl);

	$CekResi = array();

	if ($ResponcURL['error'] == 'Cnote No. Not Found.') {
		$CekResi['name'] = 'JNE';
		$CekResi['site'] = 'jne.co.id';
		$CekResi['error'] = true;
		$CekResi['message'] = 'Nomor resi tidak ditemukan.';
		print_r(json_encode($CekResi));
	} else {
		$CekResi['name'] = 'JNE';
		$CekResi['site'] = 'jne.co.id';
		$CekResi['error'] = false;
		$CekResi['message'] = 'success';

		$Keterangan = array(
			'info' => array(
				'no_awb' => $ResponcURL['cnote']['cnote_no'],
				'service' => $ResponcURL['cnote']['cnote_services_code'],
				'status' => ' | '.$ResponcURL['cnote']['pod_status'],
				'tanggal_kirim' => date('d-m-Y H:i', strtotime($ResponcURL['cnote']['cnote_date'])),
				'tanggal_terima' => date('d-m-Y H:i', strtotime($ResponcURL['cnote']['cnote_pod_date'])),
				'harga' => $ResponcURL['cnote']['shippingcost'],
				'berat' => $ResponcURL['cnote']['weight'],
				'catatan' => $ResponcURL['cnote']['cnote_goods_descr'],
			),
		);

		$Pengirim = array(
			'pengirim' => array(
				'nama' => $ResponcURL['detail'][0]['cnote_shipper_name'],
				'phone' => null,
				'alamat' => $ResponcURL['detail'][0]['cnote_shipper_city'],
			),
		);

		$Penerima = array(
			'penerima' => array(
				'nama' => $ResponcURL['cnote']['cnote_receiver_name'],
				'nama_penerima' => $ResponcURL['cnote']['cnote_pod_receiver'],
				'phone' => null,
				'alamat' => $ResponcURL['detail'][0]['cnote_receiver_city']
			),
		);

		$HitungRiwayat = count($ResponcURL['history']);
		$Riwayat = array();
		foreach ($ResponcURL['history'] as $k => $v) {
			$Riwayat[$k]['tanggal'] = date('d-m-Y H:i', strtotime($ResponcURL['history'][$k]['date']));
			$PecahRiwayat = preg_split('/[\[\]]/', $ResponcURL['history'][$k]['desc']);
			if (preg_match('/DELIVERED/', $ResponcURL['history'][$k]['desc'])) {
				$Pecah = explode(' | ', $PecahRiwayat[1]);
				$Riwayat[$k]['posisi'] = strtoupper(rtrim(end($Pecah)));
				$Riwayat[$k]['message'] = 'DELIVERED';
			} else {
				$Pecah = explode(' | ', $PecahRiwayat[1]);
				$Riwayat[$k]['posisi'] = strtoupper(rtrim(end($Pecah)));
				$Riwayat[$k]['message'] = rtrim(str_replace(' AT', '', $PecahRiwayat[0]));
			}
			
		}

		$HasilRiwayat = array(
			'history' => $Riwayat,
		);

		$Hasil = array_merge($CekResi, $Keterangan, $Pengirim, $Penerima, $HasilRiwayat);
		print_r(json_encode($Hasil));
	}
}
