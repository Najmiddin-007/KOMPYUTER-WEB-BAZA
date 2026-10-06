<?php
require_once __DIR__.'/includes/functions.php';
if($u=current_user()) audit('user',$u['id'],'logout');
session_unset(); session_destroy();
session_start(); flash("Tizimdan chiqdingiz.");
redirect('index.php');
