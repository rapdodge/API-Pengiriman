<?php
date_default_timezone_set('Asia/Jakarta');
error_reporting(0);
header('Content-Type: application/json');
$JasaPengiriman = array('anteraja', 'jnt', 'jx', 'lionparcel', 'ninja', 'pos', 'sicepat', 'tiki', 'wahana');
$Kurir = strtolower($_GET['kurir']);
$Resi = $_GET['resi'];

if ($Kurir == null && $Resi == null) {
	$CekResi = array();
	$CekResi['name'] = null;
	$CekResi['site'] = null;
	$CekResi['error'] = true;
	$CekResi['message'] = 'Anda belum memasukkan jasa pengiriman & resi!';
	print_r(json_encode($CekResi));
} elseif ($Kurir == null && $Resi != null) {
	$CekResi = array();
	$CekResi['name'] = null;
	$CekResi['site'] = null;
	$CekResi['error'] = true;
	$CekResi['message'] = 'Anda hanya memasukkan resi saja, mohon tambahkan jasa pengiriman!';
	print_r(json_encode($CekResi));
} elseif (in_array($Kurir, $JasaPengiriman) && $Resi == null) {
	$CekResi = array();
	$CekResi['name'] = null;
	$CekResi['site'] = null;
	$CekResi['error'] = true;
	$CekResi['message'] = 'Anda hanya memasukkan jasa pengiriman saja, mohon tambahkan resi!';
	print_r(json_encode($CekResi));
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
				"user-agent: okhttp/3.10.0"
			),
		)
	);

	$ResponcURL = json_decode(curl_exec($curl), true);

	curl_close($curl);

	$Tgl_Kirim = end($ResponcURL['content'][0]['history']);
	$TanggalKirim = date('d-m-Y H:i', strtotime($Tgl_Kirim['timestamp']));

	$Tgl_Terima = reset($ResponcURL['content'][0]['history']);
	if (strpos($Tgl_Terima['message']['id'], 'Delivery sukses') !== false) {
		$TanggalTerima = date('d-m-Y H:i', strtotime($Tgl_Terima['timestamp']));
	} else {
		$TanggalTerima = null;
	}

	if ($ResponcURL['content'][0]['detail']['final_status'] == 250) {
		$StatusKirim = 'DELIVERED';
	} else {
		$StatusKirim = 'ON PROCESS';
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
				'tanggal_kirim' => $TanggalKirim,
				'tanggal_terima' => $TanggalTerima,
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
				$Riwayat[$k]['posisi'] = 'Diterima';
				$Riwayat[$k]['message'] = $BalikRiwayat[$k]['message']['id'];
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
				"method" => "order.massOrderTrack",
				"format" => "json",
				"v" => "1.0",
				"data" => "{\"parameter\":{\"billCodes\":\"$Resi\",\"lang\":\"en\"}}"
			),
		)
	);

	$ResponcURL = json_decode(curl_exec($curl), true);

	curl_close($curl);

	$DecodeData = json_decode($ResponcURL['data'], true);
	$Tgl_Terima = reset($DecodeData['bills'][0]['details']);

	if ($Tgl_Terima['scanstatus'] == 'Delivered') {
		$TanggalTerima = $Tgl_Terima['acceptTime'];
	} else {
		$TanggalTerima = null;
	}

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

		$Tgl_Kirim = end($DecodeData['bills'][0]['details']);

		$Keterangan = array(
			'info' => array(
				'no_awb' => $DecodeData['bills'][0]['billCode'],
				'service' => null,
				'status' => strtoupper($DecodeData['bills'][0]['status']),
				'tanggal_kirim' => date('d-m-Y H:i', strtotime($Tgl_Kirim['acceptTime'])),
				'tanggal_terima' => date('d-m-Y H:i', strtotime($TanggalTerima)),
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
				'status' => strtoupper($ResponcURL['sicepat']['result']['last_status']['status']),
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
				'nama_penerima' => reset($PecahPenerima1),
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
} elseif ($Kurir == 'jx') {
	$curl = curl_init();

	curl_setopt_array(
		$curl,
		array(
			CURLOPT_URL => "https://www.j-express.id/api/a_tracking",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => "_token=0&code=$Resi",
			CURLOPT_HTTPHEADER => array(
				"Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
				"Cookie: _token=0; ci_session=0; _token=282e385954029c985329489b833e3096; ci_session=htqrg3nagdckge43f6tr1do8batqlig3"
			),
		)
	);

	$ResponcURL = json_decode(curl_exec($curl), true);

	curl_close($curl);

	$CekResi = array();

	if ($ResponcURL['status'] != 'success') {
		$CekResi['name'] = 'JX';
		$CekResi['site'] = 'j-express.id';
		$CekResi['error'] = true;
		$CekResi['message'] = 'Nomor resi tidak ditemukan.';
		print_r(json_encode($CekResi));
	} else {
		$CekResi['name'] = 'JX';
		$CekResi['site'] = 'j-express.id';
		$CekResi['error'] = false;
		$CekResi['message'] = 'success';

		$Tgl_Kirim = end($ResponcURL['track']);
		$ArrayStatus = reset($ResponcURL['track']);

		if (strpos($ArrayStatus['state'], 'Pengiriman telah berhasil') !== false) {
			$StatusKirim = 'DELIVERED';
			$TanggalTerima = $ArrayStatus['times'];
		} else {
			$StatusKirim = 'ON PROCESS';
			$TanggalTerima = null;
		}

		$Keterangan = array(
			'info' => array(
				'no_awb' => $Resi,
				'service' => null,
				'status' => $StatusKirim,
				'tanggal_kirim' => date('d-m-Y H:i', strtotime($Tgl_Kirim['times'])),
				'tanggal_terima' => date('d-m-Y H:i', strtotime($TanggalTerima)),
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

		$BalikRiwayat = array_reverse($ResponcURL['track']);
		$Riwayat = array();
		foreach ($BalikRiwayat as $k => $v) {
			$Riwayat[$k]['tanggal'] = date('d-m-Y H:i', strtotime($BalikRiwayat[$k]['times']));
			if (strpos($ArrayStatus['state'], 'Pengiriman telah berhasil') !== false) {
				$Riwayat[$k]['posisi'] = null;
				$Riwayat[$k]['message'] = $BalikRiwayat[$k]['state'];
			} else {
				$Riwayat[$k]['posisi'] = null;
				$Riwayat[$k]['message'] = $BalikRiwayat[$k]['state'];
			}
		}

		$HasilRiwayat = array(
			'history' => $Riwayat,
		);

		$Hasil = array_merge($CekResi, $Keterangan, $Pengirim, $Penerima, $HasilRiwayat);
		print_r(json_encode($Hasil));
	}
} elseif ($Kurir == 'wahana') {
	$curl = curl_init();

	curl_setopt_array(
		$curl,
		array(
			CURLOPT_URL => "http://intranet.wahana.com/ci-oauth2/Api/trackingNew?access_token=093a64444fa19f591682f7087a5e5a08febd9e43&ttk=$Resi",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET"
		)
	);

	$ResponcURL = json_decode(curl_exec($curl), true);

	curl_close($curl);

	$CekResi = array();

	if ($ResponcURL['status'] != 'OK') {
		$CekResi['name'] = 'WAHANA';
		$CekResi['site'] = 'www.wahana.com';
		$CekResi['error'] = true;
		$CekResi['message'] = 'Nomor resi tidak ditemukan.';
		print_r(json_encode($CekResi));
	} else {
		$CekResi['name'] = 'WAHANA';
		$CekResi['site'] = 'www.wahana.com';
		$CekResi['error'] = false;
		$CekResi['message'] = 'success';

		$Tgl_Kirim = reset($ResponcURL['data']);
		$ArrayStatus = end($ResponcURL['data']);

		if (strpos($ArrayStatus['StatusInternal'], 'Terkirim') !== false) {
			$StatusKirim = 'DELIVERED';
			$TanggalTerima = $ArrayStatus['Tanggal'];
		} else {
			$StatusKirim = 'ON PROCESS';
			$TanggalTerima = null;
		}

		$Keterangan = array(
			'info' => array(
				'no_awb' => $ResponcURL['TTKNO'],
				'service' => null,
				'status' => $StatusKirim,
				'tanggal_kirim' => date('d-m-Y H:i', strtotime($Tgl_Kirim['Tanggal'])),
				'tanggal_terima' => date('d-m-Y H:i', strtotime($TanggalTerima)),
				'harga' => null,
				'berat' => null,
				'catatan' => null,
			),
		);

		$Pengirim = array(
			'pengirim' => array(
				'nama' => $ResponcURL['Pengirim'],
				'phone' => null,
				'alamat' => null,
			),
		);

		$Penerima = array(
			'penerima' => array(
				'nama' => $ResponcURL['Penerima'],
				'nama_penerima' => null,
				'phone' => null,
				'alamat' => $ResponcURL['Alamatpenerima'],
			),
		);

		$Riwayat = array();
		foreach ($ResponcURL['data'] as $k => $v) {
			$Riwayat[$k]['tanggal'] = date('d-m-Y H:i', strtotime($ResponcURL['data'][$k]['Tanggal']));
			if (strpos($ResponcURL['data'][$k]['StatusInternal'], 'Terkirim') !== false) {
				$Riwayat[$k]['posisi'] = $ResponcURL['data'][$k]['lokasicd'];
				$Riwayat[$k]['message'] = 'Diterima';
			} else {
				$Riwayat[$k]['posisi'] = $ResponcURL['data'][$k]['lokasicd'];
				$Riwayat[$k]['message'] = $ResponcURL['data'][$k]['TrackStatusNama'];
			}
		}

		$HasilRiwayat = array(
			'history' => $Riwayat,
		);

		$Hasil = array_merge($CekResi, $Keterangan, $Pengirim, $Penerima, $HasilRiwayat);
		print_r(json_encode($Hasil));
	}
} elseif ($Kurir == 'pos') {
	$curl = curl_init();

	curl_setopt_array(
		$curl,
		array(
			CURLOPT_URL => "https://order.posindonesia.co.id/api/lacak",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => "{\"barcode\":\"$Resi\"}",
			CURLOPT_HTTPHEADER => array(
				'content-type: application/json'
			),
		)
	);

	$ResponcURL = json_decode(curl_exec($curl), true);

	curl_close($curl);

	$CekResi = array();

	if ($ResponcURL['errors']['global'] == 'Data dengan barcode tersebut tidak ditemukan') {
		$CekResi['name'] = 'Pos Indonesia';
		$CekResi['site'] = 'www.posindonesia.co.id';
		$CekResi['error'] = true;
		$CekResi['message'] = 'Nomor resi tidak ditemukan.';
		print_r(json_encode($CekResi));
	} else {
		$CekResi['name'] = 'Pos Indonesia';
		$CekResi['site'] = 'www.posindonesia.co.id';
		$CekResi['error'] = false;
		$CekResi['message'] = 'success';

		$Tgl_Kirim = reset($ResponcURL['result']);
		$ArrayStatus = end($ResponcURL['result']);

		if (strpos($ArrayStatus['description'], 'Diterima') !== false) {
			$StatusKirim = 'DELIVERED';
			$TanggalTerima = $ArrayStatus['eventDate'];
			$NmPenerima = $ArrayStatus['description'];
			$NamaPenerima = preg_replace('/(.*)PENERIMA \/ KETERANGAN : (.*)/', '$2', $NmPenerima);
		} else {
			$StatusKirim = 'ON PROCESS';
			$TanggalTerima = null;
			$NamaPenerima = null;
		}

		$LedakInfo = explode(';', $Tgl_Kirim['description']);

		$Keterangan = array(
			'info' => array(
				'no_awb' => $Resi,
				'service' => preg_replace('/(.*)LAYANAN :(.*)/', '$2', $LedakInfo[0]),
				'status' => $StatusKirim,
				'tanggal_kirim' => date('d-m-Y H:i', strtotime($Tgl_Kirim['eventDate'])),
				'tanggal_terima' => date('d-m-Y H:i', strtotime($TanggalTerima)),
				'harga' => null,
				'berat' => null,
				'catatan' => null,
			),
		);

		$Pengirim = array(
			'pengirim' => array(
				'nama' => preg_replace('/(.*)PENGIRIM : (.*)/', '$2', $LedakInfo[1]),
				'phone' => $LedakInfo[3],
				'alamat' => $LedakInfo[2] . ', ' . $LedakInfo[4],
			),
		);

		$Penerima = array(
			'penerima' => array(
				'nama' => preg_replace('/(.*)PENERIMA : (.*)/', '$2', $LedakInfo[7]),
				'nama_penerima' => $NamaPenerima,
				'phone' => $LedakInfo[9],
				'alamat' => $LedakInfo[8] . ', ' . $LedakInfo[10],
			),
		);

		$Riwayat = array();
		foreach ($ResponcURL['result'] as $k => $v) {
			switch ($ResponcURL['result'][$k]['eventName']) {
				case 'POSTING LOKET':
					$Riwayat[$k] = [
						'tanggal' => date('d-m-Y H:i', strtotime($ResponcURL['result'][$k]['eventDate'])),
						'posisi' => $ResponcURL['result'][$k]['officeName'],
						'message' => 'Penerimaan di loket ' . $ResponcURL['result'][$k]['officeName'],
					];
					break;

				case 'MANIFEST SERAH':
					$Riwayat[$k] = [
						'tanggal' => date('d-m-Y H:i', strtotime($ResponcURL['result'][$k]['eventDate'])),
						'posisi' => $ResponcURL['result'][$k]['officeName'],
						'message' => 'Diteruskan ke Hub ' . preg_replace('/(.*)KANTOR TUJUAN : (.*)/', '$2', $ResponcURL['result'][$k]['description']),
					];
					break;

				case 'MANIFEST TERIMA':
					$Riwayat[$k] = [
						'tanggal' => date('d-m-Y H:i', strtotime($ResponcURL['result'][$k]['eventDate'])),
						'posisi' => $ResponcURL['result'][$k]['officeName'],
						'message' => 'Tiba di Hub ' . $ResponcURL['result'][$k]['officeName'],
					];
					break;

				case 'PROSES ANTAR':
					$Riwayat[$k] = [
						'tanggal' => date('d-m-Y H:i', strtotime($ResponcURL['result'][$k]['eventDate'])),
						'posisi' => $ResponcURL['result'][$k]['officeName'],
						'message' => 'Proses antar di ' . $ResponcURL['result'][$k]['officeName'],
					];
					break;

				case 'SELESAI ANTAR':
					if (strpos('Antar Ulang', $ResponcURL['result'][$k]['description']) !== false) {
						$StatusAntar = 'Gagal antar - (';
						$StatusAntar .= preg_replace('/(.*)KETERANGAN : (.*)/', '$2', $ResponcURL['result'][$k]['description']) . ')';
					} else {
						$NamaPenerima = preg_replace('/(.*)PENERIMA \/ KETERANGAN : (.*)/', '$2', $ResponcURL['result'][$k]['description']);
						$StatusAntar = 'Selesai antar. (';
						$StatusAntar .= $NamaPenerima . ')';
					}
					$Riwayat[$k] = [
						'tanggal' => date('d-m-Y H:i', strtotime($ResponcURL['result'][$k]['eventDate'])),
						'posisi' => $ResponcURL['result'][$k]['officeName'],
						'message' => $StatusAntar
					];
					break;
			}
		}

		$HasilRiwayat = array(
			'history' => $Riwayat,
		);

		$Hasil = array_merge($CekResi, $Keterangan, $Pengirim, $Penerima, $HasilRiwayat);
		print_r(json_encode($Hasil));
	}
} elseif ($Kurir == 'ninja') {
	$curl = curl_init();

	curl_setopt_array(
		$curl,
		array(
			CURLOPT_URL => "https://api.ninjavan.co/id/shipperpanel/app/tracking?id=$Resi",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"User-Agent: okhttp/3.4.1"
			),
		)
	);

	$ResponcURL = json_decode(curl_exec($curl), true);

	curl_close($curl);

	$Tgl_Kirim = reset($ResponcURL['orders'][0]['events']);
	$TanggalKirim = $Tgl_Kirim['time'] / 1000;

	if ($ResponcURL['orders'][0]['status'] == 'Completed') {
		$StatusKirim = 'DELIVERED';
	} else {
		$StatusKirim = strtoupper($ResponcURL['orders'][0]['status']);
	}

	$Tgl_Terima = end($ResponcURL['orders'][0]['events']);
	if (strpos($Tgl_Terima['description'], 'berhasil dikirimkan') !== false) {
		$TanggalTerima = $Tgl_Terima['time'] / 1000;
	} else {
		$TanggalTerima = null;
	}

	$CekResi = array();

	if ($TanggalKirim == null) {
		$CekResi['name'] = 'NinjaXpress';
		$CekResi['site'] = 'www.ninjaxpress.co';
		$CekResi['error'] = true;
		$CekResi['message'] = 'Nomor resi tidak ditemukan.';
		print_r(json_encode($CekResi));
	} else {
		$CekResi['name'] = 'NinjaXpress';
		$CekResi['site'] = 'www.ninjaxpress.co';
		$CekResi['error'] = false;
		$CekResi['message'] = 'success';

		$Keterangan = array(
			'info' => array(
				'no_awb' => $ResponcURL['orders'][0]['tracking_id'],
				'service' => $ResponcURL['orders'][0]['service_type'],
				'status' => $StatusKirim,
				'tanggal_kirim' => date('d-m-Y H:i', $TanggalKirim),
				'tanggal_terima' => date('d-m-Y H:i', $TanggalTerima),
				'harga' => null,
				'berat' => null,
				'catatan' => null,
			),
		);

		$Pengirim = array(
			'pengirim' => array(
				'nama' => $ResponcURL['orders'][0]['from_name'],
				'phone' => null,
				'alamat' => null,
			),
		);

		$Penerima = array(
			'penerima' => array(
				'nama' => null,
				'nama_penerima' => $ResponcURL['orders'][0]['transactions'][1]['signature']['name'],
				'phone' => null,
				'alamat' => $ResponcURL['orders'][0]['to_city'],
			),
		);

		$Riwayat = array();
		foreach ($ResponcURL['orders'][0]['events'] as $k => $v) {
			$Riwayat[$k]['tanggal'] = date('d-m-Y H:i', $ResponcURL['orders'][0]['events'][$k]['time'] / 1000);
			$PecahRiwayat = preg_split('/[\[\]]/', $ResponcURL['orders'][0]['events'][$k]['description']);
			if (strpos($ResponcURL['orders'][0]['events'][$k]['description'], 'berhasil dikirimkan') !== false) {
				$Riwayat[$k]['posisi'] = 'DITERIMA';
				$Riwayat[$k]['message'] = $ResponcURL['orders'][0]['events'][$k]['description'];
			} elseif (strpos($ResponcURL['orders'][0]['events'][$k]['description'], ' - ') !== false) {
				$Pecah = explode(' | ', $PecahRiwayat[1]);
				$Riwayat[$k]['posisi'] = preg_replace('/(.*) - (.*)/', '$2', $ResponcURL['orders'][0]['events'][$k]['description']);
				$Riwayat[$k]['message'] = rtrim(str_replace(' AT', '', $PecahRiwayat[0]));
			} else {
				$Riwayat[$k]['posisi'] = null;
				$Riwayat[$k]['message'] = $ResponcURL['orders'][0]['events'][$k]['description'];
			}
		}

		$HasilRiwayat = array(
			'history' => $Riwayat,
		);

		$Hasil = array_merge($CekResi, $Keterangan, $Pengirim, $Penerima, $HasilRiwayat);
		print_r(json_encode($Hasil));
	}
} elseif ($Kurir == 'lionparcel') {
	if (strpos($Resi, '-') !== false) {
		$Resi = str_replace('-', '', $Resi);
	}

	$Resi = substr($Resi, 0, 2) . '-' . substr($Resi, 2);
	$Resi = substr($Resi, 0, 5) . '-' . substr($Resi, 5);

	$curl = curl_init();

	curl_setopt_array(
		$curl,
		array(
			CURLOPT_URL => "https://algo-api.lionparcel.com/v1/shipment/search?reference=$Resi",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
		)
	);

	$ResponcURL = json_decode(curl_exec($curl), true);

	curl_close($curl);

	$Tgl_Kirim = end($ResponcURL['histories']);
	$TanggalKirim = date('d-m-Y H:i', strtotime($Tgl_Kirim['created_at']));

	$Tgl_Terima = reset($ResponcURL['histories']);
	if (strpos($Tgl_Terima['status'], 'Terkirim') !== false) {
		$TanggalTerima = date('d-m-Y H:i', strtotime($Tgl_Terima['created_at']));
		$StatusKirim = 'DELIVERED';
		$NamaPenerima = $Tgl_Terima['person_name'];
	} else {
		$TanggalTerima = null;
		$StatusKirim = strtoupper($Tgl_Terima['status']);
		$NamaPenerima = null;
	}

	$Harga = array(
		$ResponcURL['publish_rate'], $ResponcURL['forward_rate'], $ResponcURL['shipping_surcharge_rate'], $ResponcURL['commodity_surcharge_rate'], $ResponcURL['heavy_weight_surcharge_rate'], $ResponcURL['insurance_rate'], $ResponcURL['wood_packing_rate']
	);
	$HitungHarga = array_sum($Harga);

	$CekResi = array();

	if ($ResponcURL == null) {
		$CekResi['name'] = 'LionParcel';
		$CekResi['site'] = 'lionparcel.com';
		$CekResi['error'] = true;
		$CekResi['message'] = 'Nomor resi tidak ditemukan.';
		print_r(json_encode($CekResi));
	} else {
		$CekResi['name'] = 'LionParcel';
		$CekResi['site'] = 'lionparcel.com';
		$CekResi['error'] = false;
		$CekResi['message'] = 'success';

		$Keterangan = array(
			'info' => array(
				'no_awb' => $ResponcURL['package_id'],
				'service' => $ResponcURL['service_type'],
				'status' => $StatusKirim,
				'tanggal_kirim' => $TanggalKirim,
				'tanggal_terima' => $TanggalTerima,
				'harga' => $HitungHarga,
				'berat' => $ResponcURL['gross_weight'],
				'catatan' => null,
			),
		);

		$Pengirim = array(
			'pengirim' => array(
				'nama' => $ResponcURL['sender']['name'],
				'phone' => $ResponcURL['sender']['address'],
				'alamat' => $ResponcURL['sender']['phone'],
			),
		);

		$Penerima = array(
			'penerima' => array(
				'nama' => $ResponcURL['recipient']['name'],
				'nama_penerima' => $NamaPenerima,
				'phone' => $ResponcURL['recipient']['phone'],
				'alamat' => $ResponcURL['recipient']['address'],
			),
		);

		$BalikRiwayat = array_reverse($ResponcURL['histories']);
		$Riwayat = array();
		foreach ($BalikRiwayat as $k => $v) {
			$Riwayat[$k]['tanggal'] = date('d-m-Y H:i', strtotime($BalikRiwayat[$k]['created_at']));
			if (strpos($BalikRiwayat[$k]['status'], 'Terkirim') !== false) {
				$Riwayat[$k]['posisi'] = 'DITERIMA';
				$Riwayat[$k]['message'] = $BalikRiwayat[$k]['long_status'];
			} else {
				$Riwayat[$k]['posisi'] = $BalikRiwayat[$k]['city'];
				$Riwayat[$k]['message'] = $BalikRiwayat[$k]['long_status'];
			}
		}

		$HasilRiwayat = array(
			'history' => $Riwayat,
		);

		$Hasil = array_merge($CekResi, $Keterangan, $Pengirim, $Penerima, $HasilRiwayat);
		print_r(json_encode($Hasil));
	}
} elseif ($Kurir == 'tiki') {
	$curl = curl_init();

	curl_setopt_array(
		$curl,
		array(
			CURLOPT_URL => "https://my.tiki.id/api/connote/info",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => "cnno=$Resi",
			CURLOPT_HTTPHEADER => array(
				"Authorization:  0437fb74-91bd-11e9-a74c-06f2c0b7c6f0-91bf-11e9-a74c-06f2c4b0b602",
				"Content-Type: application/x-www-form-urlencoded"
			),
		)
	);

	$ResponcURL0 = json_decode(curl_exec($curl), true);

	curl_close($curl);

	$curl = curl_init();

	curl_setopt_array(
		$curl,
		array(
			CURLOPT_URL => "https://my.tiki.id/api/connote/history",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => "cnno=$Resi",
			CURLOPT_HTTPHEADER => array(
				"Authorization:  0437fb74-91bd-11e9-a74c-06f2c0b7c6f0-91bf-11e9-a74c-06f2c4b0b602",
				"Content-Type: application/x-www-form-urlencoded"
			),
		)
	);

	$ResponcURL1 = json_decode(curl_exec($curl), true);

	curl_close($curl);

	$Tgl_Kirim = end($ResponcURL1['response'][0]['history']);
	$TanggalKirim = $Tgl_Kirim['entry_date'];

	$Tgl_Terima = reset($ResponcURL1['response'][0]['history']);
	if (strpos($Tgl_Terima['noted'], 'Success') !== false) {
		$TanggalTerima = $Tgl_Terima['entry_date'];
		$StatusKirim = 'DELIVERED';
		$NmPenerima = $Tgl_Terima['noted'];
		$NamaPenerima = preg_replace('/(.*) RECEIVED BY: (.*)/', '$2', $NmPenerima);
	} else {
		$TanggalTerima = null;
		$StatusKirim = 'ON PROCESS';
		$NamaPenerima = null;
	}

	$CekResi = array();

	if ($ResponcURL0['response'] == null) {
		$CekResi['name'] = 'TIKI';
		$CekResi['site'] = 'tiki.id';
		$CekResi['error'] = true;
		$CekResi['message'] = 'Nomor resi tidak ditemukan.';
		print_r(json_encode($CekResi));
	} else {
		$CekResi['name'] = 'TIKI';
		$CekResi['site'] = 'tiki.id';
		$CekResi['error'] = false;
		$CekResi['message'] = 'success';

		$Keterangan = array(
			'info' => array(
				'no_awb' => $ResponcURL0['response'][0]['cnno'],
				'service' => $ResponcURL0['response'][0]['product'],
				'status' => $StatusKirim,
				'tanggal_kirim' => date('d-m-Y H:i', strtotime($TanggalKirim)),
				'tanggal_terima' => date('d-m-Y H:i', strtotime($TanggalTerima)),
				'harga' => $ResponcURL0['response'][0]['total_fee'],
				'berat' => $ResponcURL0['response'][0]['weight'],
				'catatan' => null,
			),
		);

		$Pengirim = array(
			'pengirim' => array(
				'nama' => $ResponcURL0['response'][0]['consignor_name'],
				'phone' => null,
				'alamat' => $ResponcURL0['response'][0]['consignor_address'],
			),
		);

		$Penerima = array(
			'penerima' => array(
				'nama' => $ResponcURL0['response'][0]['consignee_name'],
				'nama_penerima' => $NamaPenerima,
				'phone' => null,
				'alamat' => $ResponcURL['orders'][0]['consignee_address'],
			),
		);

		$BalikRiwayat = array_reverse($ResponcURL1['response'][0]['history']);
		$Riwayat = array();
		foreach ($BalikRiwayat as $k => $v) {
			$Riwayat[$k]['tanggal'] = date('d-m-Y H:i', strtotime($BalikRiwayat[$k]['entry_date']));
			if (strpos($BalikRiwayat[$k]['noted'], 'Success') !== false) {
				$Riwayat[$k]['posisi'] = 'DITERIMA';
				$Riwayat[$k]['message'] = $BalikRiwayat[$k]['noted'];
			} else {
				$Riwayat[$k]['posisi'] = $BalikRiwayat[$k]['entry_name'];
				$Riwayat[$k]['message'] = $BalikRiwayat[$k]['noted'];
			}
		}

		$HasilRiwayat = array(
			'history' => $Riwayat,
		);

		$Hasil = array_merge($CekResi, $Keterangan, $Pengirim, $Penerima, $HasilRiwayat);
		print_r(json_encode($Hasil));
	}
} else {
	$CekResi = array();
	$CekResi['name'] = null;
	$CekResi['site'] = null;
	$CekResi['error'] = true;
	$CekResi['message'] = 'Jasa pengiriman belum didukung!';
	print_r(json_encode($CekResi));
}
