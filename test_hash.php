<?php
$hash = '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm';
var_dump(password_verify('admin123', $hash));
$new_hash = password_hash('admin123', PASSWORD_DEFAULT);
echo "\nNew hash: $new_hash\n";
