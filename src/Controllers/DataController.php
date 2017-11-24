<?php
namespace robjuz\LaravelClicData\Controllers;

use robjuz\LaravelClicData\Data;

class DataController extends Controller {

    public function index()
    {
        return Data::all();
    }

    public function show($id)
    {
        return Data::get($id);
    }
}