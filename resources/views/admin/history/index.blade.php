@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            @if(isset($orders))
                {!! Table::withContents($orders->items())
                 ->callback('Ações', function($campo, $model){
                    $linkEdit = route('historyDetail', ['order' => $model->id]);
                     return Button::link('Ver Detalhes &nbsp'.Icon::create('eye-open'))->asLinkTo($linkEdit);
                 })->withAttributes([
                    'style' => 'font-size: 13px'
                 ]);
                 !!}
        </div>
        {!! $orders->links(); !!}
        @else
            <h4>Nenhuma venda realizada!</h4>
        @endif
    </div>
@endsection