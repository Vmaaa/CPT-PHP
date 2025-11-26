<?php require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../inc/inc_auth.php'; ?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= CSS_URL ?>/style.css">
<link rel="stylesheet" href="<?= CSS_URL ?>/general.css">
<link rel="stylesheet" href="<?= CSS_URL ?>/components/buttons.css">
<link rel="stylesheet" href="<?= CSS_URL ?>/components/forms.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script defer src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
<?php
if (!empty($pageStyle)) {
  echo '<link rel="stylesheet" href="' . CSS_URL . '/' . $pageStyle . '">';
}
?>
