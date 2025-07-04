# Changelog TOL API (CodeIgniter 4)

---

## v1.4.0 (2025-07-04)
- Dokumentasi aplikasi diperbarui di README.md
- Audit permission granular di seluruh endpoint penting
- Penambahan dokumentasi API lengkap di `API_DOCUMENTATION.md`
- Penambahan catatan pengembangan dan saran pengembangan lanjutan

## v1.3.0
- Penambahan endpoint dan tombol export Excel (XLSX) (opsional, siap dikembangkan)
- Penyesuaian UX datatables untuk data besar (opsional: infinite scroll, page size besar, dsb)
- Penambahan bulk action (opsional)
- Penambahan audit log, notifikasi, dan fitur keamanan tambahan (opsional)

## v1.2.0
- Penambahan filter tanggal (range datepicker) pada datatables transaksi
- Penambahan tombol export CSV custom di halaman transaksi (bukan export datatables bawaan)
- Endpoint backend `/backend/transaksi/export_csv` menerima filter aktif dan output file CSV siap download
- Placeholder search datatables diganti menjadi "Search No PO"
- Hilangkan tombol export datatables bawaan, hanya pakai tombol export custom

## v1.1.0
- Penambahan fitur drag & drop, checklist, parent-child logic pada manajemen role/permission
- Penambahan filter customer dan status pada datatables transaksi
- Penambahan halaman detail transaksi (lensa kanan/kiri, jasa, dsb)
- Penambahan flash message, sticky sidebar/footer, notifikasi
- Perbaikan bug assignPermissionsToRole

## v1.0.0 (Initial)
- Inisialisasi project CodeIgniter 4
- Setup struktur folder, environment, dan database
- Implementasi autentikasi JWT, session, dan API Key
- CRUD user, role, permission, produk, kategori, transaksi, jasa, lensa
- Implementasi permission granular (parent-child, slug, link)
- UI backend: login, dashboard, manajemen user, role, permission, produk, kategori, transaksi


## 2026-06-13
- Update seluruh data master
- Pengecekan dan penyesuaian field transaksi

## 2025-06-09
- Persiapan data untuk server live testing
- Implementasi Create Transaction OR
- Penambahan data gambar model dari master model

## 2025-06-05
- getAll Lensa pakai paging
- Invalid token login dengan JWT
- Permission global pakai AppPermission
- Penyesuaian field tabel trn_or_01 dan or_01_jasa

---

> Changelog ini merupakan gabungan dari changelog manual author dan changelog fitur utama, dirangkum dan diurutkan dari yang terbaru. Untuk detail commit, lihat bagian changelog otomatis di bawah atau riwayat commit di repo.





#--------------------------------------------------------------------
# Catatan Input OR Independent
#--------------------------------------------------------------------


# # Kode Brand ( CZ, HB, SC )

# # Group Lensa
# 	#BIFOCAL "BF"
# 	#OFFICELENS "OL"
# 	#PROGRESSIVE "PG"
# 	#PROGRESSIVE FREE FORM "PF"
# 	#SINGLE VISION "SV"
# 	#SINGLE VISION FREE FORM "SF"
	
# # Select Lensa L|R
#     SELECT * FROM mst_jlensa_5digit 
#     WHERE type_customer IN ("I","A") AND kd_brand="CZ" AND jenis_lensa="SF" 
#     LIMIT 10;
	
# # SPHERIS ========
#     SELECT DISTINCT nama_jpower 
#     FROM db_tol.mst_jpower 
#     WHERE jenis_jpower="S" AND aktif=1 
#     AND CAST(nama_jpower AS DECIMAL(6,2)) < 0 
#     UNION ALL 
#     SELECT "0.00" nama_jpower 
#     UNION ALL 
#     SELECT DISTINCT nama_jpower 
#     FROM db_tol.mst_jpower 
#     WHERE jenis_jpower="S" AND aktif=1 
#     AND CAST(nama_jpower AS DECIMAL(6,2)) > 0 
#     ORDER BY CAST(nama_jpower AS DECIMAL(6,2)) DESC ;

# # CYLINDER =========
#     SELECT 
#     	kode_lensa_5digit,kode_jspheris,kode_jcylinder AS kode_cyl 
#     FROM 
#     	mst_over_power 
#     WHERE 
#     	kode_lensa_5digit='97677' 
#     	AND kode_jspheris='-0.50' 
#     	AND aktif=1;
    	
# # AXIS ==========
#   SELECT DISTINCT nama_jpower
#   FROM db_tol.mst_jpower
#   WHERE jenis_jpower="A" 
#   AND aktif=1
#   ORDER BY CAST(nama_jpower AS DECIMAL(6,2));
	
		
# # ADD ============================

# 	$cek_1 = SELECT * FROM db_tol.mst_add_h WHERE lensa_5digit_id = "97677" AND lens_aktif = 1 ;
# 	# if($cek_1) #bila ada datanya, maka :
# 		$data_add = SELECT d.* FROM db_tol.mst_add_d d WHERE d.lensa_5digit_id ="97677" AND d.cyl_aktif = 1 ;
# 	# else
# 		$cek_2 = SELECT * FROM mst_jpower_lensa_prs WHERE lensa_5digit_id="97677" AND jenis_jpower="D" AND aktif=1 ORDER BY CAST(nama_jpower AS DECIMAL(6,2));
# 		#if($cek_2)
# 		  $data_add = $cek_2;
# 		# else
# 		  $data_add = SELECT DISTINCT nama_jpower FROM db_tol.mst_jpower WHERE jenis_jpower="D" AND aktif=1 ORDER BY CAST(nama_jpower AS DECIMAL(6,2));


# # NO PO = Diambil Dari No Penjualan Optik Independent , Input Manual, Validasi Karakter , Wajib Di isi
# # Nama Pasien =  Isi Sendiri| Input Manual , Validasi Karakter, Boleh Kosong

# # Usia = cek untuk kode lensa, jika kode L dan R sama cek kode lensa untuk validasi umur boleh dikosongkan
# 	SELECT * FROM mst_jlensa_5digit WHERE lensa_5digit_id="97677"
# 	AND ((nama_lensa_5digit LIKE "%SMARTLIFE%") OR (nama_lensa_5digit LIKE "%LIGHT 2%"));
# 	# kalau L dan R tidak sama usia boleh kosongkan
# 	# Isi Sendiri| Input Manual , Validasi Numeric

# #  --------Base CRV1 = Isi Sendiri| Input Manual , Validasi Karakter TIDAK DIPAKAI

## Base CRV2 = Isi Sendiri| Input Manual , Validasi Karakter, Optional
## PRISMA1 = 0-15 , Optional
## BASE 1  = 0-399 , Optional
## PRISMA2 = Isi Sendiri| Input Manual, Validasi Karakter , Optional
## BASE 2 = Isi Sendiri| Input Manual, Validasi Karakter , Optional
## PDF = Isi Sendiri| Input Manual, Boleh 13.1 (satu digit belakang koma) , Optional
## PDN = Isi Sendiri| Input Manual, Boleh 13.1 (satu digit belakang koma) , Optional
## QTY = 1 , Wajib Bila Ada Kode Lensa di isi


## RIGHT    = ET : Numeric, Default 0 (NOL) , Optional | CT : Numeric, Default 0 (NOL) , Optional
## LEFT     = ET : Numeric, Default 0 (NOL) , Optional | CT : Numeric, Default 0 (NOL) , Optional

## WA : Numeric, Default 0 (NOL) , Optional | V
## PT : Numeric, Default 0 (NOL) , Optional | V
## BVD : Numeric, Default 0 (NOL) , Optional | V
## FFV : Numeric, Default 0 (NOL) , Optional | V
## V CODE : Numeric, Default 0 (NOL) , Optional | V
## RD : Numeric, Default 0 (NOL) , Optional | V

## PE : Numeric, Default Null , Optional | V
## MBS: Numeric, Default 0 (NOL) , Optional | V

## MID : Numeric, Default 0 (NOL) , Optional | 
## B :Numeric, Default 0 (NOL) , Optional | 
## ED Numeric, Default 0 (NOL) , Optional | 
## A : Numeric, Default 0 (NOL) , Optional | 
## DBL : Numeric, Default 0 (NOL) , Optional | 
## SH/PV : Numeric, Default 0 (NOL) , Optional | 



# ### Kondisi Frame = Isi Sendiri| Input Manual, Validasi Karakter


# ### Model Frame = 1-11 , gambar nanti di cdn | V

# ### Status Frame = ( COMPLETE = C , ENCLOSED = E, TO COME = T) , Boleh Kosong | V

# ### Jenis Frame = 1-6 ( BOR, BOR LEVEL, FULL METAL, FULL PLASTIC, NYLOR, NYLOR METAL ) | V

# ### NOTE = Isi Sendiri| Input Manual, Validasi Karakter | V

# ### Special Instruction = Isi Sendiri| Input Manual, Validasi Karakter | V



# get nilai koridor
    # // IF SH/PV <> '') THEN
    # // BEGIN
    # // seg_height = SH/PV;
    # // IF (seg_height <= 18) THEN
    # //     koridor := 'SHORT'
    # // ELSE IF (seg_height >= 19) THEN
    # //     koridor := 'LONG';
    # // EditSHPV.Enabled := FALSE;

    
# `mst_sales_customer`
# `mst_customer`   
# `mst_jlensa_5digit`
# `mst_base_sph`
# `mst_jjasa`
# `mst_cyl_add`;


# Yang belum 
# Get data mst_customer join ke mst_salesman







#--------------------------------------------------------------------
#------- Catatan Develop --------------------------------------------

# 13/06/2026
# update seluruh data master
# pengecekan field transaksi
# 


# 09/06/2025
# Siapkan Data Untuk di server Live testing 
# Create Transaction OR 
# Data gambar model dari master model

# 05/06/2025
# getAll Lensa pakai paging - DONE
# Invalid token login dengan jwt - DONE
# permission Global pakai appPermission - DONE
# siapkan field tabel trn_or_01 dan or_01_jasa - DONE
    
#--------------------------------------------------------------------