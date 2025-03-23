<?php
include 'config.php';

// Membaca daftar folder dari folder.txt
$folderNames = explode(PHP_EOL, trim(file_get_contents($config['folder'])));

// Membaca daftar keywords dari kw.txt
$keywords = explode(PHP_EOL, trim(file_get_contents($config['keywords'])));

// Template HTML
$htmlTemplate = file_get_contents($config['template']);

foreach ($folderNames as $index => $folderName) {
    $folderName = trim($folderName);

    if (!empty($folderName)) {
        // Pastikan folder belum ada, lalu buat folder
        if (!file_exists($folderName)) {
            mkdir($folderName, 0777, true);

            // Ambil keyword berdasarkan indeks folder
            $brand = isset($keywords[$index]) ? trim($keywords[$index]) : 'Default Brand';

            // Generate URL untuk folder
            $url = $config['url'] . $folderName . '/';

            // Ganti placeholder dalam template
            $htmlContent = str_replace('{{ BRAND }}', $brand, $htmlTemplate);
            $htmlContent = str_replace('{{ URL }}', $url, $htmlContent);

            // Simpan file index.php dalam folder yang dibuat
            file_put_contents("$folderName/index.php", $htmlContent);

            echo "Folder '$folderName' dan file 'index.php' telah dibuat dengan brand '$brand'.<br>";
        } else {
            echo "Folder '$folderName' sudah ada.<br>";
        }
    }
}

// Membuat sitemap jika belum ada
if (!file_exists($config['sitemap'])) {
    $doc = new DOMDocument('1.0', 'UTF-8');
    $doc->formatOutput = true;

    $urlset = $doc->createElement('urlset');
    $urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

    $doc->appendChild($urlset);

    foreach ($folderNames as $index => $folderName) {
        $folderName = trim($folderName);

        if (!empty($folderName)) {
            $url = $config['url'] . $folderName . '/';

            $urlElement = $doc->createElement('url');

            $locElement = $doc->createElement('loc', htmlspecialchars($url));
            $urlElement->appendChild($locElement);

            $lastmodElement = $doc->createElement('lastmod', date("Y-m-d"));
            $urlElement->appendChild($lastmodElement);

            $changefreqElement = $doc->createElement('changefreq', 'daily');
            $urlElement->appendChild($changefreqElement);

            $urlset->appendChild($urlElement);
        }
    }

    $doc->save($config['sitemap']);
    echo "Sitemap telah dibuat.<br>";
}

// Kembalikan response 404
header("HTTP/1.1 404 Not Found");
exit;
