<?php

require_once 'vendor/autoload.php';

// Instantiate Bin class
$bin = new \Riad\Bin();

// Switch error mode. if true Exceptions will be thrown if any error occurs, if false NULL will be returned instead. Default: false
$bin->error(true);

// Get bin information, if the second arg is true, then an array will be returned, if false, an object will be returned. Default: false
$bin->get(437776, true);

// Both search() and lookup() are aliases of get()
$bin->search(437776);

// You can get only need specific fields by calling the method fields() **BEFORE** get() search() or lookup()
$bin->fields(['bin', 'level', 'issuer'])
    ->lookup(437776);

// Get bin information using SQL-like syntax, prepared param expected for bin **ONLY**, it cannot be used with another thing.
// You may not use prepared request, no harm will be done to your server/app, nor will it be done to ours.
$bin->query("SELECT * FROM bins WHERE bin = ?")
    ->run([436667]);
