<?php

namespace App\Models;

use App\Models\Client;
use function array_add;
use function array_merge;
use function array_push;
use Bootstrapper\Interfaces\TableInterface;
use Illuminate\Database\Eloquent\Model;
use PhpParser\Node\Stmt\ClassLike;

class Order extends Model implements TableInterface
{
    protected $table = 'orders';
    protected $fillable = ['total', 'status', 'client_id', 'user_id', 'associated'];

    public function itens()
    {
        return $this->hasMany(Item::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

	/**
	 * A list of headers to be used when a table is displayed
	 *
	 * @return array
	 */
	public function getTableHeaders()
	{
		return ['Id', 'Cliente / Mesa', 'Valor Total', 'Quantidade de Produtos', 'Forma de Pagamento', 'Data', 'Status'];
	}

	/**
	 * Get the value for a given header. Note that this will be the value
	 * passed to any callback functions that are being used.
	 *
	 * @param string $header
	 * @return mixed
	 */
	public function getValueForHeader($header)
	{
		switch ($header){
			case 'Id':
				return $this->id;
				break;
			case 'Cliente / Mesa':
				return Client::find($this->client_id)->name;
				break;
			case 'Valor Total':
				return 'R$ '.number_format((float)$this->getValorTotal(), 2, '.', '');
				break;
			case 'Quantidade de Produtos':
				return $this->getQtdProdutosTotal();
				break;
			case 'Forma de Pagamento':
				return $this->getFormaDePagamento();
				break;
			case 'Data':
				return $this->getDataFormatada();
				break;
			case 'Status':
				return $this->getStatusFormatado();
				break;
		}
	}

	public function getOrderOriginal(){
		if($this->original_order != null)
			return Order::find($this->original_order);
		else
			return $this;
	}

	public function getQtdProdutosTotal(){
		$original = $this->getOrderOriginal();
		$itensOriginais = Item::all()->where('order_id', '=', $original->id);
		$subOrders = Order::all()->where('original_order','=', $original->id);
		$itens = $itensOriginais->toArray();
		// caso possua pagamentos parcelados
		if($subOrders->count() > 0){
			foreach ($subOrders as $subOrder){
				$itens = array_merge($itens,Item::all()->where('order_id', '=', $subOrder->id)->toArray());
			}
		}

		$qtdTotal = 0;
		foreach ($itens as $item){
			$qtdTotal += $item['qtd'];
		}
		return $qtdTotal;
	}

	public function getValorTotal(){
		$original = $this->getOrderOriginal();
		$itensOriginais = Item::all()->where('order_id', '=', $original->id);
		$subOrders = Order::all()->where('original_order','=', $original->id);
		$itens = $itensOriginais->toArray();
		// caso possua pagamentos parcelados
		if($subOrders->count() > 0){
			foreach ($subOrders as $subOrder){
				$itensSecundarios = Item::all()->where('order_id', '=', $subOrder->id)->toArray();
				$itens = array_merge($itens,$itensSecundarios);
			}
		}
		$vlrTotal = 0;
		foreach ($itens as $item){
			$vlrTotal += $item['total'];
		}
		return $vlrTotal;
	}

	public function getDataFormatada(){
		$dataFormatada = new \DateTime($this->updated_at);
		return $dataFormatada->format('d/m/Y');
	}

	public function getFormaDePagamento(){
		$original = $this->getOrderOriginal();;
		$subOrders = Order::all()->where('original_order','=', $original->id);

		$pagamentoFormatado = '';

		switch ($original->pay_method){
			case 0:
				$pagamentoFormatado = 'Dinheiro';
				break;
			case 1:
				$pagamentoFormatado = 'Débito';
				break;
			case 2:
				$pagamentoFormatado = 'Crédito';
				break;
		}

		// caso possua pagamentos parcelados
		if($subOrders->count() > 0){
			return 'Diversos';
		}else
			return $pagamentoFormatado;
	}

	public function getStatusFormatado(){
		switch ($this->status){
			case 1:
				return 'Cancelada';
				break;
			case 2:
				return 'Mesa';
				break;
			case 3:
				return 'Paga';
				break;
			case 4:
				return 'Em Aberto';
				break;
			case 5:
				return 'Parcial';
				break;
		}
	}
}
