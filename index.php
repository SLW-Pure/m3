<?php
require('fpdf/fpdf.php');

if (isset($_POST['hesapla']) || isset($_POST['pdf'])) {
  // Hesaplama işlemleri
  $enler = $_POST['en'];
  $boylar = $_POST['boy'];
  $derler = $_POST['der'];
  $adetler = $_POST['adet'];
  $metrekupFiyat = floatval($_POST['metrekup_fiyat']);
  $paraBirimi = $_POST['para_birimi'];

  $sonuclar = array();
  $toplamFiyat = 0;
  $toplamSonuc = 0;

  for ($i = 0; $i < count($enler); $i++) {
    $en = floatval($enler[$i]);
    $boy = floatval($boylar[$i]);
    $der = floatval($derler[$i]);
    $adet = intval($adetler[$i]);

    $toplam = $en * $boy * $der;
    $sonuc = ($toplam / 1000000) * $adet;
    $fiyat = $sonuc * $metrekupFiyat;
    if ($paraBirimi == 'usd') {
      $fiyat = $fiyat / 32.22;
    } elseif ($paraBirimi == 'eur') {
      $fiyat = $fiyat / 35.50;
    }

    $sonuclar[] = array(
      'en' => $en,
      'boy' => $boy,
      'der' => $der,
      'adet' => $adet,
      'sonuc' => $sonuc,
      'fiyat' => $fiyat,
      'paraBirimi' => $paraBirimi
    );

    $toplamSonuc += $sonuc;
    $toplamFiyat += $fiyat;
  }

  // PDF Oluşturma
  if (isset($_POST['pdf'])) {
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,10,'Metreküp Fiyat Hesaplama Sonuçları',0,1,'C');
    
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(30,10,'En',1);
    $pdf->Cell(30,10,'Boy',1);
    $pdf->Cell(30,10,'Kalınlık',1);
    $pdf->Cell(30,10,'Adet',1);
    $pdf->Cell(30,10,'Toplam m3',1);
    $pdf->Cell(30,10,'Fiyat',1);
    $pdf->Ln();

    foreach ($sonuclar as $sonuc) {
      $pdf->Cell(30,10,$sonuc['en'],1);
      $pdf->Cell(30,10,$sonuc['boy'],1);
      $pdf->Cell(30,10,$sonuc['der'],1);
      $pdf->Cell(30,10,$sonuc['adet'],1);
      $pdf->Cell(30,10,round($sonuc['sonuc'],2),1);
      $pdf->Cell(30,10,round($sonuc['fiyat'],2),1);
      $pdf->Ln();
    }

    $pdf->Output();
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metreküp Hesaplama</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">

<!-- Logo ve Menü -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <a class="navbar-brand" href="#">
    <img src="https://rootali.net/wp-content/uploads/logo.png" width="150" height="50" alt="Ali Çömez Logo">
  </a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarNav">
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <a class="nav-link" href="#">Ana Sayfa</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="https://alicomez.com/who-am-i">Hakkımda</a>
      </li>
    </ul>
  </div>
</nav>

<div class="container mt-5">
  <?php
  if (isset($sonuclar) && count($sonuclar) > 0) {
    echo '<h2>Sonuçlar:</h2>';
    echo '<table class="table table-bordered">';
    echo '<thead><tr><th>En</th><th>Boy</th><th>Kalınlık</th><th>Adet</th><th>Toplam m³</th><th>Fiyat ('.strtoupper($paraBirimi).')</th></tr></thead>';
    echo '<tbody>';
    foreach ($sonuclar as $sonuc) {
      echo '<tr>';
      echo '<td>'.$sonuc['en'].'</td>';
      echo '<td>'.$sonuc['boy'].'</td>';
      echo '<td>'.$sonuc['der'].'</td>';
      echo '<td>'.$sonuc['adet'].'</td>';
      echo '<td>'.round($sonuc['sonuc'],2).'</td>';
      echo '<td>'.round($sonuc['fiyat'],2).'</td>';
      echo '</tr>';
    }
    echo '</tbody></table>';
    echo '<div class="text-center">';
    echo '<h3>Toplam Metreküp: '.round($toplamSonuc,2).' m³</h3>';
    echo '<h3>Toplam Fiyat: '.round($toplamFiyat,2).' '.strtoupper($paraBirimi).'</h3>';
    
    // PDF İndirme Butonu Hesaplama Yapıldıktan Sonra Gösterilir
    echo '<form method="POST">';
    foreach ($_POST as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $arrayValue) {
                echo '<input type="hidden" name="'.$key.'[]" value="'.$arrayValue.'">';
            }
        } else {
            echo '<input type="hidden" name="'.$key.'" value="'.$value.'">';
        }
    }
    echo '<button type="submit" name="pdf" class="btn btn-danger mt-3">PDF İndir</button>';
    echo '</form>';

    echo '</div>';
  }
  ?>

  <div class="card mt-5">
    <div class="card-header">Hesaplama</div>
    <div class="card-body">
      <form action="" method="POST">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>En (cm)</th>
              <th>Boy (cm)</th>
              <th>Kalınlık (cm)</th>
              <th>Adet</th>
            </tr>
          </thead>
          <tbody id="inputs-container">
            <tr>
              <td><input type="text" class="form-control" name="en[]" placeholder="En (cm)" required></td>
              <td><input type="text" class="form-control" name="boy[]" placeholder="Boy (cm)" required></td>
              <td><input type="text" class="form-control" name="der[]" placeholder="Kalınlık (cm)" required></td>
              <td><input type="number" class="form-control" name="adet[]" placeholder="Adet" required></td>
            </tr>
          </tbody>
        </table>
        <button type="button" class="btn btn-success mb-2" onclick="addInput()">Yeni Ekle</button>

        <div class="form-group mt-3">
          <label for="metrekup_fiyat">Metreküp Fiyatı (₺):</label>
          <input type="number" class="form-control" name="metrekup_fiyat" placeholder="Metreküp başına fiyat" required>
        </div>

        <div class="form-group mt-3">
          <label for="para_birimi">Para Birimi Seçin:</label>
          <select class="form-control" name="para_birimi" required>
            <option value="try">Türk Lirası (TRY)</option>
            <option value="usd">Amerikan Doları (USD)</option>
            <option value="eur">Euro (EUR)</option>
          </select>
        </div>

        <button type="submit" name="hesapla" class="btn btn-success">Hesapla</button>
      </form>
    </div>
  </div>
</div>

<script>
  function addInput() {
    var container = document.getElementById("inputs-container");

    var newRow = document.createElement("tr");

    var newEnInput = document.createElement("td");
    newEnInput.innerHTML = '<input type="text" class="form-control" name="en[]" placeholder="En (cm)" required>';
    
    var newBoyInput = document.createElement("td");
    newBoyInput.innerHTML = '<input type="text" class="form-control" name="boy[]" placeholder="Boy (cm)" required>';
    
    var newDerInput = document.createElement("td");
    newDerInput.innerHTML = '<input type="text" class="form-control" name="der[]" placeholder="Kalınlık (cm)" required>';
    
    var newAdetInput = document.createElement("td");
    newAdetInput.innerHTML = '<input type="number" class="form-control" name="adet[]" placeholder="Adet" required>';

    newRow.appendChild(newEnInput);
    newRow.appendChild(newBoyInput);
    newRow.appendChild(newDerInput);
    newRow.appendChild(newAdetInput);
    
    container.appendChild(newRow);
  }
</script>

</body>
</html>
