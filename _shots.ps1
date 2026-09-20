# Ambil screenshot halaman publik pakai Chrome headless
param([string]$Suffix = '')
$chrome = 'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe'
$out = 'c:\xampp\htdocs\website-jasa\_shots'
if (!(Test-Path $out)) { New-Item -ItemType Directory -Path $out | Out-Null }

$pages = @{
  '01-home'         = 'http://localhost/website-jasa/'
  '02-layanan'      = 'http://localhost/website-jasa/layanan'
  '03-portfolio'    = 'http://localhost/website-jasa/portfolio'
  '04-artikel'      = 'http://localhost/website-jasa/artikel'
  '05-kontak'       = 'http://localhost/website-jasa/kontak'
  '06-tentang'      = 'http://localhost/website-jasa/tentang'
  '07-pesan'        = 'http://localhost/website-jasa/pesan'
  '08-pesan-cek'    = 'http://localhost/website-jasa/pesan/cek'
  '09-layanan-dtl'  = 'http://localhost/website-jasa/layanan/pembuatan-website'
  '10-artikel-dtl'  = 'http://localhost/website-jasa/artikel/ffffff-abdabdabda-adbbf-fhfj-dajfj'
  '11-portfolio-dtl'= 'http://localhost/website-jasa/portfolio/uji-card-1'
}

foreach ($k in $pages.Keys) {
  $file = Join-Path $out ("{0}{1}.png" -f $k, $Suffix)
  & $chrome --headless=new --disable-gpu --no-sandbox --hide-scrollbars --force-device-scale-factor=1 `
            --virtual-time-budget=6000 --window-size=1366,2600 `
            --screenshot="$file" $pages[$k] 2>$null | Out-Null
  if (Test-Path $file) { "OK  $k -> $((Get-Item $file).Length) bytes" } else { "GAGAL $k" }
}