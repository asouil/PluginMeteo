<?php
/*
Plugin Name: meteo
Plugin URI: http://meteo.com/
Description: Ce plugin va révolutionner le monde de la météo.
Version: 0.1
Author: Astech
Author URI: http://meteo.com/
License: GPL3
Text Domain: meteo
*/

class Meteo
{
    private $ville="Montlucon";

    public function __construct()
    {
        add_action('widgets_init','declarerWidget');
        add_action('admin_menu', array($this,'declareAdmin'));
    }
    
    public function declareAdmin(){
        add_menu_page('Configuration de la meteo', 'Meteo', 'manage_options', 'meteo', 
        array(&$this, 'menuHtml'));
        add_submenu_page('meteo','Réinitialisation meteo','Réinitialisation','manage_options',
        'reinit',array($this,'menuHtmlInit'));
    }

    public static function install()
    {
        Meteo::install_db();
    }

    public function install_db(){
        global $wpdb;
        $wpdb->query("CREATE TABLE IF NOT EXISTS
        ".$wpdb->prefix."meteo (id int(11) AUTO_INCREMENT PRIMARY KEY, 
        weather VARCHAR(255), 
        temp FLOAT, 
        humidity FLOAT, 
        city VARCHAR(255), 
        tempmax FLOAT, 
        tempmin FLOAT, 
        wind FLOAT, 
        date CURRENT_DATE);");
    }

    public static function uninstall()
    {
        Meteo::uninstall_db();
    }

    public function uninstall_db(){
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS".$wpdb->prefix."meteo;");
    }

    public function desactivate()
    {
    }

    public function menuHtml(){
        echo '<h1>'.get_admin_page_title().'</h1>';
        echo '<p> Page du Plugin Meteo !!! </p>';
        echo '<h1>La ville</h1>'; 
        echo '<p>Cliquez sur le bouton suivant pour changer la ville</p>'; 
        echo "<form method='POST' action='#'>
            <select><option value='Montlucon'>Montluçon</option>
            <option value='Moulins'>Moulins</option>
            <option value='London'>London</option>
            </select>
            <input type='submit' name='ville'> 
            </form> "; 
        if(isset($_POST['ville'])){   
            $this->ville=$_POST['ville'];
        }
    
    }

    public function menuHtmlInit(){ 
        //global $wpdb; 
        echo "Réinitialiser
        <form method='POST' action='#'>
        <input type='submit' name='ville'> 
        </form>";
        $this->ville="Montlucon";
    }       
}

register_activation_hook(__FILE__, array('Meteo', 'install'));

register_deactivation_hook(__FILE__, array('Meteo','desactivate'));

register_uninstall_hook(__FILE__, array('Meteo','uninstall'));

$met= new Meteo;

class affichageMeteo extends WP_Widget {
    
    private $humidity;
    private $temp;
    private $wind;
    private $sky;
    private $url;
    private $min;
    private $max;

    public function __construct($ville="Moulins"){
        parent::__construct('idAffichageMeteo', 'affichageMeteo', array('description' => 'plugin meteo'));
        require_once 'config.php';
        $this->ville=$ville;
        $this->url="http://api.openweathermap.org/data/2.5/find?q=".$ville."&units=metric&type=accurate&mode=xml&APPID=".$appid."&lang=FR";
        $w=simplexml_load_file($this->url);
        $this->humidity=$w->list->item->humidity['value'];
        $this->temp=$w->list->item->temperature['value'];
        $this->min=$w->list->item->temperature['min'];
        $this->max=$w->list->item->temperature['max'];
        $this->wind=$w->list->item->wind->speed['value'];
        $this->sky=$w->list->item->weather['value'];
        }
        
    public function widget($args, $instance){
        echo '
        <div class="weathertime">
        <h1>'.$this->ville.'</h1>
        <p>Today</p>
        <p><img src="flechebas">'.$this->min.'<img src="flechehaut">'.$this->max.'</p> <br/>
        <p>Vitesse du vent :'.$this->wind.'km/h</p> <br/>
        <img src="goutte"><p>'.$this->humidity.'% </p>
        <div class="temperature"><p>'.$this->sky.'</p><p>'.$this->temp.'°C </p></div>
        </div>';
        
    }

    public function humidity($url){
        $getweather=simplexml_load_file($url);
        $gethumidity= $getweather->list->item->humidity['value'];
        $this->humidity=$gethumidity;
    }
    public function temp($url){
        $getweather=simplexml_load_file($url);
        $gettemp= $getweather->list->item->temperature['value'];
        $this->temp=$gettemp;
    }

    public function tempmin($url){
        $getweather=simplexml_load_file($url);
        $gettemp= $getweather->list->item->temperature['min'];
        $this->min=$gettemp;
    }

    public function tempmax($url){
        $getweather=simplexml_load_file($url);
        $gettemp= $getweather->list->item->temperature['max'];
        $this->max=$gettemp;
    }

    public function wind($url){
        $getweather=simplexml_load_file($url);
        $getwind= $getweather->list->item->speed['value'];
        $this->wind=$getwind;
    }

    public function sky($url) {
        $getweather=simplexml_load_file($url);
        $sky= $getweather->list->item->weather['value'];
        $this->sky=$sky;
    }
    
}
function declarerWidget(){
    register_widget('affichageMeteo');
}
add_shortcode('affichageMeteo','[meteo]');

?>
