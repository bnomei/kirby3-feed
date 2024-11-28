<?php

const KIRBY_HELPER_DUMP = false;
const KIRBY_HELPER_E = false;

require 'kirby/bootstrap.php';

echo (new Kirby)->render();
