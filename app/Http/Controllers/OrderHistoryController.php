<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Request;

class OrderHistoryController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$orders = Order::select()->whereNull('original_order')->orderBy('updated_at', 'desc')->paginate(6);

		return view('admin.history.index', compact('orders'));
	}

	public function show()
	{
		$order = Order::find($_GET)->first();
		return view('admin.history.show', compact('order'));
	}


	public function buscaPagamentosDerivados(){

	}
}
