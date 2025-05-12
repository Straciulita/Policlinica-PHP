<?php
require_once 'tabel_dinamic.php';

$search = isset($_POST['search']) ? $_POST['search'] : '';
afiseazaTabel("Pacient", $search);
