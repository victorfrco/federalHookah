@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            @if(isset($order))
             <h1 align="center" style="margin-top:15%">{!! Icon::create('warning-sign') !!} Ops, esta página está em manutenção! {!! Icon::create('warning-sign') !!}</h1>
                @php
                    $dados = App\Http\Controllers\OrderHistoryController::exibeDetalhes($order);
                @endphp

                @if($dados['possuiSubOrder'])
                    Exibe SubOrders {!! $dados['subOrders']->count() !!}
                    dd($dados);
                @endif










            @else
            <h4>Nenhuma venda realizada!</h4>
        @endif
    </div>
@endsection