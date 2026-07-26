CREATE DATABASE campushub;
USE campushub;

CREATE TABLE organisasi (
    id_organisasi INT AUTO_INCREMENT PRIMARY KEY,
    nama_organisasi VARCHAR(100) NOT NULL,
    logo VARCHAR(255) DEFAULT NULL,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    id_organisasi INT NULL,
    nama VARCHAR(100) NOT NULL,
    nim VARCHAR(20) DEFAULT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Admin','Panitia','Mahasiswa') NOT NULL,
    foto VARCHAR(255) DEFAULT 'default.png',
    status ENUM('Aktif','Nonaktif') DEFAULT 'Aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_user_organisasi
    FOREIGN KEY (id_organisasi)
    REFERENCES organisasi(id_organisasi)
    ON UPDATE CASCADE
    ON DELETE SET NULL
);

CREATE TABLE kategori_event (
    id_kategori INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE event (
    id_event INT AUTO_INCREMENT PRIMARY KEY,

    id_organisasi INT NOT NULL,
    id_panitia INT NOT NULL,
    id_kategori INT NOT NULL,

    judul VARCHAR(150) NOT NULL,
    deskripsi TEXT NOT NULL,
    poster VARCHAR(255),

    tanggal DATE NOT NULL,
    jam TIME NOT NULL,

    lokasi VARCHAR(150) NOT NULL,

    kuota INT NOT NULL,

    status ENUM('Draft','Publish','Selesai') DEFAULT 'Draft',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_event_organisasi
    FOREIGN KEY (id_organisasi)
    REFERENCES organisasi(id_organisasi)
    ON UPDATE CASCADE
    ON DELETE CASCADE,

    CONSTRAINT fk_event_panitia
    FOREIGN KEY (id_panitia)
    REFERENCES users(id_user)
    ON UPDATE CASCADE
    ON DELETE CASCADE,

    CONSTRAINT fk_event_kategori
    FOREIGN KEY (id_kategori)
    REFERENCES kategori_event(id_kategori)
    ON UPDATE CASCADE
    ON DELETE CASCADE
);

CREATE TABLE pendaftaran (
    id_pendaftaran INT AUTO_INCREMENT PRIMARY KEY,

    id_event INT NOT NULL,
    id_user INT NOT NULL,

    status ENUM('Menunggu','Disetujui','Ditolak') DEFAULT 'Menunggu',

    tanggal_daftar TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_pendaftaran_event
    FOREIGN KEY (id_event)
    REFERENCES event(id_event)
    ON UPDATE CASCADE
    ON DELETE CASCADE,

    CONSTRAINT fk_pendaftaran_user
    FOREIGN KEY (id_user)
    REFERENCES users(id_user)
    ON UPDATE CASCADE
    ON DELETE CASCADE,

    UNIQUE(id_event,id_user)
);

CREATE TABLE kehadiran (
    id_kehadiran INT AUTO_INCREMENT PRIMARY KEY,

    id_pendaftaran INT NOT NULL UNIQUE,

    status_hadir ENUM('Hadir','Tidak Hadir') DEFAULT 'Tidak Hadir',

    waktu_checkin DATETIME DEFAULT NULL,

    CONSTRAINT fk_kehadiran_pendaftaran
    FOREIGN KEY (id_pendaftaran)
    REFERENCES pendaftaran(id_pendaftaran)
    ON UPDATE CASCADE
    ON DELETE CASCADE
);

CREATE TABLE sertifikat (
    id_sertifikat INT AUTO_INCREMENT PRIMARY KEY,

    id_kehadiran INT NOT NULL UNIQUE,

    file_sertifikat VARCHAR(255) NOT NULL,

    tanggal_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_sertifikat_kehadiran
    FOREIGN KEY (id_kehadiran)
    REFERENCES kehadiran(id_kehadiran)
    ON UPDATE CASCADE
    ON DELETE CASCADE
);

CREATE TABLE dokumentasi (
    id_dokumentasi INT AUTO_INCREMENT PRIMARY KEY,

    id_event INT NOT NULL,

    foto VARCHAR(255) NOT NULL,

    keterangan VARCHAR(255),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_dokumentasi_event
    FOREIGN KEY (id_event)
    REFERENCES event(id_event)
    ON UPDATE CASCADE
    ON DELETE CASCADE
);

SELECT
    e.id_event,
    e.judul,
    e.tanggal,
    e.lokasi,
    e.kuota,

    -- jumlah peserta yang sudah disetujui
    COUNT(CASE WHEN p.status = 'Disetujui' THEN 1 END) AS kuota_terpakai,

    -- sisa kuota
    e.kuota - COUNT(CASE WHEN p.status = 'Disetujui' THEN 1 END) AS kuota_tersisa

FROM event e
LEFT JOIN pendaftaran p
       ON e.id_event = p.id_event
GROUP BY
    e.id_event,
    e.judul,
    e.tanggal,
    e.lokasi,
    e.kuota
ORDER BY e.tanggal ASC;

SELECT
    e.*,
    COUNT(CASE WHEN p.status = 'Disetujui' THEN 1 END) AS kuota_terpakai,
    e.kuota - COUNT(CASE WHEN p.status = 'Disetujui' THEN 1 END) AS kuota_tersisa
FROM event e
LEFT JOIN pendaftaran p
       ON e.id_event = p.id_event
WHERE e.id_event = 1
GROUP BY e.id_event;

SELECT
    e.id_event,
    e.kuota,
    COUNT(CASE WHEN p.status = 'Disetujui' THEN 1 END) AS terpakai
FROM event e
LEFT JOIN pendaftaran p
       ON e.id_event = p.id_event
WHERE e.id_event = ?
GROUP BY e.id_event;

SELECT
    e.id_event,
    e.judul,
    e.kuota - COUNT(CASE WHEN p.status = 'Disetujui' THEN 1 END) AS kuota_tersisa,
    CASE
        WHEN e.kuota - COUNT(CASE WHEN p.status = 'Disetujui' THEN 1 END) <= 0
            THEN 'Penuh'
        ELSE 'Tersedia'
    END AS status_kuota
FROM event e
LEFT JOIN pendaftaran p
       ON e.id_event = p.id_event
GROUP BY e.id_event, e.judul, e.kuota;