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
} elseif ($Kurir == 'anteraja') {
	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => "https://api.anteraja.id/order/tracking",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "POST",
	  CURLOPT_POSTFIELDS =>"[{\"codes\":\"$Resi\"}]",
	  CURLOPT_HTTPHEADER => array(
	    "mv: 1.2",
	    "source: aca_android",
	    "content-type: application/json; charset=UTF-8",
	    "user-agent: okhttp/3.10.0",
	  ),
	));

	$ResponcURL = json_decode(curl_exec($curl), true);

	curl_close($curl);
	
	if ($ResponcURL['content'][0]['detail']['final_status'] == 250) {
		$StatusKirim = ' | DELIVERED';
	} else {
		$StatusKirim = ' | ON PROCESS';
	}
	

	$CekResi = array();

	if ($ResponcURL['status'] != 200) {
		$CekResi['name'] = 'AnterAja';
		$CekResi['site'] = 'anteraja.id';
		$CekResi['error'] = true;
		$CekResi['message'] = 'Nomor resi tidak ditemukan.';
		print_r(json_encode($CekResi));
	} else {
		$CekResi['name'] = 'AnterAja';
		$CekResi['site'] = 'anteraja.id';
		$CekResi['error'] = false;
		$CekResi['message'] = 'success';

		$Keterangan = array(
			'info' => array(
				'no_awb' => $ResponcURL['content'][0]['awb'],
				'service' => $ResponcURL['content'][0]['detail']['service_code'],
				'status' => $StatusKirim,
				'tanggal_kirim' => date('d-m-Y H:i', strtotime($ResponcURL['content'][0]['detail']['shipped_date'])),
				'tanggal_terima' => date('d-m-Y H:i', strtotime($ResponcURL['content'][0]['detail']['delivered_date'])),
				'harga' => $ResponcURL['content'][0]['detail']['actual_amount'],
				'berat' => $ResponcURL['content'][0]['detail']['weight'],
				'catatan' => $ResponcURL['content'][0]['items'][0]['name'],
			),
		);

		$Pengirim = array(
			'pengirim' => array(
				'nama' => $ResponcURL['content'][0]['detail']['sender']['name'],
				'phone' => null,
				'alamat' => null,
			),
		);

		$Penerima = array(
			'penerima' => array(
				'nama' => $ResponcURL['content'][0]['detail']['receiver']['name'],
				'nama_penerima' => $ResponcURL['content'][0]['detail']['actual_receiver'],
				'phone' => null,
				'alamat' => null,
			),
		);

		$BalikRiwayat = array_reverse($ResponcURL['content'][0]['history']);
		$HitungRiwayat = count($BalikRiwayat);
		$Riwayat = array();
		foreach ($BalikRiwayat as $k => $v) {
			$Riwayat[$k]['tanggal'] = date('d-m-Y H:i', strtotime($BalikRiwayat[$k]['timestamp']));
			if (preg_match('/Delivery sukses/', $BalikRiwayat[$k]['message']['id'])) {
				$Riwayat[$k]['posisi'] = null;
				$Riwayat[$k]['message'] = 'DELIVERED';
			} else {
				$Riwayat[$k]['posisi'] = null;
				$Riwayat[$k]['message'] = $BalikRiwayat[$k]['message']['id'];
			}
			
		}

		$HasilRiwayat = array(
			'history' => $Riwayat,
		);

		$Hasil = array_merge($CekResi, $Keterangan, $Pengirim, $Penerima, $HasilRiwayat);
		print_r(json_encode($Hasil));
	}
}
