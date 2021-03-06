<?php

namespace App\Forms;

use Kris\LaravelFormBuilder\Form;

class ClientForm extends Form
{
    public function buildForm()
    {
        $this
            ->add('name', 'text',[
                'label' => 'Nome',
                'rules' => 'required'
            ])
            ->add('nickname', 'text', [
                'label' => 'Razão Social',
                'rules' => 'required'
            ])
            ->add('cnpj', 'text', [
                'label' => 'CNPJ',
                'rules' => 'max:16'
            ])
	        ->add('phone1', 'text', [
		        'label' => 'Tel. Celular',
		        'rules' => 'max:11'])
        ;
    }
}
