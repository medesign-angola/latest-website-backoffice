<?php

require_once(TEMPLATEPATH . "/Utils/Geral.php");
require_once(TEMPLATEPATH . "/Utils/Util.php");

class Home{

    public function __construct()
    {
        $this->namespace = 'wp/v2';
        $this->endpointClients = '/clientes';
        $this->endpointServices = '/servicos';
    }

    public function registerRoute()
    {
        Geral::registerRoute('GET', 'clients', $this, $this->namespace, $this->endpointClients);
        Geral::registerRoute('GET', 'services', $this, $this->namespace, $this->endpointServices);
    }

    public function clients(){
        return Util::clients();
    }

    public function services(){
        return Util::services();
    }
    
}


function registerRoute(){
    $home = new Home();
    $home->registerRoute();
}

add_action('rest_api_init', 'registerRoute');