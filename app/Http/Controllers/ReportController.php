<?php

namespace App\Http\Controllers;

use \Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(){
    	return view('admin.reports.index');
    }

    public function generateReport(Request $request){
        $date = $request->toArray()['date'];
        $this->buscaDadosPorData($date);
    }

	private function buscaDadosPorData($date) {
    	$dados = [];

    	$dados['data'] = $date;
		$dados['totalDeVendas'] = DB::table('orders')->whereDate('created_at','=', $date)->count();
		$dados['vlrTotalDeVendas'] = DB::table('orders')->whereDate('created_at','=', $date)->sum('total');


		$dados['totalDeVendasFinalizadas'] = DB::table('orders')->where('status', '=', 3)
		                                                              ->whereDate('created_at','=', $date)->count();
		$dados['vlrTotalDeVendasFinalizadas'] = DB::table('orders')->where('status', '=', 3)
		                                                ->whereDate('created_at','=', $date)->sum('total');


		$dados['totalDeVendasEmAberto'] = DB::table('orders')->where('status', '=', 2)
		                                          ->whereDate('created_at','=', $date)->count();
		$dados['vlrTotalDeVendasEmAberto'] = DB::table('orders')->where('status', '=', 2)
		                                                ->whereDate('created_at','=', $date)->sum('total');


		$dados['totalDeVendasCanceladas'] = DB::table('orders')->where('status', '=', 1)
		                                            ->whereDate('created_at','=', $date)->count();
		$dados['vlrTotalDeVendasCanceladas'] = DB::table('orders')->where('status', '=', 1)
		                                                ->whereDate('created_at','=', $date)->sum('total');



		dd($dados);
	}
}
