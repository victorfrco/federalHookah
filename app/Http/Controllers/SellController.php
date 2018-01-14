<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Item;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Sell;
use App\Models\PartialOrder;
use function array_push;
use Auth;
use Bootstrapper\Facades\Modal;
use function compact;
use function dd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use function redirect;
use function Sodium\compare;
use function view;

class SellController extends Controller
{

    private $STATUS_CANCELADA = 1;
    private $STATUS_MESA = 2;
    private $STATUS_PAGA = 3;
	private $STATUS_EM_ABERTO = 4;
	private $STATUS_PAGA_PARCIALMENTE = 5;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::all();
        return view('admin.sells.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Sell  $sell
     * @return \Illuminate\Http\Response
     */
    public function show(Sell $sell)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Sell  $sell
     * @return \Illuminate\Http\Response
     */
    public function edit(Sell $sell)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Sell  $sell
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Sell $sell)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Sell  $sell
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sell $sell)
    {
        //
    }

    //aplica ou remove preço de associado a uma venda
	public function aplicarRemoverDesconto(Request $request){
//		dd($request->toArray());
		$order = Order::find($request->get('order_id'));
		if(!$order->associated) {
			$this->recebeDesconto($order, true);
		}
		else{
			$this->recebeDesconto($order, false);
		}

		$order->save();
		$categories = Category::all();

		return view('home', compact('order', 'categories'));
	}


    //exclui item do pedido e devolve a quantidade ao estoque;
    public function removeItem(Request $request){
		$item = Item::find($request->toArray()['item']);
	    $product = Product::find($item->product_id);
	    $categories = Category::all();

	    $product->qtd += $item->qtd;
	    $product->save();
	    $item->delete();
	    $order = $this->atualizaPedido($item->order_id);

	    return view('home', compact('order', 'categories'));
    }

    public function criarMesa(Request $request){
        $order = new Order();
        $order->client_id = $request->toArray()['client_id'];
        $order->total = 0;
        $order->status = $this->STATUS_MESA;
        $order->associated = $request->toArray()['associated'];
        $order->user_id = Auth::user()->id;
        $order->save();

        $categories = Category::all();
        return redirect('home')->with(compact('order', 'categories'));
    }

    public function vinculaItensNoPedido(Order $pedido, array $itens)
    {
        if($pedido->id == null)
            $pedido->save();
        else{
            $pedidoAntigo = Order::find($pedido->id);
            $pedidoAntigo->total += $pedido->total;
            $pedidoAntigo->save();
        }
        foreach($itens as $item){
            if ($item->qtd > 0){
                $item->order_id = $pedido->id;
                $itemAntigo = Item::where([
                    ['order_id', '=', $item->order_id],
                    ['product_id', '=', $item->product_id]
                ])->get();
                if($itemAntigo->isNotEmpty())
                {
                    $itemAntigo[0]->qtd += $item->qtd;
                    $itemAntigo[0]->total += $item->total;
                    $itemAntigo[0]->save();
                }else
                    $item->save();
            }
            $product = Product::find($item->product_id);
            $product->qtd -= $item->qtd;
            $product->save();
        }
        return $pedido->id;
    }

    public function codBarra(Request $request){
    	$product = $request->get('product_barcode');
    	$product = Product::where('barcode', '=', $product)->first();
	    if($product != null){
	    	$product->qtd--;
	    	$product->update();
		    $order = Order::find( $request->toArray()['order_id']);

			//verifica se ja existe um item com esse produto nesse pedido;
		    $item = Item::where('order_id', '=', $order->id)->where('product_id','=', $product->id)->first();
		    if($product->qtd <= 0)
			    return redirect()->back()->with('semEstoque', $order)->with(compact('order'));
		    if($item != null) {
				//adiciona mais 1 quantidade do produto ao item;

				$item->qtd ++;
				if($order->associated) {
					$item->total += $product->price_discount;
					$order->total += $product->price_discount;
				}
				else {
					$item->total += $product->price_resale;
					$order->total += $product->price_resale;
				}
				$item->update();
				$order->update();
			}
			else{
				$item = new Item();
				$item->product_id = $product->id;
				if($order->associated == 0)
					$item->total = $product->price_resale;
				else
					$item->total = $product->price_discount;
				$item->qtd = 1;
				$item->order_id = $order->id;
				$item->save();
				$order->total += $item->total;
				$order->update();
			}
	    }else{
		    if(array_key_exists( 'order_id' , $request->toArray())) {
			    $order = Order::find( $request->toArray()['order_id'] )->first();
			    if ( $order->exists() ) {
				    return redirect()->back()->with( 'inexistente', $order )->with( compact( 'order' ) );
			    } else {
				    return redirect()->back()->with( 'inexistente', '' );
			    }
		    }
	    }
	    if(array_key_exists( 'order_id' , $request->toArray()))
		    $order = Order::find( $request->toArray()['order_id']);
	    $categories = Category::all();

	    return view('home', compact('order', 'categories'));
    }

    public function addProducts(Request $request)
    {
        $items = [];
        $valorTotal = 0;
        $order = new Order();
        if(array_key_exists( 'order_id' , $request->toArray()))
            $order = Order::find($request->toArray()['order_id']);
        else {
            $order->client_id = Client::find(1)->id;
            $order->associated = 0;
            $order->status = $this->STATUS_EM_ABERTO;
            $order->user_id = Auth::user()->id;
        }
        $order->total = 0;

        foreach ($request->toArray() as $produto => $quantidade){

            if($produto != "_token" && $produto != "order_id") {
                $item = new Item();
                $item->product_id = $produto;
                if($order->associated == 0)
                    $item->total = $quantidade * Product::find($produto)->price_resale;
                else
                    $item->total = $quantidade * Product::find($produto)->price_discount;
                $item->qtd = $quantidade;
                $item->order_id = $order->id;
                array_push($items, $item);
                $valorTotal += $item->total;
            }
        }
        $order->total += $valorTotal;
        $order = Order::find($this->vinculaItensNoPedido($order, $items));
	    $categories = Category::all();
	    return view('home', compact('order', 'categories'));
    }

    public function listaProdutosPorMarca($products = array())
    {
        $divs = [];
        $divHeader = '<table class="table table-bordered" style="font-size: 13px; color:black">
                    <tr>
                        <th>Nome</th>
                        <th style="text-align: center">Estoque</th>
                        <th style="text-align: center">Preço</th>
                        <th style="text-align: center">Associado</th>
                        <th style="text-align: center">Quantidade</th>
                    </tr>';
        $divFooter = '<input name="_token" type="hidden" value="'. csrf_token().'"/></table>';
        foreach ($products as $product){
            $divCont = '<tr>
                        <td>'.$product->name.'
                        </td>
                        <td style="text-align: center">
                        '.$product->qtd.'
                        </td>
                        <td style="text-align: center">R$ '.$product->price_resale.'
                        </td>
                        <td style="text-align: center">R$ '.$product->price_discount.'
                        </td>
                        <td style="text-align: center" form="form-add-order">'.
                \Bootstrapper\Facades\Button::appendIcon(\Bootstrapper\Facades\Icon::plus())->withAttributes(
                    ['class' => 'btn btn-xs', 'onclick' => "incrementaProduto($product->id)"]).
                       '&nbsp;&nbsp;&nbsp;&nbsp;<input id="'.$product->id.'"  min="0" style="width:60px" class="form" name="'.$product->id.'" type="number" value="0">&nbsp;&nbsp;&nbsp;&nbsp;'.
               \Bootstrapper\Facades\Button::appendIcon(\Bootstrapper\Facades\Icon::minus())->withAttributes(
                    ['class' => 'btn btn-xs', 'onclick' => "decrementaProduto($product->id)"]).'
                                
                        </td>
                        </tr>';
            array_push($divs, $divCont);
        }
        $string = implode($divs);
        $table = $divHeader.$string.$divFooter;

        return $table;
    }

    public function concluirVenda(Request $request){
        $order = Order::find($request->toArray()['order_id']);
        $order->pay_method = $request->toArray()['formaPagamento'];

        if($order->pay_method == 4)
            $order->obs = $request->toArray()['obs'];

        $order->status = $this->STATUS_PAGA;
        $order->update();
        return Redirect::to('/home')->with('vendaRealizada', 'Venda realizada com sucesso!');
    }

    public function cancelarVenda(Request $request){
        $order = Order::find($request->toArray()['order_id']);
        $order->status = $this->STATUS_CANCELADA;
        $order->save();
        $this->devolveProdutoEstoque($order->id);
        return Redirect::to('/home');
    }

    private function devolveProdutoEstoque($id)
    {
        $itens = Item::all()->where('order_id', '=', $id);

        foreach ($itens as $item){
            $product = Product::find($item->product_id);
            $product->qtd += $item->qtd;
            $product->save();
        }

    }

    private function recebeDesconto($order, $aplica)
    {
    	$total = 0;
	    foreach ($order->itens()->get() as $item){
		    if($aplica) {
			    $item->total = $item->qtd * Product::find( $item->product_id )->price_discount;
			    $order->associated = 1;
		    }
		    else{
			    $item->total = $item->qtd * Product::find( $item->product_id )->price_resale;
			    $order->associated = 0;
		    }
		    $item->save();
		    $total += $item->total;
	    }
	    $pago = OrderController::valorPago($order);
        $total -= $pago;
	    $order->total = $total;

        return $order;
    }

    private function atualizaPedido($id){
	    $itens = Item::all()->where('order_id', '=', $id);
		$order = Order::find($id);
		$total = 0;

		foreach ($itens as $item){
			$product = Product::find($item->product_id);
			$total += $item->qtd * $product->price_resale;
		}
		$order->total = $total;
	    $order->save();
    	return $order;
    }

	public static function buscaProdutosPorVenda(Order $order)
	{
		$itens = Item::all()->where('order_id', '=', $order->id);
		$divs = [];
		$divHeader = '<table class="table table-bordered" style="font-size: 13px; color:black">
                    <tr>
                        <th style="text-align: center">Descrição</th>
                        <th style="text-align: center">Quantidade</th>
                        <th style="text-align: center">Valor Unidade</th>
                        <th style="text-align: center">A Pagar</th>
                    </tr>';
		$divFooter = '<input name="_token" type="hidden" value="'. csrf_token().'"/></table>';
		foreach ($itens as $item){
			$product = Product::find($item->product_id);
			$divCont = '<tr>
                        <td>'.$product->name.'
                        </td>
                        <td style="text-align: center">'.$item->qtd.'
                        </td>
                        <td style="text-align: center">R$'.$item->total/$item->qtd.'
                        </td>
                        <td style="text-align: center; min-width: 170px" form="form-add-order">'.
			           \Bootstrapper\Facades\Button::appendIcon(\Bootstrapper\Facades\Icon::plus())->withAttributes(
				           ['class' => 'btn btn-xs', 'onclick' => "incrementaProduto($product->id)"]).
			           '&nbsp;<input id="'.$product->id.'"  min="0" max="'.$item->qtd.'" style="width:60px" class="form" name="'.$item->id.'" type="number" value="0">&nbsp;'.
			           \Bootstrapper\Facades\Button::appendIcon(\Bootstrapper\Facades\Icon::minus())->withAttributes(
				           ['class' => 'btn btn-xs', 'onclick' => "decrementaProduto($product->id)"]).'
                                
                        </td>
                        </tr>';
			array_push($divs, $divCont);
		}
		$string = implode($divs);
		$table = $divHeader.$string.$divFooter;

		return $table;
	}

	public function vendaParcial(Request $request){
		//verificar se o item só contem um produto, se for unico troca o order_id do item,
		//     senão cria um novo item para essa order e subtrai a quantidade de produtos do item da ordem antiga

        $orderOriginal = Order::find($request->get('order_id'));
		$parcial = new Order();
		//setar forma de pagamento, e valor total da ordem derivada,
		$parcial->pay_method = $request->get('formaPagamento');
		$parcial->total = 0;
		$parcial->status = 1;
		$parcial->client_id = $orderOriginal->client_id;
		$parcial->user_id = $orderOriginal->user_id;
		$parcial->associated = $orderOriginal->associated;
		$parcial->original_order = $orderOriginal->id;

        if($parcial->pay_method == 4) {
            $parcial->total = $request->toArray()['valorPago'];
            $parcial->obs = $request->toArray()['obsParcial'];
            $orderOriginal->total -= $parcial->total;
            if($orderOriginal->total < 1)
                $orderOriginal->status = $this->STATUS_PAGA;
            else
                $orderOriginal->status = $this->STATUS_PAGA_PARCIALMENTE;

            if($orderOriginal->total < 1){
                $orderOriginal->status = $this->STATUS_PAGA;
                $orderOriginal->update();
                $parcial->save();
                $categories = Category::all();
                return view('home', compact('order', 'categories'));
            }
            $orderOriginal->update();
            $parcial->save();

            $order = $orderOriginal;
            $categories = Category::all();
            return view('home', compact('order', 'categories'));

        }

		$totalItensRemovidos = 0;
		foreach ($request->toArray() as $item => $quantidade){
			if($item != "valorPago" && $item != "obsParcial" && $item != "_token" && $item != "order_id" && $item != "formaPagamento" && $quantidade != 0) {
				$itemOriginal = Item::find($item);
				$valorDoItem = $itemOriginal->total / $itemOriginal->qtd;
				if($itemOriginal->qtd > $quantidade){

					//verificar o valor total de cada order de acordo com a quantidade de produtos de cada item
					$itemOriginal->qtd -= $quantidade;
					$itemOriginal->total = $itemOriginal->qtd * $valorDoItem;

					$itemDerivado = new Item();
					$itemDerivado->product_id = $itemOriginal->product_id;
					$itemDerivado->qtd = $quantidade;
					$itemDerivado->total = $quantidade * $valorDoItem;
					$parcial->save();
					$itemDerivado->order_id = $parcial->id;

					$itemOriginal->update();
					$itemDerivado->save();
					$totalItensRemovidos += $itemDerivado->total;

					$parcial->total += $itemDerivado->total;
				} else{
					//item aponta para a partialOrder
					$parcial->total += $itemOriginal->total;
					$parcial->save();
					$itemOriginal->order_id = $parcial->id;
					$totalItensRemovidos += $itemOriginal->total;
					$itemOriginal->update();
				}
			}
		}

		$orderOriginal->total -= $totalItensRemovidos;
		$parcial->save();
		$orderOriginal->status = $this->STATUS_PAGA_PARCIALMENTE;
		$orderOriginal->save();
		
		//verificar se a ordem de origem ainda possui itens, senão deve-se colocá-la como paga
		if(Item::all()->where('order_id', '=', $orderOriginal->id)->isEmpty()){
			$orderOriginal->status = $this->STATUS_PAGA;
			$orderOriginal->save();
			return Redirect::to('/home')->with('vendaRealizada', 'Venda realizada com sucesso!');
		}

        $order = $orderOriginal;
		$categories = Category::all();
		return view('home', compact('order', 'categories'));
	}
}
