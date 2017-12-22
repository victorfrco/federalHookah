<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Federal Hookah Pub') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
<h2 align="center"> Relatório Diário - Sintético</h2>
<h4 align="center"> Data: {{$dados['data']}}</h4>
<table class="table table-striped">
    <thead>
    <tr>
        <th>Status</th>
        <th>Quantidade</th>
        <th>Valor Total</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <th>Total</th>
        <td>{{$dados['totalDeVendas']}}</td>
        <td>{{$dados['vlrTotalDeVendas']}}</td>
    </tr>
    <tr>
        <th>Em Aberto</th>
        <td>{{$dados['totalDeVendasEmAberto']}}</td>
        <td>{{$dados['vlrTotalDeVendasEmAberto']}}</td>
    </tr>
    </tbody>
</table>
</body>
