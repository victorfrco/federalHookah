<?php

namespace App\Forms;

use Kris\LaravelFormBuilder\Form;

class ClientForm extends Form
{
    public function buildForm()
    {
        $id = $this->getData('id');
        $this
            ->add('name', 'text',[
                'label' => 'Nome',
                'rules' => 'required'
            ])
            ->add('nickname', 'text', [
                'label' => 'Razão Social',
                'rules' => 'required'
            ])
	        ->add('phone1', 'text', [
		        'label' => 'Tel. Celular',
		        'rules' => 'max:11|required'
	        ])
	        ->add('phone2', 'text', [
		        'label' => 'Tel. Comercial',
		        'rules' => 'max:11'
	        ])
	        ->add('cpf', 'text', [
		        'label' => 'CPF',
		        'rules' => "max:11"
	        ])
	        ->add('cnpj', 'text', [
		        'label' => 'CNPJ',
		        'rules' => "max:18"
	        ])
	        ->add('adr_street', 'text', [
		        'label' => 'Rua'
	        ])
	        ->add('adr_number', 'text', [
		        'label' => 'Número'
	        ])
	        ->add('adr_compl', 'text', [
		        'label' => 'Complemento'
	        ])
	        ->add('adr_neighborhood', 'text', [
		        'label' => 'Bairro'
	        ])
	        ->add('adr_cep', 'text', [
		        'label' => 'CEP',
		        'rules' => 'max:8'
	        ])
	        ->add('obs', 'text', [
		        'label' => 'Observação'
	        ])
        ;
    }
}
