# API Documentation

Dokumentasi ini berisi daftar endpoint API yang tersedia pada aplikasi beserta deskripsi singkatnya. Endpoint dikelompokkan berdasarkan fungsionalitas dan role akses.

---

## 1. Auth (API)

| Method | Endpoint                              | Deskripsi                       |
|--------|----------------------------------------|---------------------------------|
| POST   | /api/v1/auth/register                  | Register user                   |
| POST   | /api/v1/auth/login                     | Login user                      |
| POST   | /api/v1/auth/forgot-password           | Request reset password          |
| POST   | /api/v1/auth/reset-password            | Reset password                  |
| POST   | /api/v1/auth/logout                    | Logout (JWT)                    |
| POST   | /api/v1/auth/forgot-password-mail      | Forgot password via email       |
| POST   | /api/v1/auth/reset-password-mail       | Reset password via email        |
| POST   | /api/v1/auth/register-mail             | Register via email              |
| GET    | /api/v1/auth/activate                  | Aktivasi akun                   |

## 2. Public (API Key)

| Method | Endpoint                              | Deskripsi                       |
|--------|----------------------------------------|---------------------------------|
| GET    | /api/v1/public/categories              | List kategori                   |
| GET    | /api/v1/public/categories/{id}         | Detail kategori                 |
| GET    | /api/v1/public/products                | List produk                     |
| GET    | /api/v1/public/products/{id}           | Detail produk                   |

## 3. Client (JWT)

| Method | Endpoint                              | Deskripsi                       |
|--------|----------------------------------------|---------------------------------|
| GET    | /api/v1/client/profile                 | Profil user                     |
| GET    | /api/v1/client/kodebrand               | Data master kode brand          |
| GET    | /api/v1/client/grouplensa              | Data master group lensa         |
| GET    | /api/v1/client/lensa                   | Data master lensa               |
| GET    | /api/v1/client/spheris                 | Data master spheris             |
| GET    | /api/v1/client/cylinder                | Data master cylinder            |
| GET    | /api/v1/client/axis                    | Data master axis                |
| GET    | /api/v1/client/additional              | Data master additional          |
| GET    | /api/v1/client/model                   | Data master model               |
| GET    | /api/v1/client/jasa                    | Data master jasa                |
| GET    | /api/v1/client/base                    | Data master base                |
| GET    | /api/v1/client/prisma                  | Data master prisma              |
| GET    | /api/v1/client/framestatus             | Data master frame status        |
| GET    | /api/v1/client/framejenis              | Data master frame jenis         |
| POST   | /api/v1/client/transaksi               | Buat transaksi                  |
| GET    | /api/v1/client/transaksi               | List transaksi                  |
| GET    | /api/v1/client/transaksi/{id}          | Detail transaksi                |
| POST   | /api/v1/client/transactions            | Buat transaksi (alt)            |
| GET    | /api/v1/client/transactions            | List transaksi (alt)            |
| GET    | /api/v1/client/transactions/{id}       | Detail transaksi (alt)          |

## 4. Admin (JWT)

### Role Management
| Method | Endpoint                              | Deskripsi                       |
|--------|----------------------------------------|---------------------------------|
| GET    | /api/v1/admin/roles                    | List role                       |
| POST   | /api/v1/admin/roles                    | Tambah role                     |
| GET    | /api/v1/admin/roles/{id}               | Detail role                     |
| PUT    | /api/v1/admin/roles/{id}               | Update role                     |
| DELETE | /api/v1/admin/roles/{id}               | Hapus role                      |
| GET    | /api/v1/admin/roles/{id}/permissions   | List permission pada role       |
| POST   | /api/v1/admin/roles/{id}/permissions   | Assign permission ke role       |

### Permission Management
| Method | Endpoint                              | Deskripsi                       |
|--------|----------------------------------------|---------------------------------|
| GET    | /api/v1/admin/permissions              | List permission                 |
| POST   | /api/v1/admin/permissions              | Tambah permission               |
| GET    | /api/v1/admin/permissions/{id}         | Detail permission               |
| PUT    | /api/v1/admin/permissions/{id}         | Update permission               |
| DELETE | /api/v1/admin/permissions/{id}         | Hapus permission                |

### User Management
| Method | Endpoint                              | Deskripsi                       |
|--------|----------------------------------------|---------------------------------|
| GET    | /api/v1/admin/users                    | List user                       |
| POST   | /api/v1/admin/users                    | Tambah user                     |
| PUT    | /api/v1/admin/users/{id}               | Update user                     |
| GET    | /api/v1/admin/users/{id}/roles         | List role user                  |
| POST   | /api/v1/admin/users/{id}/roles         | Assign role ke user             |

### Category Management
| Method | Endpoint                              | Deskripsi                       |
|--------|----------------------------------------|---------------------------------|
| GET    | /api/v1/admin/categories               | List kategori                   |
| POST   | /api/v1/admin/categories               | Tambah kategori                 |
| PUT    | /api/v1/admin/categories/{id}          | Update kategori                 |
| DELETE | /api/v1/admin/categories/{id}          | Hapus kategori                  |

### Product Management
| Method | Endpoint                              | Deskripsi                       |
|--------|----------------------------------------|---------------------------------|
| GET    | /api/v1/admin/products                 | List produk                     |
| POST   | /api/v1/admin/products                 | Tambah produk                   |
| GET    | /api/v1/admin/products/{id}            | Detail produk                   |
| PUT    | /api/v1/admin/products/{id}            | Update produk                   |
| DELETE | /api/v1/admin/products/{id}            | Hapus produk                    |

### API Key Management
| Method | Endpoint                              | Deskripsi                       |
|--------|----------------------------------------|---------------------------------|
| GET    | /api/v1/admin/apikeys                  | List API key                    |
| POST   | /api/v1/admin/apikeys                  | Tambah API key                  |
| PUT    | /api/v1/admin/apikeys/{id}             | Update API key                  |
| DELETE | /api/v1/admin/apikeys/{id}             | Hapus API key                   |

### Transaction Management
| Method | Endpoint                              | Deskripsi                       |
|--------|----------------------------------------|---------------------------------|
| GET    | /api/v1/admin/transactions             | List semua transaksi            |
| GET    | /api/v1/admin/transactions/{id}        | Detail transaksi                |
| PUT    | /api/v1/admin/transactions/{id}/status | Update status transaksi         |

### Karyawan
| Method | Endpoint                              | Deskripsi                       |
|--------|----------------------------------------|---------------------------------|
| GET    | /api/v1/admin/karyawans                | List karyawan                   |

---

## 5. Web (Session)

| Method | Endpoint              | Deskripsi                |
|--------|-----------------------|--------------------------|
| GET    | /login                | Halaman login admin      |
| POST   | /login                | Proses login admin       |
| GET    | /logout               | Logout admin             |
| GET    | /forgot_password      | Halaman lupa password    |
| POST   | /forgot_password      | Proses lupa password     |
| GET    | /reset_password       | Halaman reset password   |
| POST   | /reset_password       | Proses reset password    |
| GET    | /backend/             | Dashboard admin          |
| GET    | /backend/dashboard    | Dashboard admin          |
| GET    | /backend/transaksi    | List transaksi           |
| GET    | /backend/transaksi/detail/{id} | Detail transaksi  |
| POST   | /backend/transaksi/update_status | Update status    |
| GET    | /backend/users        | List user                |

---

> Catatan: Beberapa endpoint membutuhkan autentikasi JWT, API Key, atau session, serta permission tertentu.

Silakan gunakan dokumentasi ini untuk pengecekan dan pengembangan selanjutnya.
