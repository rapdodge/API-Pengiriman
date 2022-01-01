<?php
date_default_timezone_set('Asia/Jakarta');
error_reporting(0);
header('Content-Type: application/json');
$JasaPengiriman = array('anteraja', 'ninja', 'pos', 'sicepat', 'tiki');
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
