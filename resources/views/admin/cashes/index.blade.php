@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <h2>Caixa Atual</h2>
        </div>
        <div class="row">
            @php
                $orderDao = new App\Dao\DaoOrder();
                    if($caixa == null){
                        echo 'Não existe caixa em aberto. Por favor realize a abertura de caixa!<br><br>';
                        echo \Bootstrapper\Facades\Button::success('Abrir Caixa')->withAttributes(['data-toggle' => 'modal', 'data-target' => '#abrirCaixaModal']);
                    }else{
                        $controller = new \App\Http\Controllers\CashController();
                        if($autenticado != null){
                            echo 'Hora de abertura do caixa: '.$caixa->getDataAberturaFormatada().'<br>';
                            echo 'Valor de abertura: '.$caixa->getValorAberturaFormatado().'<br>';
                            echo 'Valor de vendas (dinheiro): '.$caixa->getValorAtualFormatado().'<br>';
                            echo 'Valor de vendas (débito): R$'.number_format($orderDao->buscaVendasPorFormaDePagamentoCaixa($caixa->opened_at, 2, new DateTime())->sum('total'), 2, ',', '.').'<br>';
                            echo 'Valor de vendas (crédito): R$'.number_format($orderDao->buscaVendasPorFormaDePagamentoCaixa($caixa->opened_at, 3, new DateTime())->sum('total'), 2, ',', '.').'<br>';
                            echo 'Valor atual (caixa + troco): '.$caixa->getValorTotalFormatado().'<br><br>';
                            echo \Bootstrapper\Facades\Button::danger('Fechar Caixa')->withAttributes(['data-toggle' => 'modal', 'data-target' => '#fecharCaixaModal']);
                        }else{
                            echo '<br><br>'.\Bootstrapper\Facades\Button::success('Visualizar Caixa')->withAttributes(['data-toggle' => 'modal', 'data-target' => '#autenticar']);
                        }
                    }
            @endphp
        </div>
    </div>

    <div data-keyboard="false" data-backdrop="static" class="modal fade" id="abrirCaixaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title titulo">Abrir Caixa</h4>
                </div>
                {!! Form::open(array('action' => 'CashController@store', 'method' => 'post')) !!}
                <div class="modal-body task">
                    <label>
                        Informe o valor atual do caixa (troco): R$
                        <input style="width: 90px" step="0.01" name="inicial_value">
                    </label>
                </div>
                <div class="modal-footer">
                    {!! Form::submit('Abrir!', array('class' => 'btn btn-success')) !!}
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    <div data-keyboard="false" data-backdrop="static" class="modal fade" id="fecharCaixaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title titulo">Fechar Caixa</h4>
                </div>
                {!! Form::open(array('action' => 'CashController@fecharCaixa', 'method' => 'post')) !!}
                <div class="modal-body task">
                    Deseja realmente fechar o caixa? <br>
                    @if($caixa != null)
                        O valor atual em caixa deve ser <span style="font-size: 25px">{{$caixa->getValorTotalFormatado()}}</span>.<br>
                        {{Form::hidden('cash_id', $caixa->id)}}
                    @endif
                    Valor Confere?
                    <select id="confirma" onclick="confirmar()" required>
                        <option value="">Selecione...</option>
                        <option value="1">Sim</option>
                        <option value="2">Não</option>
                    </select>
                    <div id="obs" style="display: none">
                        <br>
                        Informe uma observação:
                        <textarea id="observacao" name="obs" style="width:500px"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    {!! Form::submit('Fechar!', array('class' => 'btn btn-success')) !!}
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

    <div data-keyboard="false" data-backdrop="static" class="modal fade" id="autenticar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title titulo" style="color:#954120;">Privilégios Insuficientes</h4>
                </div>
                {!! Form::open(array('action' => 'CashController@autenticar', 'method' => 'post')) !!}
                <div class="modal-body task">
                    Insira a senha de Administrador <br>
                    {!! Form::password('senha') !!}
                </div>
                <div class="modal-footer">
                    {!! Form::submit('Ok!', array('class' => 'btn btn-success')) !!}
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/css/bootstrap-select.min.css">
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.12.4/js/bootstrap-select.min.js"></script>

    <script>
        function confirmar() {
            if (document.getElementById('confirma').value === '2') {
                document.getElementById('obs').style.display = 'block';
                document.getElementById('observacao').required = true;
            } else {
                document.getElementById('obs').style.display = 'none';
                document.getElementById('observacao').required = false;
            }
        }
    </script>
@endsection