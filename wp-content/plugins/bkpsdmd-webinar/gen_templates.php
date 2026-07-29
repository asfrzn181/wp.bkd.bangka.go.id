<?php
// Script temporary untuk generate default-sk.docx dan default-petikan.docx

function create_minimal_docx($file_path, $content_xml) {
    $zip = new ZipArchive();
    if ($zip->open($file_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
<Default Extension="xml" ContentType="application/xml"/>
<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
</Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>');
        $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
<w:body>' . $content_xml . '</w:body>
</w:document>');
        $zip->close();
        echo "Created: $file_path\n";
    }
}

$dir = __DIR__ . '/templates/';
if (!is_dir($dir)) mkdir($dir, 0755, true);

// SK docx
$sk_xml = '<w:p><w:r><w:t>SURAT KEPUTUSAN (SK MINUT)</w:t></w:r></w:p>
<w:p><w:r><w:t>Nomor: ${sk_number}</w:t></w:r></w:p>
<w:p><w:r><w:t>Tanggal: ${sk_date}</w:t></w:r></w:p>
<w:p><w:r><w:t>Tentang: Penyelenggaraan Webinar ${nama_webinar}</w:t></w:r></w:p>
<w:p><w:r><w:t>Pelaksanaan: ${tanggal_pelaksanaan} (${jam_mulai} - ${jam_selesai} WIB)</w:t></w:r></w:p>
<w:p><w:r><w:t>Jumlah Peserta Hadir: ${jumlah_peserta} orang</w:t></w:r></w:p>
<w:p><w:r><w:t>Daftar Peserta:</w:t></w:r></w:p>
<w:p><w:r><w:t>${daftar_peserta}</w:t></w:r></w:p>
<w:p><w:r><w:t>Pejabat Penandatangan: ${signing_official}</w:t></w:r></w:p>';

create_minimal_docx($dir . 'default-sk.docx', $sk_xml);

// Petikan docx
$petikan_xml = '<w:p><w:r><w:t>PETIKAN SERTIFIKAT KEIKUTSERTAAN</w:t></w:r></w:p>
<w:p><w:r><w:t>Nomor Petikan: ${petikan_number}</w:t></w:r></w:p>
<w:p><w:r><w:t>Diberikan kepada:</w:t></w:r></w:p>
<w:p><w:r><w:t>Nama: ${nama_peserta}</w:t></w:r></w:p>
<w:p><w:r><w:t>Email: ${email_peserta}</w:t></w:r></w:p>
<w:p><w:r><w:t>Jabatan: ${jabatan}</w:t></w:r></w:p>
<w:p><w:r><w:t>Instansi: ${instansi}</w:t></w:r></w:p>
<w:p><w:r><w:t>Atas partisipasinya dalam webinar: ${nama_webinar}</w:t></w:r></w:p>
<w:p><w:r><w:t>Tanggal Pelaksanaan: ${tanggal_pelaksanaan}</w:t></w:r></w:p>
<w:p><w:r><w:t>Referensi SK Minut: ${sk_number} Tanggal ${sk_date}</w:t></w:r></w:p>
<w:p><w:r><w:t>Pejabat Penandatangan SK: ${signing_official}</w:t></w:r></w:p>
<w:p><w:r><w:t>Verifikasi Keaslian: ${qr_url}</w:t></w:r></w:p>';

create_minimal_docx($dir . 'default-petikan.docx', $petikan_xml);
