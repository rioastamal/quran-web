<?php
// Asmaul Husna Data
$asmaulHusna = [
    ["n" => 1, "latin" => "Allah", "arabic" => "الله", "meaning" => "Nama Allah yang paling agung"],
    ["n" => 2, "latin" => "Ar-Rahman", "arabic" => "الرحمن", "meaning" => "Yang Maha Pengasih"],
    ["n" => 3, "latin" => "Ar-Rahim", "arabic" => "الرحيم", "meaning" => "Yang Maha Penyayang"],
    // Add more names here...
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asmaul Husna</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Asmaul Husna</h1>
    <div class="asmaul-husna-container">
        <?php foreach ($asmaulHusna as $name): ?>
            <div class="asmaul-husna-card">
                <h2><?php echo $name['n'] . ". " . $name['latin']; ?></h2>
                <p class="arabic"><?php echo $name['arabic']; ?></p>
                <p><?php echo $name['meaning']; ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>