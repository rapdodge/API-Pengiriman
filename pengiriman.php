<?php
date_default_timezone_set('Asia/Jakarta');
error_reporting(0);
$Kurir = strtolower($_GET['kurir']);
$Resi = $_GET['resi'];

if ($Kurir == 'jne') {
	$curl = curl_init();

	curl_setopt_array(
		$curl,
		array(
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
		)
	);

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
				'status' => ' | ' . $ResponcURL['cnote']['pod_status'],
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

	curl_setopt_array(
		$curl,
		array(
			CURLOPT_URL => "https://api.anteraja.id/order/tracking",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => "[{\"codes\":\"$Resi\"}]",
			CURLOPT_HTTPHEADER => array(
				"mv: 1.2",
				"source: aca_android",
				"content-type: application/json; charset=UTF-8",
				"user-agent: okhttp/3.10.0",
			),
		)
	);

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
} elseif ($Kurir == 'jnt') {
	$curl = curl_init();

	curl_setopt_array(
		$curl,
		array(
			CURLOPT_URL => "http://jk.jet.co.id:22234/jandt-app-ifd-web/router.do",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => array(
				'method' => 'order.massOrderTrack',
				'format' => 'json',
				'v' => '1.0',
				'data' => "{\"parameter\":{\"billCodes\":\"$Resi\",\"lang\":\"en\"}}"
			),
		)
	);

	$ResponcURL = json_decode(curl_exec($curl), true);

	curl_close($curl);

	$DecodeData = json_decode($ResponcURL['data'], true);
	$CekResi = array();

	if ($DecodeData['bills'][0]['details'] == NULL) {
		$CekResi['name'] = 'JNT';
		$CekResi['site'] = 'jet.co.id';
		$CekResi['error'] = true;
		$CekResi['message'] = 'Nomor resi tidak ditemukan.';
		print_r(json_encode($CekResi));
	} else {
		$CekResi['name'] = 'JNT';
		$CekResi['site'] = 'jet.co.id';
		$CekResi['error'] = false;
		$CekResi['message'] = 'success';

		$Keterangan = array(
			'info' => array(
				'no_awb' => $DecodeData['bills'][0]['billCode'],
				'service' => null,
				'status' => ' | ' . strtoupper($DecodeData['bills'][0]['status']),
				'tanggal_kirim' => null,
				'tanggal_terima' => null,
				'harga' => null,
				'berat' => null,
				'catatan' => null,
			),
		);

		$Pengirim = array(
			'pengirim' => array(
				'nama' => null,
				'phone' => null,
				'alamat' => null,
			),
		);

		$Penerima = array(
			'penerima' => array(
				'nama' => null,
				'nama_penerima' => null,
				'phone' => null,
				'alamat' => null,
			),
		);

		$BalikRiwayat = array_reverse($DecodeData['bills'][0]['details']);
		$Riwayat = array();
		foreach ($BalikRiwayat as $k => $v) {
			$Riwayat[$k]['tanggal'] = date('d-m-Y H:i', strtotime($BalikRiwayat[$k]['acceptTime']));
			if ($BalikRiwayat[$k]['scanstatus'] == 'Delivered') {
				$Riwayat[$k]['posisi'] = $BalikRiwayat[$k]['city'];
				$Riwayat[$k]['message'] = 'DELIVERED';
			} elseif ($BalikRiwayat[$k]['scanstatus'] == 'On Delivery') {
				$Riwayat[$k]['posisi'] = $BalikRiwayat[$k]['city'];
				$Riwayat[$k]['message'] = strtoupper($BalikRiwayat[$k]['scanstatus']);
			} elseif ($BalikRiwayat[$k]['scanstatus'] == 'Departed') {
				$Riwayat[$k]['posisi'] = $BalikRiwayat[$k]['city'];
				$Riwayat[$k]['message'] = strtoupper($BalikRiwayat[$k]['scanstatus']);
			} else {
				$Riwayat[$k]['posisi'] = $BalikRiwayat[$k]['city'];
				$Riwayat[$k]['message'] = strtoupper($BalikRiwayat[$k]['scanstatus']) . ' AT ' . strtoupper($BalikRiwayat[$k]['siteName']);
			}
		}

		$HasilRiwayat = array(
			'history' => $Riwayat,
		);

		$Hasil = array_merge($CekResi, $Keterangan, $Pengirim, $Penerima, $HasilRiwayat);
		print_r(json_encode($Hasil));
	}
} elseif ($Kurir == 'sicepat') {
	$curl = curl_init();

	curl_setopt_array(
		$curl,
		array(
			CURLOPT_URL => "http://api.sicepat.com/customer/waybill?waybill=$Resi",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"api-key: 48cd408f0f2f2e3872ec81a958483cb0"
			),
		)
	);

	$ResponcURL = json_decode(curl_exec($curl), true);

	curl_close($curl);

	$CekResi = array();

	if ($ResponcURL['sicepat']['status']['code'] != 200) {
		$CekResi['name'] = 'SiCepat';
		$CekResi['site'] = 'sicepat.com';
		$CekResi['error'] = true;
		$CekResi['message'] = 'Nomor resi tidak ditemukan.';
		print_r(json_encode($CekResi));
	} else {
		$CekResi['name'] = 'SiCepat';
		$CekResi['site'] = 'sicepat.com';
		$CekResi['error'] = false;
		$CekResi['message'] = 'success';

		$Keterangan = array(
			'info' => array(
				'no_awb' => $ResponcURL['sicepat']['result']['waybill_number'],
				'service' => $ResponcURL['sicepat']['result']['service'],
				'status' => ' | ' . strtoupper($ResponcURL['sicepat']['result']['last_status']['status']),
				'tanggal_kirim' => $ResponcURL['sicepat']['result']['send_date'],
				'tanggal_terima' => $ResponcURL['sicepat']['result']['POD_receiver_time'],
				'harga' => $ResponcURL['sicepat']['result']['totalprice'],
				'berat' => $ResponcURL['sicepat']['result']['weight'],
				'catatan' => null,
			),
		);

		$Pengirim = array(
			'pengirim' => array(
				'nama' => $ResponcURL['sicepat']['result']['sender'],
				'phone' => null,
				'alamat' => $ResponcURL['sicepat']['result']['sender_address'],
			),
		);

		$PecahPenerima0 = preg_split('/[\[\]]/', $ResponcURL['sicepat']['result']['last_status']['receiver_name']);
		$PecahPenerima1 = explode(' - ', $PecahPenerima0[1]);

		$Penerima = array(
			'penerima' => array(
				'nama' => $ResponcURL['sicepat']['result']['receiver_name'],
				'nama_penerima' => $PecahPenerima1 = rtrim(reset($PecahPenerima1)),
				'phone' => null,
				'alamat' => $ResponcURL['sicepat']['result']['receiver_address'],
			),
		);

		$Riwayat = array();
		foreach ($ResponcURL['sicepat']['result']['track_history'] as $k => $v) {
			$Riwayat[$k]['tanggal'] = date('d-m-Y H:i', strtotime($ResponcURL['sicepat']['result']['track_history'][$k]['date_time']));
			if ($ResponcURL['sicepat']['result']['track_history'][$k]['status'] == 'DELIVERED') {
				$Riwayat[$k]['posisi'] = 'Diterima';
				$Riwayat[$k]['message'] = $ResponcURL['sicepat']['result']['track_history'][$k]['receiver_name'];
			} else {
				$Riwayat[$k]['posisi'] = preg_replace('/(.*)\[(.*)\](.*)/', '$2', $ResponcURL['sicepat']['result']['track_history'][$k]['city']);
				if (strpos($ResponcURL['sicepat']['result']['track_history'][$k]['city'], 'SIGESIT') !== false) {
					$Riwayat[$k]['posisi'] = 'Diantar';
				}
				$Riwayat[$k]['message'] = $ResponcURL['sicepat']['result']['track_history'][$k]['city'];
			}
		}

		$HasilRiwayat = array(
			'history' => $Riwayat,
		);

		$Hasil = array_merge($CekResi, $Keterangan, $Pengirim, $Penerima, $HasilRiwayat);
		print_r(json_encode($Hasil));
	}
}
