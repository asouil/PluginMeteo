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

    public static function desactivate()
    {
    }
    public static function menuHtml(){
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
            $ville=$_POST['ville'];
        }
    
    }

    public static function menuHtmlInit(){ 
        //global $wpdb; 
    }       
}

register_activation_hook(__FILE__, array('Meteo', 'install'));

register_deactivation_hook(__FILE__, array('Meteo','desactivate'));

register_uninstall_hook(__FILE__, array('Meteo','uninstall'));

new Meteo;

class affichageMeteo extends WP_Widget {

    public function __construct(){
        parent::__construct('idAffichageMeteo', 'affichageMeteo', array('description' => 'plugin meteo'));
    }

    public function widget($args, $instance){
        $meteo = apply_filters('widget_text', $instance);
        require_once 'config.php';
        $ville="Montlucon";
        $url="http://api.openweathermap.org/data/2.5/find?q=".$ville."&units=metric&type=accurate&mode=xml&APPID=".$appid."&lang=FR";
        $humidity=humidity($url);
        $temp=temp($url);
        $min=tempmin($url);
        $max=tempmax($url);
        $wind=wind($url);
        $weather=sky($url);
        $day=new Date('dd/mm/YYYY');
        echo ' ?
        <div class="weathertime">
        <h1>'.$ville.'</h1>
        <p>'.$min.' '.$max.'</p>
        <p><img src="flechebas"'.$day.'<img src="flechehaut"</p> <br/>
        <p>'.$wind.'</p> <br/>
        <img src="goutte"><p>'.$humidity.'</p>
        <div class="temperature"><p>'.$weather.'</p><p>'.$temp.'</p></div>
        </div>';
    }

    public static function humidity($url){
        $getweather=simplexml_load_file($url);
        $gethumidity= $getweather->list->item->humidity['value'];
        return $gethumidity;
    }
    public static function temp($url){
        $getweather=simplexml_load_file($url);
        $gettemp= $getweather->list->item->temperature['value'];
        return $gettemp;
    }

    public static function tempmin($url){
        $getweather=simplexml_load_file($url);
        $gettemp= $getweather->list->item->temperature['min'];
        return $gettemp;
    }

    public static function tempmax($url){
        $getweather=simplexml_load_file($url);
        $gettemp= $getweather->list->item->temperature['max'];
        return $gettemp;
    }

    public static function wind($url){
        $getweather=simplexml_load_file($url);
        $getwind= $getweather->list->item->speed['value'];
        return $getwind;
    }

    public static function sky($url) {
        $getweather=simplexml_load_file($url);
        $sky= $getweather->list->item->weather['value'];
        return $sky;
    }
    
}
function declarerWidget(){
    register_widget('affichageMeteo');
}
add_shortcode('widget','[meteo]');
?>
